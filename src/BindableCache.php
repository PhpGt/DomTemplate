<?php
namespace Gt\DomTemplate;

use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use stdClass;
use Stringable;

class BindableCache {
	/**
	 * @var array<string, array<string, callable>> Outer array key is the
	 * fully-qualified class name, inner array key is the bind key, callable
	 * is the method that returns the bind value.
	 */
	private array $bindableClassMap;
	/**
	 * @var array<string, bool> A cache of class names that are known to
	 * NOT be bindable (to avoid having to check with reflection each time).
	 */
	private array $nonBindableClassMap;

	public function __construct() {
		$this->bindableClassMap = [];
		$this->nonBindableClassMap = [];
	}

	public function isBindable(object $object):bool {
		$refObj = null;

		if($object instanceof ReflectionClass) {
			$refObj = $object;
			$classString = $refObj->getNamespaceName() . "\\" . $refObj->getShortName();
		}
		else {
			$classString = $object::class;
		}

		if(isset($this->bindableClassMap[$classString])) {
			return true;
		}

		if(isset($this->nonBindableClassMap[$classString])) {
			return false;
		}

// Reflection is SLOW! The two checks above ensure that this step is only done
// once per class (not object).
		if(!$refObj) {
			$refObj = new ReflectionObject($object);
		}
		$attributeCache = [];
		$cacheObjectKeys = [];

		foreach($refObj->getMethods() as $refMethod) {
			$refAttributes = $this->getBindAttributes($refMethod);
			$methodName = $refMethod->getName();

			/** @var ?ReflectionNamedType $refReturn */
			$refReturn = $refMethod->getReturnType();
			if(!$refReturn instanceof ReflectionNamedType) {
				continue;
			}
			$refReturnName = $refReturn?->getName();

			foreach($refAttributes as $refAttr) {
				$bindKey = $this->getBindKey($refAttr, $refMethod);
				$attributeCache[$bindKey] = fn(object $object):null|iterable|string
					=> $this->nullableStringOrIterable($object->$methodName());
				if(class_exists($refReturnName)) {
					$cacheObjectKeys[$bindKey] = $refReturnName;
				}
			}
		}

		foreach($refObj->getProperties() as $refProp) {
			$propName = $refProp->getName();

			if($refAttributes = $this->getBindAttributes($refProp)) {
				foreach($refAttributes as $refAttr) {
					$bindKey = $this->getBindKey($refAttr);
// TODO: Test for object type in object property.
					$attributeCache[$bindKey]
						= fn(object $object, $key):null|iterable|string => $this->nullableStringOrIterable($object->$propName);
				}
			}
			elseif($refProp->isPublic()) {
				$bindKey = $propName;

				/** @var ?ReflectionNamedType $refType */
				$refType = $refProp->getType();
				$refTypeName = $refType?->getName();
				$attributeCache[$bindKey]
					= fn(object $object, $key):null|iterable|string => isset($object->$key) ? $this->nullableStringOrIterable($object->$key) : null;
				if(class_exists($refTypeName)) {
					$cacheObjectKeys[$bindKey] = $refTypeName;
				}
			}
		}

		if(empty($attributeCache)) {
			$this->nonBindableClassMap[$object::class] = true;
			return false;
		}

		$attributeCache = $this->expandObjects(
			$attributeCache,
			$cacheObjectKeys,
		);

		$this->bindableClassMap[$classString] = $attributeCache;
		return true;
	}

	/**
	 * @param array<string, Closure> $cache
	 * @param array<string, class-string> $objectKeys
	 * @return array<string, Closure>
	 */
	private function expandObjects(array $cache, array $objectKeys):array {
		if(empty($objectKeys)) {
			return $cache;
		}

		foreach($cache as $key => $closure) {
			if($objectType = $objectKeys[$key] ?? null) {
				$refClass = new ReflectionClass($objectType);
				if($this->isBindable($refClass)) {
					$bindable = $this->bindableClassMap[$objectType];
					foreach($bindable as $bindableKey => $bindableClosure) {
						$cache["$key.$bindableKey"] = $bindableClosure;
					}
				}

//				unset($cache[$key]);
			}
		}

		return $cache;
	}

	/** @return array<string, string> */
	public function convertToKvp(object $object):array {
		$kvp = [];

		if($object instanceof stdClass) {
			foreach(get_object_vars($object) as $key => $value) {
				if(is_null($value)) {
					$kvp[$key] = null;
				}
				elseif(is_iterable($value)) {
					$kvp[$key] = $value;
				}
				else {
					$kvp[$key] = (string)$value;
				}
			}
			return $kvp;
		}

		if(!$this->isBindable($object)) {
			return [];
		}

		$className = $object::class;
		foreach($this->bindableClassMap[$className] as $key => $closure) {
			$objectToExtract = $object;
			$deepKey = $key;
			$deepestKey = $key;
			while(str_contains($deepKey, ".")) {
				$propName = strtok($deepKey, ".");
				$deepKey = substr($deepKey, strpos($deepKey, ".") + 1);
				$deepestKey = $deepKey;
// TODO: This "get*()" function should not be hard coded here - it should load the appropriate
// Bind/BindGetter by matching the correct Attribute.
				$bindFunc = "get" . ucfirst($propName);
				$objectToExtract = $objectToExtract->$propName ?? $objectToExtract->$bindFunc();
			}

			$value = $closure($objectToExtract, $deepestKey);
			if(is_null($value)) {
				$kvp[$key] = null;
			}
			elseif(is_iterable($value)) {
				$kvp[$key] = $value;
			}
			else {
				$kvp[$key] = (string)$value;
			}
		}

		return $kvp;
	}

	/** @return array<ReflectionAttribute<Bind|BindGetter>> */
	private function getBindAttributes(ReflectionMethod|ReflectionProperty $ref):array {
		return array_filter(
			$ref->getAttributes(),
			fn(ReflectionAttribute $refAttr) =>
				$refAttr->getName() === Bind::class
				|| $refAttr->getName() === BindGetter::class
		);
	}

	/** @param ReflectionAttribute<Bind|BindGetter> $refAttr */
	private function getBindKey(
		ReflectionAttribute $refAttr,
		?ReflectionMethod $refMethod = null,
	):string {
		if($refAttr->getName() === BindGetter::class && $refMethod) {
			$methodName = $refMethod->getName();
			if(!str_starts_with($methodName, "get")) {
				throw new BindGetterMethodDoesNotStartWithGetException(
					"Method $methodName has the BindGetter Attribute, but its name doesn't start with \"get\". For help, see https://www.php.gt/domtemplate/bindgetter"
				);
			}
			return lcfirst(
				substr($methodName, 3)
			);
		}

		return $refAttr->getArguments()[0];
	}

	/** @return null|string|array<int|string, mixed> */
	private function nullableStringOrIterable(mixed $value):null|iterable|string {
		if(is_scalar($value)) {
			return $value;
		}
		elseif(is_iterable($value)) {
			return $value;
		}
		elseif(is_object($value)) {
			if($value instanceof Stringable || method_exists($value, "__toString")) {
				return (string)$value;
			}
		}

		return null;
	}
}

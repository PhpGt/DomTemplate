<?php
namespace Gt\DomTemplate;

use ReflectionAttribute;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;

class BindableCache {
	/**
	 * @var array<string, array<string, callable>> Outer array key is the
	 * fully-qualified class name, inner array key is the bind key, callable
	 * is the method that returns the bind value.
	 */
	private array $classAttributes;
	/**
	 * @var array<string, bool> A cache of class names that are known to
	 * NOT be bindable (to avoid having to check with reflection each time).
	 */
	private array $nonBindableClasses;

	public function __construct() {
		$this->classAttributes = [];
		$this->nonBindableClasses = [];
	}

	public function isBindable(object $object):bool {
		if(isset($this->classAttributes[$object::class])) {
			return true;
		}

		if(isset($this->nonBindableClasses[$object::class])) {
			return false;
		}

// Reflection is SLOW! The two checks above ensure that this step is only done
// once per class (not object).
		$refObj = new ReflectionObject($object);
		$attributeCache = [];
		foreach($refObj->getMethods() as $refMethod) {
			$refAttributes = $this->getBindAttributes($refMethod);
			$methodName = $refMethod->getName();

			foreach($refAttributes as $refAttr) {
				$bindKey = $refAttr->getArguments()[0];
				$attributeCache[$bindKey]
					= fn(object $object) => $object->$methodName();
			}
		}
		foreach($refObj->getProperties() as $refProp) {
			$refAttributes = $this->getBindAttributes($refProp);
			$propName = $refProp->getName();

			foreach($refAttributes as $refAttr) {
				$bindKey = $refAttr->getArguments()[0];
				$attributeCache[$bindKey]
					= fn(object $object) => $object->$propName;
			}
		}

		if(empty($attributeCache)) {
			$this->nonBindableClasses[$object::class] = true;
			return false;
		}

		$this->classAttributes[$object::class] = $attributeCache;
		return true;
	}

	/** @return array<string, string> */
	public function convertToKvp(object $object):array {
		$kvp = [];

		foreach($this->classAttributes[$object::class] as $key => $closure) {
			$kvp[$key] = $closure($object);
		}

		return $kvp;
	}

	private function getBindAttributes(ReflectionMethod|ReflectionProperty $ref):array {
		return array_filter(
			$ref->getAttributes(),
			fn(ReflectionAttribute $refAttr) =>
				$refAttr->getName() === Bind::class
		);
	}
}

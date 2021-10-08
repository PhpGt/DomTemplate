<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Stringable;

class ListBinder {
	public function __construct(
		private TemplateCollection $templateCollection
	) {
	}

	/** @param Iterator<mixed> $listData */
	public function bindListData(
		iterable $listData,
		Document|Element $context,
		?string $templateName = null
	):int {
		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		if($this->isEmpty($listData)) {
			$this->clearTemplateParentHTML($context, $templateName);
			return 0;
		}

		$templateItem = $this->templateCollection->get(
			$context,
			$templateName
		);

		$binder = new ElementBinder();
		$nestedCount = 0;
		$i = -1;
		foreach($listData as $listKey => $listItem) {
			$i++;
			$t = $templateItem->insertTemplate();

// If the $listItem's first value is iterable, then treat this as a nested list.
			if($this->isNested($listItem)) {
				$binder->bind(null, $listKey, $t);
				$nestedCount += $this->bindListData(
					$listItem,
					$t,
					$templateName
				);
				continue;
			}

			if($this->hasBindAttributes($listItem)) {
				$binder->bindMethodPropertyAttributes($listItem, $t);
			}
			else {
				if(is_object($listItem) && method_exists($listItem, "asArray")) {
					$listItem = $listItem->asArray();
				}

				if($this->isKVP($listItem)) {
					foreach($listItem as $key => $value) {
						$binder->bind($key, $value, $t);

						if($this->isNested($value)) {
							$binder->bind(null, $key, $t);
							$nestedCount += $this->bindListData(
								$value,
								$t,
								$templateName
							);
						}
					}
				}
				else {
					$binder->bind(null, $listItem, $t);
				}
			}
		}

		return $nestedCount + $i + 1;
	}

	/** @param Iterator<mixed>|array<mixed> $listData */
	private function isEmpty(iterable $listData):bool {
		if(is_array($listData)) {
			return is_null(array_key_first($listData));
		}
		else {
			/** @var Iterator<mixed> $listData */
			$listData->rewind();
			return !$listData->valid();
		}
	}

	private function clearTemplateParentHTML(
		Element $context,
		?string $templateName
	):void {
		$template = $this->templateCollection->get($context, $templateName);
		$parent = $template->getTemplateParent();
		$parent->innerHTML = trim($parent->innerHTML);
	}

	private function isKVP(mixed $item):bool {
		if(is_scalar($item)) {
			return false;
		}

		if($item instanceof Stringable) {
			return false;
		}

		return true;
	}

	private function hasBindAttributes(mixed $item):bool {
		if(is_scalar($item) || is_array($item)) {
			return false;
		}

		/** @var array<ReflectionAttribute<object>> $attributeList */
		$attributeList = [];

		$refClass = new ReflectionClass($item);
		foreach($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
			array_push($attributeList, ...$refMethod->getAttributes());
		}

		foreach($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $refProperty) {
			array_push($attributeList, ...$refProperty->getAttributes());
		}

		foreach($attributeList as $attribute) {
			if($attribute->getName() === Bind::class) {
				return true;
			}
		}

		return false;
	}

	private function isNested(mixed $item):bool {
		if(is_array($item)) {
			$key = array_key_first($item);
			return is_int($key) || is_iterable($item[$key]);
		}
		elseif($item instanceof Iterator) {
			return true;
		}

		return false;
	}
}

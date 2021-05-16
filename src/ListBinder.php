<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class ListBinder {
	public function __construct(
		private TemplateCollection $templateCollection
	) {
	}

	/**
	 * @param iterable $listData
	 * @param Document|Element $context
	 * @param string|null $templateName
	 * @return int
	 */
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
		$i = -1;
		foreach($listData as $i => $listItem) {
			$t = $templateItem->insertTemplate();

			if($this->hasBindAttributes($listItem)) {
				$binder->handleBindAttributes($listItem, $t);
			}
			elseif($this->isKVP($listItem)) {
				foreach($listItem as $key => $value) {
					$binder->bind($key, $value, $t);
				}
			}
			else {
				$binder->bind(null, $listItem, $t);
			}
		}

		return $i + 1;
	}

	private function isEmpty(iterable $listData):bool {
		if(is_array($listData)) {
			return is_null(array_key_first($listData));
		}
		else {
			/** @var Iterator $listData */
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

		if(is_array($item)) {
			$firstKey = array_key_first($item);

			if(is_string($firstKey)) {
				return true;
			}
			return false;
		}

		if($item instanceof Iterator) {
			return true;
		}
		elseif(is_object($item)) {
			return true;
		}

		return true;
	}

	private function hasBindAttributes(mixed $item):bool {
		if(is_scalar($item) || is_array($item)) {
			return false;
		}

		/** @var array<ReflectionAttribute> $attributeList */
		$attributeList = [];

		$refClass = new ReflectionClass($item);
		foreach($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
			array_push($attributeList, ...$refMethod->getAttributes());
		}

		foreach($refClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $refProperty) {
			array_push($attributeList, ...$refProperty->getAttributes());
		}

		foreach($attributeList as $attribute) {
			if($attribute->getName() === Bind::class) {
				return true;
			}
		}

		return false;
	}
}

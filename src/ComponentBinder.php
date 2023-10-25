<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;

class ComponentBinder extends DocumentBinder {
	public function __construct(
		private Element $componentElement,
		Document $document,
		array $config = [],
		?ElementBinder $elementBinder = null,
		?PlaceholderBinder $placeholderBinder = null,
		?TableBinder $tableBinder = null,
		?ListBinder $listBinder = null,
		?ListElementCollection $templateCollection = null,
		?BindableCache $bindableCache = null,
	) {
		parent::__construct(
			$document,
			$config,
			$elementBinder,
			$placeholderBinder,
			$tableBinder,
			$listBinder,
			$templateCollection,
			$bindableCache,
		);
	}

	public function bindList(
		iterable $listData,
		?Element $context = null,
		?string $templateName = null
	):int {
		if($context) {
			$this->checkElementContainedWithinComponent($context);
		}
		else {
			$context = $this->componentElement;
		}

		return parent::bindList($listData, $context, $templateName);
	}

	protected function bind(
		?string $key,
		mixed $value,
		?Element $context = null
	):void {
		if($context) {
			$this->checkElementContainedWithinComponent($context);
		}
		else {
			$context = $this->componentElement;
		}

		if(is_callable($value) && !is_string($value)) {
			$value = call_user_func($value);
		}

		$this->elementBinder->bind($key, $value, $context);
		$this->placeholderBinder->bind($key, $value, $context);
	}

	private function checkElementContainedWithinComponent(Element $context):void {
		if($this->componentElement !== $context && !$this->componentElement->contains($context)) {
			throw new ComponentDoesNotContainContextException(
				"<{$this->componentElement->tagName}> does not contain requested <$context->tagName>."
			);
		}
	}
}

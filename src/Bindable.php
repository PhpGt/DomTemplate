<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element as BaseElement;

trait Bindable {
	public function bind(iterable $data, string $templateName = null):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		$this->bindExisting($element, $data, $templateName);
		$this->bindTemplates($element, $data, $templateName);
	}

	protected function bindExisting(
		BaseElement $parent,
		iterable $data,
		string $templateName = null
	):void {
		$elementsWithBindAttribute = $parent->xPath(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]"
		);

		foreach($elementsWithBindAttribute as $element) {
			$this->setData($element, $data);
		}
	}

	protected function bindTemplates(
		BaseElement $element,
		iterable $data,
		string $templateName = null
	):void {
		$templateFragmentMap = $this->templateFragmentMap ?? [];
		foreach($templateFragmentMap as $name => $fragment) {
			if($name !== $templateName) {
				continue;
			}

		}
	}

	protected function setData(BaseElement $element, iterable $data):void {
		foreach($element->attributes as $attr) {
			$matches = [];
			if(!preg_match("/(?:data-bind:)(.+)/",
			$attr->name,$matches)) {
				continue;
			}

			$key = $attr->value;
			$dataValue = $data[$key];

			switch($matches[1]) {
			case "html":
				$element->innerHTML = $dataValue;
				break;

			case "text":
				$element->innerText = $dataValue;
				break;
			}
		}
	}
}
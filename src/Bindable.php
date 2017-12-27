<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
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

			$key = $this->getKeyFromAttribute($element, $attr);
			if(!isset($data[$key])) {
				throw new BoundDataNotSetException($key);
			}
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

	protected function getKeyFromAttribute(BaseElement $element, Attr $attr):string {
		$key = $attr->value;

		if($key[0] === "@") {
			$key = substr($key, 1);
			$attributeValue = $element->getAttribute($key);
			if(is_null($attributeValue)) {
				throw new BoundAttributeDoesNotExistException($attr->name);
			}

			return $attributeValue;
		}

		return $key;
	}
}
<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Attr;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\HTMLCollection;

trait Bindable {
	public function bind(iterable $data):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		$data = $this->wrapData($data);

		$this->bindExisting($element, $data);
		$this->bindTemplates(
			$element,
			$data
		);
		$this->cleanBindAttributes($element);
	}

	protected function bindExisting(
		DOMNode $parent,
		iterable $data
	):void {
		$childrenWithBindAttribute = $this->getChildrenWithBindAttribute($parent);

		foreach($childrenWithBindAttribute as $element) {
			$this->setData($element, $data);
		}
	}

	protected function bindTemplates(
		BaseElement $element,
		iterable $data
	):void {
		/** @var DocumentFragment[] $templateChildren */
		$templateChildren = $this->getRootDocument()->getNamedTemplateChildren(
			$element->getNodePath()
		);

		foreach($data as $rowNumber => $row) {
			foreach($templateChildren as $childNumber => $fragment) {
				$newNode = $fragment->insertTemplate();
				$this->bindExisting($newNode, $row);
			}
		}
	}

	protected function setData(BaseElement $element, iterable $data):void {
		$data = $this->unwrapData($data);

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

			case "value":
				$element->value = $dataValue;
				break;

			default:
				throw new InvalidBindProperty($matches[1]);
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

	protected function wrapData(iterable $data):iterable {
		if(!isset($data[0])) {
			$data = [$data];
		}

		return $data;
	}

	protected function unwrapData(iterable $data):iterable {
		if(isset($data[0])) {
			$data = $data[0];
		}

		return $data;
	}

	protected function getTemplateNamesForElement(BaseElement $element):array {
		$templateNames = [];
		$nodePath = $element->getNodePath();

		foreach($this->templateFragmentMap as $key => $templateFragment) {
			if(strpos($key, $nodePath) === 0) {
				$templateNames []= $key;
			}
		}

		return $templateNames;
	}

	protected function getChildrenWithBindAttribute(BaseElement $parent):HTMLCollection {
		return $parent->xPath(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]"
		);
	}

	protected function cleanBindAttributes(BaseElement $element):void {
		$elementsToClean = [$element];
		$childrenWithBindAttribute = $this->getChildrenWithBindAttribute($element);
		foreach($childrenWithBindAttribute as $child) {
			$elementsToClean []= $child;
		}

		foreach($elementsToClean as $cleanMe) {
			foreach($cleanMe->attributes as $attr) {
				if(strpos($attr->name, "data-bind") === 0) {
					$cleanMe->removeAttribute($attr->name);
				}
			}
		}
	}
}
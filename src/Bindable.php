<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Element as BaseElement;
use DOMNode;
use Gt\Dom\HTMLCollection;

trait Bindable {
	public function bind(iterable $data, string $templateName = null):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		$this->bindExisting($element, $data);
		$this->bindTemplates(
			$element,
			$data,
			$templateName
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

		$this->bindAttributes($parent, $data);
	}

	protected function bindAttributes(BaseElement $element, iterable $data):void {
		foreach($element->attributes as $attr) {
			preg_match(
				"/{(.+)}/",
				$attr->value,
				$matches
			);
			if(empty($matches)) {
				continue;
			}

			list($placeholder, $dataKey) = $matches;

			if(!isset($data[$dataKey])) {
				continue;
			}

			$attr->value = str_replace(
				$placeholder,
				$data[$dataKey],
				$attr->value
			);
		}
	}

	protected function bindTemplates(
		DOMNode $element,
		iterable $data,
		string $templateName = null
	):void {
		$namesToMatch = [];

		if(is_null($templateName)) {
			$namesToMatch []= $element->getNodePath();

		}
		else {
			$namesToMatch []= $templateName;
		}

		/** @var HTMLDocument $rootDocument */
		$rootDocument = $this->getRootDocument();
		/** @var DocumentFragment[] $templateChildren */
		$templateChildren = $rootDocument->getNamedTemplateChildren(
			...$namesToMatch
		);

		foreach($data as $rowNumber => $row) {
			foreach($templateChildren as $childNumber => $fragment) {
				$insertInto = null;

				if($fragment->templateParentNode !== $element) {
					$insertInto = $element;
				}

				$newNode = $fragment->insertTemplate($insertInto);
				$this->bindExisting($newNode, $row);
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
			$bindProperty = $matches[1];

			if($bindProperty === "class") {
				$this->handleClassData(
					$attr,
					$element,
					$data
				);
			}
			else {
				$this->handlePropertyData(
					$attr,
					$bindProperty,
					$element,
					$data
				);
			}
		}
	}

	protected function handlePropertyData(
		Attr $attr,
		string $bindProperty,
		BaseElement $element,
		iterable $data
	):void {
		$dataKeyMatch = $this->getKeyFromAttribute($element, $attr);
		$dataValue = $dataKeyMatch->getValue($data) ?? "";

		switch($bindProperty) {
		case "html":
		case "innerhtml":
			$element->innerHTML = $dataValue;
			break;

		case "text":
		case "innertext":
		case "textcontent":
			$element->innerText = $dataValue;
			break;

		case "value":
			$element->value = $dataValue;
			break;

		default:
			$element->setAttribute($bindProperty, $dataValue);
			break;
		}
	}

	protected function handleClassData(
		Attr $attr,
		BaseElement $element,
		iterable $data
	):void {
		$classList = explode(" ", $attr->value);
		$this->setClassFromData($element, $data, ...$classList);
	}

	protected function setClassFromData(
		BaseElement $element,
		iterable $data,
		string...$classList
	):void {
		foreach($classList as $class) {
			if(!strstr($class, ":")) {
				$class = "$class:$class";
			}

			list($keyMatch, $className) = explode(":", $class);

			if(!isset($data[$keyMatch])) {
				continue;
			}

			if($data[$keyMatch]) {
				$element->classList->add($className);
			}
			else {
				$element->classList->remove($className);
			}
		}
	}

	protected function getKeyFromAttribute(BaseElement $element, Attr $attr):DataKeyMatch {
		$required = true;
		$key = $attr->value;

		if($key[0] === "?") {
			$required = false;
			$key = substr($key, 1);
		}

		if($key[0] === "@") {
			$key = substr($key, 1);
			$attributeValue = $element->getAttribute($key);
			if(is_null($attributeValue)) {
				throw new BoundAttributeDoesNotExistException($attr->name);
			}

			$key = $attributeValue;
		}

		return new DataKeyMatch($key, $required);
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

	protected function getChildrenWithBindAttribute(DOMNode $parent):HTMLCollection {
		return $parent->xPath(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]"
		);
	}

	protected function cleanBindAttributes(DOMNode $element):void {
		$elementsToClean = [$element];
		$childrenWithBindAttribute = $this->getChildrenWithBindAttribute($element);
		foreach($childrenWithBindAttribute as $child) {
			$elementsToClean []= $child;
		}

		foreach($elementsToClean as $cleanMe) {
			if(!$cleanMe->attributes) {
				continue;
			}

			foreach($cleanMe->attributes as $attr) {
				if(strpos($attr->name, "data-bind") === 0) {
					$cleanMe->removeAttribute($attr->name);
				}
			}
		}
	}
}

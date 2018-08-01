<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Element as BaseElement;
use DOMNode;
use Gt\Dom\HTMLCollection;
use stdClass;

trait Bindable {
	public function bind($data, string $templateName = null):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		$this->injectDataIntoAttributeValues($element, $data);

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
		$data
	):void {
		$childrenWithBindAttribute = $this->getChildrenWithBindAttribute($parent);

		foreach($childrenWithBindAttribute as $element) {
			$this->setData($element, $data);
		}
	}

	protected function bindTemplates(
		DOMNode $element,
		$data,
		string $templateName = null
	):void {
		if($element instanceof \DOMDocumentFragment) {
			return;
		}

		$namesToMatch = [];

		if(is_null($templateName)) {
			$namesToMatch []=
				$element->templateParentId
				?? $element->getNodePath();

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

		foreach($data as $rowIndex => $row) {
			foreach($templateChildren as $childNumber => $fragment) {
				$insertInto = null;

				if($fragment->templateParentNode !== $element) {
					$insertInto = $element;
				}

				$newNode = $fragment->insertTemplate($insertInto);
				$this->bindExisting($newNode, $row);
				$this->injectDataIntoAttributeValues(
					$newNode,
					$row
				);
			}
		}

		if(is_null($rowIndex)) {
			$trimmed = trim($element->innerHTML);
			if($trimmed === "") {
				$element->innerHTML = "";
			}
		}
	}

	protected function setData(BaseElement $element, $data):void {
		if(is_array($data)) {
			$data = $this->convertArrayToObject($data);
		}

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
		$data
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

	protected function injectDataIntoAttributeValues(
		DOMNode $element,
		$data
	):void {
		if(is_array($data)) {
			$data = (object)$data;
		}

		foreach($element->xPath("//*[@*[contains(.,'{')]]")
		as $elementWithBraceInAttributeValue) {
			foreach($elementWithBraceInAttributeValue->attributes as $attr) {
				preg_match_all(
					"/{([^}]+)}/",
					$attr->value,
					$matches
				);

				if(empty($matches[0])) {
					continue;
				}

				foreach($matches[0] as $i => $match) {
					$key = $matches[1][$i];

					if(!isset($data->{$key})) {
						continue;
					}


					$value = str_replace(
						$match,
						$data->{$key},
						$attr->value
					);

					$attr->ownerElement->setAttribute($attr->name, $value);
				}
			}
		}
	}

	protected function handleClassData(
		Attr $attr,
		BaseElement $element,
		$data
	):void {
		$classList = explode(" ", $attr->value);
		$this->setClassFromData(
			$element,
			$data, ...
			$classList
		);
	}

	protected function setClassFromData(
		BaseElement $element,
		$data,
		string...$classList
	):void {
		foreach($classList as $class) {
			if(!strstr($class, ":")) {
				$class = "$class:$class";
			}

			list($keyMatch, $className) = explode(":", $class);

			if(!isset($data->{$keyMatch})) {
				continue;
			}

			if($data->{$keyMatch}) {
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

			$attributesToRemove = [];
			foreach($cleanMe->attributes as $attr) {
				if(strpos($attr->name, "data-bind") === 0) {
					$attributesToRemove []= $attr->name;
				}
			}

			foreach($attributesToRemove as $attrName) {
				$cleanMe->removeAttribute($attrName);
			}
		}
	}

	protected function convertArrayToObject(array $array) {
		$object = new StdClass();
		foreach($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}
}

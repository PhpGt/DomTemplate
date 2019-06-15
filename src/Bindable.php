<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr as BaseAttr;
use Gt\Dom\Element as BaseElement;
use DOMNode;
use StdClass;
use Gt\Dom\HTMLCollection as BaseHTMLCollection;

/**
 * In WebEngine, all Elements in the DOM are Bindable by default. A Bindable
 * Element is a ParentNode that can have data injected into it via this Trait's
 * bind* functions.
 */
trait Bindable {
	/**
	 * Bind a single key-value-pair within $this Element.
	 * Elements state their bindable key using the data-bind HTML attribute.
	 * There may be multiple Elements with the matching attribute, in which
	 * case they will all have their data set.
	 */
	public function bindKeyValue(
		string $key,
		string $value
	):void {
		$this->injectBoundProperty($key, $value);
		$this->injectAttributePlaceholder($key, $value);
	}

	/**
	 * Bind multiple key-value-pairs within $this Element, calling
	 * bindKeyValue for each key-value-pair in the iterable $kvp object.
	 * @see self::bindKeyValue
	 */
	public function bindData(
		iterable $kvp
	):void {
		foreach($kvp as $key => $value) {
			$this->bindKeyValue($key, $value);
		}
	}

	/**
	 * $kvpList is a nested iterable object. The outer iterable contains
	 * zero or more inner iterables. The inner iterables contain data in the
	 * form of an iterable key-value-pair array (typically an associative
	 * array or data object).
	 *
	 * For each iteration of the outer iterable object, a new clone will be
	 * made of the template element with the given name. The cloned element
	 * will have the inner iterable data bound to it before being added into
	 * the DOM in the position that it was originally extracted.
	 *
	 * TODO: Enforce the following:
	 * When $templateName is not provided, the data within $kvpList will be
	 * bound to an element that has a data-template attribute with no value.
	 * If there are multiple un-named template elements, an exception is
	 * thrown - in this case, you will need to use bindNestedList
	 *
	 * @throws NamelessTemplateSpecificityException
	 * @see self::bindNestedList
	 */
	public function bindList(
		iterable $kvpList,
		string $templateName = null
	):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}
		/** @var HTMLDocument $document */
		$document = $element->ownerDocument;

		foreach($kvpList as $data) {
			if(is_null($templateName)) {
				$t = $document->getUnnamedTemplate($element);
			}
			else {
				$t = $document->getNamedTemplate($templateName);
			}

			$t->bindData($data);
			$t->insertTemplate();
		}
	}

	/**
	 * When data needs binding to a nested DOM structure, a BindIterator is
	 * necessary to link each child list with the correct template.
	 *
	 * TODO: Implement.
	 */
	public function bindNestedList(BindIterator $iterator):void {

	}

	/**
	 * Within the current element, iterate all children that have a
	 * matching data-bind:* attribute, and inject the provided $value
	 * into the according property value.
	 */
	protected function injectBoundProperty(
		string $key,
		string $value
	):void {
		$children = $this->getChildrenWithBindAttribute();

		foreach($children as $child) {
			foreach($child->attributes as $attr) {
				/** @var Attr $attr */
// Skip attributes that do not have a bindProperty set (the text that comes after
// the colon in data-bind:*
				$matches = [];
				if(!preg_match(
					"/(?:data-bind:)(?P<bindProperty>.+)/",
					$attr->name,
					$matches
				)) {
					continue;
				}

				$element = $attr->ownerElement;
				$keyToSet = $this->getKeyToSet($attr);
				$attr->ownerDocument->storeBoundAttribute($attr);

// Skip attributes whose value does not equal the key that we are setting.
				if($keyToSet !== $key) {
					continue;
				}

				$bindProperty = $matches["bindProperty"];

// The "class" property behaves differently to others, as it is represented by
// a StringMap rather than a single value.
				if($bindProperty === "class") {
					$element->classList->toggle($value);
				}
				else {
					$this->setPropertyValue(
						$element,
						$bindProperty,
						$value
					);
				}
			}
		}
	}

	/**
	 * The data-bind syntax can reference another attribute's value to use
	 * as the key to set, using the @ syntax. For example,
	 * data-bind:text="@id" will bind the element's text content with the
	 * data value with the key of the element's id attribute value.
	 */
	protected function getKeyToSet(
		BaseAttr $attr
	):string {
		$keyToSet = $attr->value;

		if($keyToSet[0] === "@") {
			$lookupAttribute = substr($keyToSet, 1);
			$keyToSet = $attr->ownerElement->getAttribute(
				$lookupAttribute
			);

			if(is_null($keyToSet)) {
				throw new BoundAttributeDoesNotExistException(
					$lookupAttribute
				);
			}
		}

		return $keyToSet;
	}

	protected function injectAttributePlaceholder(
		string $key,
		string $value
	):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		foreach($element->xPath("//*[@*[contains(.,'{')]]")
		as $elementWithBraceInAttributeValue) {
			foreach($elementWithBraceInAttributeValue->attributes
			as $attr) {
				/** @var Attr $attr */
				preg_match_all(
					"/{(?P<bindProperties>[^}]+)}/",
					$attr->value,
					$matches
				);

				$bindProperties = $matches["bindProperties"] ?? null;

				if(!in_array($key, $bindProperties)) {
					continue;
				}

				if(is_null($bindProperties)) {
					continue;
				}

				foreach($bindProperties as $i => $bindProperty) {
					$attr->value = str_replace(
						"{" . "$key" . "}",
						$value,
						$attr->value
					);
				}
			}
		}
	}

	protected function setPropertyValue(
		BaseElement $element,
		string $bindProperty,
		string $value
	):void {
		switch($bindProperty) {
		case "html":
		case "innerhtml":
		case "innerHtml":
		case "innerHTML":
			$element->innerHTML = $value;
			break;

		case "text":
		case "innertext":
		case "innerText":
			$element->innerText = $value;
			break;
		default:
			$element->setAttribute($bindProperty, $value);
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

		foreach($data as $rowIndex => $row) {
			foreach($templateChildren as $childNumber => $fragment) {
				$insertInto = null;

				if($fragment->templateParentNode !== $element) {
					$insertInto = $element;
				}

				$newNode = $fragment->insertTemplate($insertInto);
				$this->injectDataIntoBindProperties($newNode, $row);
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
			if(!preg_match(
				"/(?:data-bind:)(.+)/",
				$attr->name,
				$matches)
			) {
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
		BaseAttr $attr,
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

	protected function handleClassData(
		BaseAttr $attr,
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

	protected function getChildrenWithBindAttribute():BaseHTMLCollection {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		return $element->xPath(
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

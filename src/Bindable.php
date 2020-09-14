<?php
namespace Gt\DomTemplate;

use ArrayObject;
use Gt\Dom\Attr as BaseAttr;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\HTMLCollection as BaseHTMLCollection;
use Iterator;

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
	public function bindKeyValue(?string $key, $value):void {
		if(is_null($value)) {
			$value = "";
		}

		if($value === false) {
			return;
		}
		if($value === true) {
			$value = $key;
		}

		$this->injectBoundData($key, $value);
		$this->injectBoundAttribute($key, $value);
	}

	/**
	 * Bind a single value to a data-bind element that has no matching
	 * attribute vale. For example, <p data-bind:text>Your text here</p>
	 * does not have an addressable attribute value for data-bind:text.
	 *
	 * @param mixed $value
	 */
	public function bindValue($value):void {
		$this->bindKeyValue(null, $value);
// Note, it's impossible to inject attribute placeholders without a key.
	}

	/**
	 * Bind multiple key-value-pairs within $this Element, calling
	 * bindKeyValue for each key-value-pair in the iterable $kvp object.
	 * @param array|object|BindObject|BindDataMapper $kvp
	 * @see self::bindKeyValue
	 */
	public function bindData($kvp):void {
		$assocArray = null;

		if($this->isIndexedArray($kvp)) {
			throw new IncompatibleBindDataException();
		}

		$assocArray = $this->convertKvpToAssocArray($kvp);

		foreach($assocArray as $key => $value) {
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
	 * When $templateName is not provided, the data within $kvpList will be
	 * bound to an element that has a data-template attribute with no value.
	 * If there are multiple un-named template elements, an exception is
	 * thrown - in this case, you will need to use bindNestedList
	 *
	 * @throws NamelessTemplateSpecificityException
	 * @see self::bindNestedList
	 */
	public function bindList(
		?iterable $kvpList,
		string $templateName = null
	):int {
		if(empty($kvpList)) {
			if($this->children->length === 0) {
				$this->innerHTML = "";
			}

			return 0;
		}

		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}
		/** @var HTMLDocument $document */
		$document = $element->ownerDocument;

		$fragment = $document->createDocumentFragment();

		if(is_null($templateName)) {
			$templateElement = $document->getUnnamedTemplate(
				$element,
				true,
				false
			);
		}
		else {
			$templateElement = $document->getNamedTemplate(
				$templateName
			);
		}

		$templateParent = $templateElement->templateParentNode;
		$parentPath = $templateParent->getNodePath();

		$count = 0;
		foreach($kvpList as $key => $data) {
			$count ++;
			$t = $templateElement->cloneNode(true);
			/** @var Element $insertedT */
			$insertedT = $fragment->appendChild($t);

			if(is_string($key)) {
				$insertedT->bindValue($key);
				$insertedT->bindKeyValue("_key", $key);
			}

			if($this->isBindableValue($data)) {
				$insertedT->bindValue($data);
			}
			else {
				$insertedT->bindData($data);
			}
		}

		$templateParent->appendChild($fragment);
		return $count;
	}

	public function bindNestedList(
		?iterable $nestedKvpList,
		bool $requireMatchingTemplatePath = false
	):int {
		if(empty($nestedKvpList)) {
			return 0;
		}

		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}
		/** @var HTMLDocument $document */
		$document = $element->ownerDocument;

		$templateParent = $document->getParentOfUnnamedTemplate(
			$element,
			$requireMatchingTemplatePath
		);

		$i = 0;
		foreach($nestedKvpList as $key => $value) {
			$i++;
			$t = $document->getUnnamedTemplate(
				$templateParent,
				false
			);

			$insertedTemplate = $templateParent->appendChild($t);

			if(is_string($key)) {
				$insertedTemplate->bindValue($key);
			}

			if(is_string($value)) {
				$insertedTemplate->bindValue($value);
			}
			elseif(is_iterable($value)) {
				$i += $insertedTemplate->bindNestedList(
					$value,
					true
				);
			}
			else {
				$assocArray = $this->convertKvpToAssocArray($value);

				foreach($assocArray as $dataKey => $dataValue) {
					if($this->isList($dataValue)) {
						$insertedTemplate->bindNestedList(
							$dataValue,
							true
						);
					}
					else {
						$insertedTemplate->bindKeyValue(
							$dataKey,
							$dataValue
						);
					}
				}
			}
		}

		return $i;
	}

	public function bindNames($kvp):void {
		$nameElements = $this->querySelectorAll("[name]");

		foreach($nameElements as $element) {
			$name = $element->name;
			if(!array_key_exists($name, $kvp)) {
				continue;
			}

			$element->value = $kvp[$name];
		}
	}

	/**
	 * Within the current element, iterate all children that have a
	 * matching data-bind:* attribute, and inject the provided $value
	 * into the according property value.
	 */
	protected function injectBoundData(
		?string $key,
		$value
	):void {
		foreach($this->getChildrenWithBindAttribute() as $child) {
			foreach($child->attributes as $attr) {
				/** @var Attr $attr */
// Skip attributes that do not have a bindProperty set (the text that
// comes after the colon in data-bind:*
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

				if($bindProperty === "property") {
					continue;
				}
				elseif($bindProperty === "class") {
					$element->classList->toggle($value);
				}
				else {
					$this->setPropertyValue(
						$element,
						$bindProperty,
						$value,
						$this->shouldIgnoreFalsey($attr)
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
	):?string {
		$keyToSet = $attr->value ?: null;

		if(is_null($keyToSet)) {
			return null;
		}

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
		elseif($keyToSet[0] === "?") {
			$keyToSet = substr($keyToSet, 1);
		}

		return $keyToSet;
	}

	protected function shouldIgnoreFalsey(BaseAttr $attr):bool {
		if(strlen($attr->value) === 0) {
			return false;
		}

		return $attr->value[0] === "?";
	}

	protected function injectBoundAttribute(
		?string $key,
		$value
	):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		foreach($element->xPath(".//*[@data-bind-attributes]") as $elementToBindAttributes) {
			foreach($elementToBindAttributes->attributes as $attr) {
				/** @var Attr $attr */

				$attrValue = $attr->value;
				if(is_null($key)) {
					$attrValue = str_replace(
						"{}",
						$value,
						$attrValue
					);

					$elementToBindAttributes->setAttribute(
						$attr->name,
						$attrValue
					);
				}

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

				$attrValue = str_replace(
					"{" . $key . "}",
					$value,
					$attrValue
				);

				$elementToBindAttributes->setAttribute($attr->name, $attrValue);
			}
		}
	}

	protected function setPropertyValue(
		BaseElement $element,
		string $bindProperty,
		string $value,
		bool $ignoreFalsey = false
	):void {
		if($ignoreFalsey) {
			if(!$value) {
				return;
			}
		}

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

		case "value":
			$tagName = strtoupper($element->tagName);
			switch($tagName) {
			case "SELECT":
			case "INPUT":
			case "TEXTAREA":
				$element->value = $value;
				break;
			default:
				$element->setAttribute($bindProperty, $value);
			}
			break;

		default:
			$element->setAttribute($bindProperty, $value);
		}
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

	protected function isIndexedArray($array):bool {
		if(!is_array($array)) {
			return false;
		}

		if($array == []) {
			return false;
		}

		$keys = array_keys($array);
		$isIndexed = true;

		foreach($keys as $key) {
			if(!is_int($key)) {
				$isIndexed = false;
			}
		}

		return $isIndexed;
	}

	protected function isAssociativeArray($array):bool {
		if(!is_array($array)) {
			return false;
		}

		if($array == []) {
			return true;
		}

		$keys = array_keys($array);
		$isAssociative = true;

		foreach($keys as $key) {
			if(!is_string($key)) {
				$isAssociative = false;
			}
		}

		return $isAssociative;
	}

	/**
	 * Does DomTemplate consider $data to be a data structure that
	 * represents a single BindableValue? A BindableValue is a single datum
	 * that does not have a "key-value" structure (just a "value").
	 */
	protected function isBindableValue($data):bool {
		return is_string($data)
		|| is_numeric($data)
		|| (is_object($data) && method_exists($data, "__toString"));
	}

	/**
	 * Does DomTemplate consider $data to be a BindableData structure that
	 * represents a "key-value" structure?
	 */
	protected function isBindableData($data):bool {
		if(is_null($data)) {
			return false;
		}

		if([] == $data) {
			return true;
		}

		if($data instanceof BindObject
		|| $data instanceof BindDataMapper) {
			return true;
		}

		if(is_object($data) && !is_iterable($data)) {
			return true;
		}

		if(is_array($data)) {
			$key = key($data);
			$firstItem = $data[$key];

			if($this->isBindableValue($firstItem)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does DomTemplate consider $data to be a List that represents multiple
	 * BindableData? A List is an iterable data structure that contains
	 * zero or more "key-value" structure.
	 */
	protected function isList($data):bool {
		$firstItem = null;

		if(is_array($data)) {
			$key = key($data);
			$firstItem = $data[$key];
		}
		elseif($data instanceof Iterator) {
			$firstItem = $data->current();
		}

		if(is_null($firstItem)) {
			return false;
		}

		return $this->isBindableData($firstItem)
			|| $this->isBindableValue($firstItem);
	}

	protected function convertKvpToAssocArray($kvp):array {
		if($this->isAssociativeArray($kvp)) {
			$assocArray = $kvp;
		}
		else {
			if($kvp instanceof BindDataMapper) {
				$assocArray = $kvp->bindDataMap();
			}
			elseif($kvp instanceof BindObject) {
				$assocArray = [];
				$prefix = "bind";
				foreach(get_class_methods($kvp) as $method) {
					if(strpos($method, $prefix) !== 0) {
						continue;
					}

					$key = lcfirst(
						substr(
							$method,
							strlen($prefix)
						)
					);

					$value = $kvp->$method();
					$assocArray[$key] = $value;
				}
			}
			elseif($kvp instanceof ArrayObject) {
				$assocArray = (array)$kvp;
			}
			elseif(is_object($kvp)) {
// Finally, assume the kvp is a Plain Old PHP Object (POPO).
				$assocArray = get_object_vars($kvp);
			}
			else {
				$assocArray = $kvp;
			}
		}

		return $assocArray;
	}
}

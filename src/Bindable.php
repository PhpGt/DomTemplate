<?php
namespace Gt\DomTemplate;

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
	public function bindKeyValue(
		?string $key,
		$value
	):void {
		if(is_null($value)) {
			$value = "";
		}

		$this->injectBoundProperty($key, $value);
		$this->injectAttributePlaceholder($key, $value);
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
	public function bindData(
		$kvp
	):void {
		$assocArray = null;

		if($this->isIndexedArray($kvp)
		|| $kvp instanceof Iterator) {
			throw new IncompatibleBindDataException();
		}

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
			elseif(is_object($kvp)) {
// Finally, assume the kvp is a Plain Old PHP Object (POPO).
				$assocArray = get_object_vars($kvp);
			}
			else {
				$assocArray = $kvp;
			}
		}

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
		iterable $kvpList,
		string $templateName = null
	):int {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}
		/** @var HTMLDocument $document */
		$document = $element->ownerDocument;

		$fragment = $document->createDocumentFragment();
		$templateParent = null;

		if(is_null($templateName)) {
			$templateElement = $document->getUnnamedTemplate(
				$element,
				true,
				false
			);
		}
		else {
			$templateElement = $document->getNamedTemplate($templateName);
		}

		$count = 0;
		foreach($kvpList as $i => $data) {
			$count ++;

			if(is_string($i)) {
				$this->bindValue($i);
			}

// TODO: Recursive call if value is iterable.
			$t = $templateElement->cloneNode(true);

			if(!$templateParent) {
				$templateParent = $templateElement->templateParentNode;
			}

			$t->bindData($data);
			$fragment->appendChild($t);
		}

		if(!is_null($templateParent)) {
			$templateParent->appendChild($fragment);
		}

		return $count;
	}

	/**
	 * When complex data needs binding to a nested DOM structure, a
	 * BindIterator is necessary to link each child list with the
	 * correct template.
	 */
	public function bindNestedList(
		iterable $data,
		bool $requireMatchingTemplatePath = false
	):int {
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
		foreach($data as $key => $value) {
			$i++;
			$t = $document->getUnnamedTemplate(
				$templateParent,
				false
			);

			if(is_string($key)) {
				$t->bindValue($key);
			}

			if(is_string($value)) {
				$t->bindValue($value);
			}

			$insertedTemplate = $templateParent->appendChild($t);

			if(is_iterable($value)) {
				$i += $insertedTemplate->bindNestedList(
					$value,
					true
				);
			}
		}

		return $i;
	}

	/**
	 * Within the current element, iterate all children that have a
	 * matching data-bind:* attribute, and inject the provided $value
	 * into the according property value.
	 */
	protected function injectBoundProperty(
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

	protected function injectAttributePlaceholder(
		?string $key,
		$value
	):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		foreach($element->xPath(".//*[@data-bind-parameters]") as $elementToBindAttributes) {
			foreach($elementToBindAttributes->attributes as $attr) {
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

				$attrValue = $attr->value;
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

		$children = $element->xPath(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]"
		);
		return $children;
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
}

<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr as BaseAttr;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\HTMLCollection as BaseHTMLCollection;

/**
 * In WebEngine, all Elements in the DOM are Bindable by default. A Bindable
 * Element is a ParentNode that can have data injected into it via this Trait's
 * bind* functions.
 */
trait Bindable {
	/**
	 * Alias of bindKeyValue.
	 */
	public function bind(?string $key, ?string $value):void {
		$this->bindKeyValue($key, $value);
	}

	/**
	 * Bind a single key-value-pair within $this Element.
	 * Elements state their bindable key using the data-bind HTML attribute.
	 * There may be multiple Elements with the matching attribute, in which
	 * case they will all have their data set.
	 */
	public function bindKeyValue(
		?string $key,
		?string $value
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
	 */
	public function bindValue(?string $value):void {
		$this->bindKeyValue(null, $value);
// Note, it's impossible to inject attribute placeholders without a key.
	}

	/**
	 * Bind multiple key-value-pairs within $this Element, calling
	 * bindKeyValue for each key-value-pair in the iterable $kvp object.
	 * @see self::bindKeyValue
	 */
	public function bindData(
		$kvp
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
				$t = $document->getUnnamedTemplate(
					$element,
					true,
					false
				);
			}
			else {
				$t = $document->getNamedTemplate($templateName);
			}

			$inserted = $t->insertTemplate();
			$inserted->bindData($data);
		}
	}

	/**
	 * When complex data needs binding to a nested DOM structure, a
	 * BindIterator is necessary to link each child list with the
	 * correct template.
	 */
	public function bindNestedList(
		iterable $data,
		bool $requireMatchingTemplatePath = false
	):void {
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

		foreach($data as $key => $value) {
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
				$insertedTemplate->bindNestedList(
					$value,
					true
				);
			}
		}
	}

	/**
	 * Within the current element, iterate all children that have a
	 * matching data-bind:* attribute, and inject the provided $value
	 * into the according property value.
	 */
	protected function injectBoundProperty(
		?string $key,
		?string $value
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

		return $keyToSet;
	}

	protected function injectAttributePlaceholder(
		?string $key,
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
}

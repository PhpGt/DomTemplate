<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\DOMTokenList;
use Gt\Dom\Element;
use Gt\Dom\Facade\DOMTokenListFactory;
use Gt\Dom\Node;
use Gt\Dom\XPathResult;

class DocumentBinder {
	public function __construct(
		private Document $document,
		private array $config = []
	) {
	}

	/**
	 * Applies the string value of $value anywhere within $context that
	 * has a data-bind attribute with no specified key.
	 */
	public function bindValue(
		mixed $value,
		Node $context = null
	):void {
		$this->bind(null, $value, $context);
	}

	/**
	 * Applies the string value of $value to any elements within $context
	 * that have the data-bind attribute matching the provided key.
	 */
	public function bindKeyValue(
		string $key,
		mixed $value,
		Node $context = null
	):void {
		$this->bind($key, $value, $context);
	}

	/**
	 * Binds multiple key-value-pairs to any matching elements within
	 * the $context element.
	 */
	public function bindData(
		mixed $kvp,
		Node $context = null
	):void {
		if($this->isIndexedArray($kvp)) {
			throw new IncompatibleBindDataException("bindData is only compatible with key-value-pair data, but it was passed an indexed array.");
		}

		foreach($kvp as $key => $value) {
			$this->bindKeyValue($key, $value, $context);
		}
	}

	private function bind(
		?string $key,
		mixed $value,
		?Node $context = null
	):void {
		if(!$context) {
			$context = $this->document;
		}

		foreach($this->evaluateDataBindElements($context) as $element) {
			/** @var Element $element */
			$this->processDataBindAttributes(
				$element,
				$key,
				$value
			);
		}
	}

	private function processDataBindAttributes(
		Element $element,
		?string $key,
		mixed $value
	) {
		foreach($element->attributes as $attrName => $attrValue) {
			if(!str_starts_with($attrName, "data-bind")) {
				continue;
			}

			if(!strstr($attrName, ":")) {
				$tag = $this->getHTMLTag($element);
				throw new InvalidBindPropertyException("$tag Element has a data-bind attribute with missing bind property - did you mean `data-bind:text`?");
			}

			$modifier = null;
			if(is_null($key)) {
// If there is no key specified, only bind the elements that don't have a
// specified key in their bind attribute's value.
				if(strlen($attrValue) > 0) {
					continue;
				}
			}
			else {
// If there is a key specified, and the bind attribute's value doesn't match,
// skip this attribute.
				$trimmedAttrValue = ltrim($attrValue, ":?@");
				$trimmedAttrValue = strtok($trimmedAttrValue, " ");
				if($key !== $trimmedAttrValue) {
					continue;
				}
				if($attrValue !== $trimmedAttrValue) {
					$modifier = $attrValue;
				}
			}

			$this->setBindProperty(
				$element,
				substr(
					$attrName,
					strpos($attrName, ":") + 1
				),
				$value,
				$modifier
			);
		}
	}

	/**
	 * This function actually mutates the Element. The type of mutation is
	 * defined by the value of $bindProperty. The default behaviour is to
	 * set the an attribute on $element where the attribute key is equal to
	 * $bindProperty and the attribute value is equal to $bindValue, however
	 * there are a few values of $bindProperty that affect this behaviour:
	 *
	 * 1) "text" will set the textContent of $element. Why "text" and
	 * not "textContent"? Because HTML attributes can't have uppercase
	 * characters, and this removes ambiguity.
	 * 2) "html" will set the innerHTML of $element. Same as above.
	 * 3) "class" will add the provided value as a class (rather than
	 * setting the class attribute and losing existing classes). The colon
	 * can be added to the bindKey to toggle, as explained in point 6 below.
	 * 4) "table" will create the appropriate columns and rows within the
	 * first <table> element within the element being bound.
	 * 5) "attr" will bind the attribute with the same name as the bindKey.
	 * 6) By default, the attribute matching $bindProperty will be set,
	 * according to these rules:
	 *    + If the bindKey is an alphanumeric string, the attribute will be
	 * 	set to the value of the matching bindValue.
	 *    + If the bindKey starts with a colon character ":", the attribute
	 * 	will be treated as a Token List, and the matching token will be
	 * 	added/removed from the attribute value depending on whether the
	 * 	$bindValue is true/false.
	 *    + If the bindKey starts with a question mark "?", the attribute
	 * 	will be toggled, depending on whether the $bindValue is
	 * 	true/false.
	 *
	 * With colon/question mark bind values, the value of the attribute will
	 * match the value of $bindValue - if a different attribute value is
	 * required, this can be specified after a space. For example:
	 * data-bind:class=":isSelected selected-item" will add/remove the
	 * "selected-item" class depending on the $bindValue's boolean value.
	 * @noinspection SpellCheckingInspection
	 */
	private function setBindProperty(
		Element $element,
		string $bindProperty,
		mixed $bindValue,
		?string $modifier = null
	):void {
		switch(strtolower($bindProperty)) {
		case "text":
		case "innertext":
		case "inner-text":
		case "textcontent":
		case "text-content":
			$element->textContent = $bindValue;
			break;

		case "html":
		case "innerhtml":
		case "inner-html":
			$element->innerHTML = $bindValue;
			break;

		case "class":
			if($modifier) {
				$this->handleModifier(
					$element,
					"class",
					$modifier,
					$bindValue
				);
			}
			else {
				$element->classList->add($bindValue);
			}
			break;

		default:
			if($modifier) {
				$this->handleModifier(
					$element,
					$bindProperty,
					$modifier,
					$bindValue
				);
			}
			else {
				$element->setAttribute($bindProperty, $bindValue);
			}

			break;
		}
	}

	private function getHTMLTag(Element $element):string {
		return "<" . strtolower($element->tagName) . ">";
	}

	private function evaluateDataBindElements(Document|Node|null $context):XPathResult {
		return $this->document->evaluate(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]",
			$context
		);
	}

	private function isIndexedArray(mixed $data):bool {
		if(!is_array($data)) {
			return false;
		}

		foreach(array_keys($data) as $key) {
			if(!is_int($key)) {
				return false;
			}
		}

		return true;
	}

	private function handleModifier(
		Element $element,
		string $attribute,
		string $modifier,
		mixed $bindValue
	):void {
		$modifierChar = $modifier[0];
		$modifierValue = substr($modifier, 1);
		if(false !== $spacePos = strpos($modifierValue, " ")) {
			$modifierValue = substr($modifierValue, $spacePos + 1);
		}

		switch($modifierChar) {
		case ":":
			$tokenList = $this->getTokenList($element, $attribute);
			if($bindValue) {
				$tokenList->add($modifierValue);
			}
			else {
				$tokenList->remove($modifierValue);
			}
			break;

		case "?":
			if($bindValue) {
				$element->setAttribute($attribute, "");
			}
			else {
				$element->removeAttribute($attribute);
			}
		}
	}

	private function getTokenList(
		Element $element,
		string $attribute
	):DOMTokenList {
		return DOMTokenListFactory::create(
			fn() => explode(" ", $element->getAttribute($attribute)),
			fn(string...$tokens) => $element->setAttribute($attribute, implode(" ", $tokens)),
		);
	}
}

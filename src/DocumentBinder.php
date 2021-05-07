<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\Node;

class DocumentBinder {
	public function __construct(
		private Document $document
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

	public function bindKeyValue(
		string $key,
		mixed $value,
		Node $context = null
	):void {
		$this->bind($key, $value, $context);
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
	 * setting the class attribute and losing existing classes).
	 * 3) ":class" will toggle the provided value as a class.
	 * 4) "?attr" will add/remove the attribute (for example, "?disabled"
	 * is useful for toggling an button's disabled attribute).
	 */
	private function setBindProperty(
		Element $element,
		string $bindProperty,
		mixed $bindValue
	):void {
		switch(strtolower($bindProperty)) {
		case "text":
			$element->textContent = $bindValue;
			break;

		default:
			$suggestedProperty = null;

			if(str_starts_with($bindProperty, "text")) {
				$suggestedProperty = "text";
			}

			$suggestionMessage = $suggestedProperty
				? " - did you mean `data-bind:$suggestedProperty`?"
				: ".";

			$tag = $this->getHTMLtag($element);
			throw new InvalidBindPropertyException("Unknown bind property `$bindProperty` on $tag Element$suggestionMessage");
		}
	}

	private function getHTMLTag(Element $element):string {
		return "<" . strtolower($element->tagName) . ">";
	}

	private function bind(
		?string $key,
		mixed $value,
		?Node $context = null
	):void {
		if(!$context) {
			$context = $this->document;
		}

		foreach($this->document->evaluate(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]",
			$context
		) as $element) {
			foreach($element->attributes as $attrName => $attrValue) {
				if(!str_starts_with($attrName, "data-bind")) {
					continue;
				}

				if(!strstr($attrName, ":")) {
					$tag = $this->getHTMLTag($element);
					throw new InvalidBindPropertyException("$tag Element has a data-bind attribute with missing bind property - did you mean `data-bind:text`?");
				}

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
					if($key !== $attrValue) {
						continue;
					}
				}

				$this->setBindProperty(
					$element,
					substr(
						$attrName,
						strpos($attrName, ":") + 1
					),
					$value
				);
			}
		}
	}
}

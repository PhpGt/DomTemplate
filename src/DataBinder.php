<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\Node;
use Stringable;

class DataBinder {
	public function __construct(
		private Document $document
	) {
	}

	/**
	 * Applies the string value of $value anywhere within $context that
	 * has a data-bind attribute with no specified key.
	 */
	public function bindValue(
		string|Stringable $value,
		Node $context = null
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

				if(strlen($attrValue) > 0) {
// Skip binding of data that specified as key, as bindValue will only bind to
// elements that have no specified key.
					continue;
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

	public function bindKeyValue(
		string $key,
		Stringable|string $value
	):void {

	}

	private function setBindProperty(
		Element $element,
		string $bindProperty,
		string|Stringable $value
	):void {
		switch(strtolower($bindProperty)) {
		case "text":
			$element->textContent = $value;
			break;

		default:
			$suggestedProperty = null;

			if(str_starts_with($bindProperty, "text")) {
				$suggestedProperty = "text";
			}

			$suggestionMessage = $suggestedProperty
				? " - did you mean `data-bind:$suggestedProperty`?"
				: "";

			$tag = $this->getHTMLtag($element);
			throw new InvalidBindPropertyException("Unknown bind property `$bindProperty` on $tag Element$suggestionMessage");
		}
	}

	private function getHTMLTag(Element $element):string {
		return "<" . strtolower($element->tagName) . ">";
	}
}

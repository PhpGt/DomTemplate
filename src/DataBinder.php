<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
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
			$tagName = "<" . strtolower($element->tagName) . ">";

			foreach($element->attributes as $attrName => $attrValue) {
				if(!str_starts_with($attrName, "data-bind")) {
					continue;
				}

				if(!strstr($attrName, ":")) {
					throw new InvalidBindPropertyException("$tagName Element has a data-bind attribute with missing bind property - did you mean `data-bind:text`?");
				}

				$bindProperty = substr(
					$attrName,
					strpos($attrName, ":") + 1
				);

				if(strlen($attrValue) > 0) {
// Skip binding of data that specified as key, as bindValue will only bind to
// elements that have no specified key.
					continue;
				}

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
					throw new InvalidBindPropertyException("Unknown bind property `$bindProperty` on $tagName Element$suggestionMessage");
				}
			}
		}
	}

	public function bindKeyValue(
		string $key,
		Stringable|string $value
	):void {

	}
}

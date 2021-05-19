<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\HTMLElement\HTMLElement;
use Throwable;

class ComponentExpander {
	public function __construct(
		private Document $document,
		private ModularContent $modularContent
	) {
	}

	/** @return Element[] */
	public function expand():array {
		$expandedComponents = [];

// Any HTML element is considered a "custom element" if it contains a hyphen in
// its name:
// @see https://www.w3.org/TR/custom-elements/#valid-custom-element-name
		$xpathResult = $this->document->evaluate("descendant-or-self::*[contains(local-name(), '-')]");
		foreach($xpathResult as $element) {
			/** @var Element $element */
			$name = strtolower($element->tagName);

			try {
				$content = $this->modularContent->getContent($name);
				$element->innerHTML = $content;
				array_push($expandedComponents, $element);
			}
			catch(Throwable) {}
		}

		return $expandedComponents;
	}
}

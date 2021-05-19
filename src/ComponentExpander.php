<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Throwable;

class ComponentExpander extends ModularContentExpander {
	/** @return Element[] */
	public function expand(Element $context = null):array {
		$expandedComponentArray = [];

		if(is_null($context)) {
			$context = $this->document->documentElement;
		}

// Any HTML element is considered a "custom element" if it contains a hyphen in
// its name:
// @see https://www.w3.org/TR/custom-elements/#valid-custom-element-name
		$xpathResult = $this->document->evaluate(
			".//*[contains(local-name(), '-')]",
			$context
		);
		foreach($xpathResult as $element) {
			/** @var Element $element */
			$name = strtolower($element->tagName);

			try {
				$content = $this->modularContent->getContent($name);
				$element->innerHTML = $content;
				array_push($expandedComponentArray, $element);
				$recursiveExpandedComponents = $this->expand($element);
				$expandedComponentArray = array_merge(
					$expandedComponentArray,
					$recursiveExpandedComponents
				);
			}
			catch(Throwable) {}
		}

		return $expandedComponentArray;
	}
}

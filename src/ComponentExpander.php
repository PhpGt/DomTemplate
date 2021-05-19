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
	public function expand(Element $context = null):array {
		$expandedComponents = [];

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
				array_push($expandedComponents, $element);
				$recursiveExpandedComponents = $this->expand($element);
				$expandedComponents = array_merge(
					$expandedComponents,
					$recursiveExpandedComponents
				);
			}
			catch(Throwable) {}
		}

		return $expandedComponents;
	}
}

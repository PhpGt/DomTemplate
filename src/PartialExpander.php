<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Gt\Dom\HTMLElement\HTMLElement;

class PartialExpander extends ModularContentExpander {
	/**
	 * @return string[] A list of names of partials that have been expanded,
	 * in the order that they were expanded.
	 */
	public function expand(Element $context = null):array {
		if(!$context) {
			$context = $this->document->documentElement;
		}

		/** @var HTMLDocument[] $partialDocumentArray */
		$partialDocumentArray = [];
		do {
			$commentIni = new CommentIni($context);
			$extends = $commentIni->get("extends");
			if(is_null($extends)) {
				break;
			}

			$partialDocument = $this->modularContent->getHTMLDocument($extends);
			$partialDocumentArray[$extends] = $partialDocument;
			$context = $partialDocument;
		}
		while(true);

		foreach($partialDocumentArray as $partialDocument) {
			if($currentTitle = $this->document->title) {
				$partialDocument->title = $currentTitle;
			}

			/** @var HTMLElement $importedRoot */
			$importedRoot = $this->document->importNode(
				$partialDocument->documentElement,
				true
			);
			$injectionPoint = $importedRoot->querySelector("[data-partial]");

// Move all the current document's content into the newly-imported injection point:
			while($child = $this->document->body->firstChild) {
				$injectionPoint->appendChild($child);
			}

// Remove everything from the current document element, to replace with the
// newly imported and newly injected partial document elements.
			while($child = $this->document->documentElement->firstChild) {
				$child->parentNode->removeChild($child);
			}

// Attach all the newly-imported nodes back to the current document, now with
// our current document already injected at the correct node.
			while($child = $importedRoot->firstChild) {
				$this->document->documentElement->appendChild($child);
			}
		}

		return array_keys($partialDocumentArray);
	}
}

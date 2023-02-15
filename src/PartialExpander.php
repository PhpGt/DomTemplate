<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class PartialExpander extends PartialContentExpander {
	/**
	 * @return string[] A list of names of partials that have been expanded,
	 * in the order that they were expanded.
	 */
	public function expand(
		?Element $context = null,
		?DocumentBinder $binder = null,
	):array {
		if(!$context) {
			$context = $this->document->documentElement;
		}

		$vars = [];
		/** @var array<string, HTMLDocument> $partialDocumentArray */
		$partialDocumentArray = [];
		do {
			$commentIni = new CommentIni($context);
			$extends = $commentIni->get("extends");
			if(is_null($extends)) {
				break;
			}

			if($commentVars = $commentIni->getVars()) {
				$vars += $commentVars;
			}

			$partialDocument = $this->partialContent->getHTMLDocument($extends);
			if(isset($partialDocumentArray[$extends])) {
				throw new CyclicRecursionException("Partial '$extends' has already been expanded in this document, expanding again would cause cyclic recursion.");
			}
			$partialDocumentArray[$extends] = $partialDocument;
			$context = $partialDocument;
		}
		while(true);

		foreach($partialDocumentArray as $extends => $partialDocument) {
			if($currentTitle = $this->document->title) {
				$partialDocument->title = $currentTitle;
			}

			$importedRoot = $this->document->importNode(
				$partialDocument->documentElement,
				true
			);
			$partialElementList = $importedRoot->querySelectorAll("[data-partial]");
			if(count($partialElementList) > 1) {
				throw new PartialInjectionMultiplePointException("The current view extends the partial \"$extends\", but there is more than one element marked with `data-partial`. For help, see https://www.php.gt/domtemplate/partials");
			}
			$injectionPoint = $partialElementList[0] ?? null;
			$partialElementList[0]?->removeAttribute("data-partial");

			if(!$injectionPoint) {
				throw new PartialInjectionPointNotFoundException("The current view extends the partial \"$extends\", but there is no element marked with `data-partial`. For help, see https://www.php.gt/domtemplate/partials");
			}

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

		if($binder) {
			foreach($vars as $key => $value) {
				$binder->bindKeyValue($key, $value);
			}
		}

		return array_keys($partialDocumentArray);
	}
}

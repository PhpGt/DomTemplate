<?php
namespace Gt\DomTemplate;

use Gt\Dom\HTMLElement\HTMLElement;
use Throwable;

class PartialExpander extends ModularContentExpander {
	/**
	 * @return string[] A list of names of partials that have been expanded,
	 * in the order that they were expanded.
	 */
	public function expand():array {
		$expandedPartialArray = [];

		$commentIni = new CommentIni($this->document);
		$extends = $commentIni->get("extends");
		$partialDocument = $this->modularContent->getHTMLDocument($extends);
// TODO: Import any HEAD elements that can be extracted from $this->document
// content, such as having an inline <title> element.
		/** @var HTMLElement $importedHTMLRoot */
		$importedHTMLRoot = $this->document->importNode(
			$partialDocument->documentElement,
			true
		);
		$importedPartialElement = $importedHTMLRoot->querySelector("[data-partial]");
		while($bodyElement = $this->document->body->firstChild) {
			$importedPartialElement->appendChild($bodyElement);
		}

		while($firstChild = $this->document->documentElement->firstChild) {
			$firstChild->parentNode->removeChild($firstChild);
		}

		while($partialFirstChild = $importedHTMLRoot->firstChild) {
			$this->document->documentElement->appendChild($partialFirstChild);
		}

		array_push($expandedPartialArray, $extends);
		return $expandedPartialArray;
	}
}

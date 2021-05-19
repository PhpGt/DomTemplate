<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\HTMLElement\HTMLElement;

class PartialExpander extends ModularContentExpander {
	private CommentIni $commentIni;

	public function __construct(
		Document $document,
		ModularContent $modularContent,
		CommentIni $commentIni = null
	) {
		parent::__construct($document, $modularContent);
		$this->commentIni = $commentIni ?? new CommentIni($document);
	}

	/**
	 * @return string[] A list of names of partials that have been expanded,
	 * in the order that they were expanded.
	 */
	public function expand():array {
		$expandedPartialArray = [];

		$extends = $this->commentIni->get("extends");
		if(is_null($extends)) {
			return $expandedPartialArray;
		}

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

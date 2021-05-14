<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\Text;

class PlaceholderBinder {
	/** @var array<string, PlaceholderText[]> */
	private array $placeholderList;

	public function __construct(
		private Document $document
	) {
		$this->placeholderList = $this->findPlaceholders($document);
	}

	public function bind(
		?string $key,
		mixed $value,
		Document|Element $context = null
	):void {
		if(is_null($context)) {
			$context = $this->document->documentElement;
		}

		foreach($this->placeholderList[$key] ?? [] as $placeholderText) {
			if(!$placeholderText->isWithinContext($context)) {
				continue;
			}

			$placeholderText->setValue($value);
		}
	}

	/**
	 * @return array<string, PlaceholderText[]> An array who's key is the
	 * bind key  and value is an array of matching PlaceholderText objects.
	 */
	private function findPlaceholders(Document $document):array {
		$placeholderList = [];

		$xpathResult = $document->evaluate("//text()[contains(.,'{{')]");
		foreach($xpathResult as $text) {
			/** @var Text $text */
			$placeholder = $text->splitText(
				strpos($text->data, "{{")
			);
			$placeholder->splitText(
				strpos($placeholder->data, "}}") + 2
			);

			$placeholderText = new PlaceholderText($placeholder);
			$key = $placeholderText->getBindKey();
			if(!isset($placeholderList[$key])) {
				$placeholderList[$key] = [];
			}

			array_push($placeholderList[$key], $placeholderText);
		}

		return $placeholderList;
	}
}

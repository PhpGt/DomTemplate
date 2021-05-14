<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
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
	 * bind key and value is an array of matching PlaceholderText objects.
	 */
	private function findPlaceholders(Document $document):array {
		$placeholderList = [];

// The XPath query is split into two, separated by the pipe character (|).
// The first query: //text()[contains(.,'{{')] finds any Text nodes that contain
// two opening curly braces.
// The second query: //@*[contains(.,'{{')] finds any Attr nodes that contain
// two opening curly braces.
// NOTE: An Attr node's value is represented by a Text node.
		$xpathResult = $document->evaluate("//text()[contains(.,'{{')] | //@*[contains(.,'{{')]");
		foreach($xpathResult as $textOrAttribute) {
			$text = $textOrAttribute;
			if($textOrAttribute instanceof Attr) {
				$text = $textOrAttribute->childNodes[0];
			}

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

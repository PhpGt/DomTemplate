<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\Text;
use Gt\Dom\XPathResult;

class PlaceholderCollection {

	public function find(Document|Element $context):XPathResult {
		if($context instanceof Document) {
			$context = $context->ownerDocument->documentElement;
		}

// The XPath query is split into two, separated by the pipe character (|).
// The first query: //text()[contains(.,'{{')] finds any Text nodes that contain
// two opening curly braces.
// The second query: //@*[contains(.,'{{')] finds any Attr nodes that contain
// two opening curly braces.
// NOTE: An Attr node's value is represented by a Text node.
		return $context->ownerDocument->evaluate(
			"//text()[contains(.,'{{')] | //@*[contains(.,'{{')]",
			$context
		);
	}

	/**
	 * @return array<string, PlaceholderText[]> An array who's key is the
	 * bind key and value is an array of matching PlaceholderText objects.
	 */
	public function extract(Document|Element $context):array {
		$placeholderList = [];

		if($context instanceof Document) {
			$context = $context->ownerDocument->documentElement;
		}

		foreach($this->find($context) as $textOrAttribute) {
			$text = $textOrAttribute;
			if($text instanceof Attr) {
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

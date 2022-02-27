<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Document;
use Gt\Dom\Node;
use Gt\Dom\Text;

class PlaceholderBinder {
	public function bind(
		?string $key,
		mixed $value,
		Node|Document $context
	):void {
		if($context instanceof Document) {
			$context = $context->documentElement;
		}

// The XPath query is split into two, separated by the pipe character (|).
// The first query: //text()[contains(.,'{{')] finds any Text nodes that contain
// two opening curly braces.
// The second query: //@*[contains(.,'{{')] finds any Attr nodes that contain
// two opening curly braces.
// NOTE: An Attr node's value is represented by a Text node.
		$xpathResult = $context->ownerDocument->evaluate(
			".//text()[contains(.,'{{')] | .//@*[contains(.,'{{')]",
			$context
		);

		foreach($xpathResult as $attributeOrText) {
			/** @var Text|Attr $text */
			$text = $attributeOrText;
			if($text instanceof Attr) {
				/** @var Text $text */
				$text = $text->lastChild;
			}

			$placeholder = $text->splitText(
				strpos($text->data, "{{")
			);
			$placeholder->splitText(
				strpos($placeholder->data, "}}") + 2
			);

			$placeholderText = new PlaceholderText($placeholder);
			if((string)$key !== $placeholderText->getBindKey()) {
				$text->parentNode->nodeValue = $text->wholeText;
				continue;
			}

			$placeholderText->setValue($value);
		}
	}
}

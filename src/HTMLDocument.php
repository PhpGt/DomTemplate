<?php
namespace Gt\DomTemplate;

use DOMElement;
use DOMNode;
use Gt\Dom\DocumentFragment;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;

class HTMLDocument extends BaseHTMLDocument {
	use TemplateParent;

	public function __construct($document = "") {
		parent::__construct($document);
		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
	}

	protected function createTemplateFragment(DOMElement $templateElement):DocumentFragment {
		$fragment = $this->createDocumentFragment();

		if($templateElement->tagName === "template") {
			while(!is_null($templateElement->childNodes[0])) {
				$fragment->appendChild(
					$templateElement->childNodes[0]
				);
			}
		}
		else {
			$fragment->appendChild($templateElement);
		}

		return $fragment;
	}
}
<?php
namespace Gt\DomTemplate;

use DOMElement;
use DOMNode;
use Gt\Dom\DocumentFragment;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;

class HTMLDocument extends BaseHTMLDocument {
	protected $templateFragments = [];

	public function __construct($document = "") {
		parent::__construct($document);
		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
	}

	public function extractTemplates():int {
		$i = null;
		$templateElementList = $this->querySelectorAll(
			"template,[data-template]"
		);

		foreach($templateElementList as $i => $templateElement) {
			$templateElement->remove();
			$this->templateFragments []= $this->createTemplateFragment(
				$templateElement
			);
		}

		if(is_null($i)) {
			return 0;
		}

		return $i + 1;
	}

	protected function createTemplateFragment(Element $templateElement):DocumentFragment {
		$fragment = $this->createDocumentFragment();

		if($templateElement->tagName === "template") {
			foreach($templateElement->childNodes as $tChild) {
				$fragment->appendChild($tChild);
			}
		}
		else {
			$fragment->appendChild($templateElement);
		}

		return $fragment;
	}
}
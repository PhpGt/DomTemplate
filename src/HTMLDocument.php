<?php
namespace Gt\DomTemplate;

use Gt\Dom\HTMLDocument as BaseHTMLDocument;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;
use DOMElement;
use DOMDocumentFragment;

class HTMLDocument extends BaseHTMLDocument {
	use TemplateParent;
	use Bindable;

	protected $templateDirectory;

	public function __construct(string $document = "", string $templateDirectory = "") {
		parent::__construct($document);

		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);

		$this->templateDirectory = $templateDirectory;
	}

	protected function createTemplateFragment(DOMElement $templateElement):BaseDocumentFragment {
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
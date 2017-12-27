<?php
namespace Gt\DomTemplate;

use DOMDocument;
use DOMElement;
use DOMDocumentFragment;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

class HTMLDocument extends BaseHTMLDocument {
	use TemplateParent;
	use Bindable;

	protected $templateDirectory;
	protected $templateFragmentMap;

	public function __construct(string $document = "", string $templateDirectory = "") {
		parent::__construct($document);

		$this->registerNodeClass(DOMDocument::class, Document::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);

		$this->templateDirectory = $templateDirectory;
		$this->templateFragmentMap = [];
	}

	public function getNamedTemplate(string $name, bool $prefix = false):?BaseDocumentFragment {
		if(!$prefix) {
			return $this->templateFragmentMap[$name] ?? null;
		}

		foreach($this->templateFragmentMap as $templateName => $fragment) {
			if(strpos($templateName, $name) === 0) {
				return $fragment;
			}
		}

		return null;
	}

	public function setNamedTemplate(string $name, BaseDocumentFragment $fragment):void {
		$this->templateFragmentMap[$name] = $fragment;
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
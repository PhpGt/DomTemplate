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

	public function getNamedTemplate(string $name):?BaseDocumentFragment {
		return $this->templateFragmentMap[$name] ?? null;
	}

	/**
	 * @return \Gt\Dom\DocumentFragment[]
	 */
	public function getNamedTemplateChildren(string $name):array {
		$children = [];

		foreach($this->templateFragmentMap as $templateName => $fragment) {
// We want a match of any non-named templates that were originally children of the named path.
			if(strpos($templateName, $name) === 0) {
				$children []= $fragment;
			}
		}

		return $children;
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
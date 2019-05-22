<?php
namespace Gt\DomTemplate;

use DOMDocument;
use DOMElement;
use DOMDocumentFragment;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

/**
 * @property-read Element $head
 * @property-read Element $documentElement;
 * @property-read Node $firstChild;
 * @property-read Node $lastChild;
 * @property-read Element $firstElementChild;
 * @property-read Element $lastElementChild;
 * @property-read Element $body;
 * @method Node getElementById(string $id)
 * @method Element createElement(string $name, string $value = null)
 *
 */
class HTMLDocument extends BaseHTMLDocument {
	use TemplateParent;
	use Bindable;
	use ParentNode;

	protected $componentDirectory;
	protected $templateFragmentMap;

	public function __construct(string $document = "", string $componentDirectory = "") {
		parent::__construct($document);

		$this->registerNodeClass(DOMDocument::class, Document::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);

		$this->componentDirectory = $componentDirectory;
		$this->templateFragmentMap = [];
	}

	public function getComponentDirectory():string {
		return $this->componentDirectory;
	}

	public function getNamedTemplate(string $name):?DocumentFragment {
		/** @var \Gt\DomTemplate\DocumentFragment $fragment */
		$fragment = $this->templateFragmentMap[$name] ?? null;

		if($fragment) {
			$clone = $fragment->cloneNode(true);
			$clone->setTemplateProperties(
				$fragment->templateParentNode,
				$fragment->templateNextSibling,
				$fragment->templatePreviousSibling
			);

			return $clone;
		}

		return null;
	}

	/**
	 * @return \Gt\Dom\DocumentFragment[]
	 */
	public function getNamedTemplateChildren(string...$namesToMatch):array {
		$children = [];

		foreach($this->templateFragmentMap as $templateName => $fragment) {
// We want a match of any non-named templates that were originally children of the named path.
			foreach($namesToMatch as $name) {
				if(strpos($templateName, $name) === 0) {
					$children []= $fragment;
				}
			}
		}

		return $children;
	}

	public function setNamedTemplate(string $name, BaseDocumentFragment $fragment):void {
		if($name[0] !== "/") {
			foreach($fragment->children as $child) {
				$child->classList->add("t-$name");
			}
		}

		$this->templateFragmentMap[$name] = $fragment;
	}

	public function createTemplateFragment(DOMElement $templateElement):BaseDocumentFragment {
		/** @var BaseDocumentFragment $fragment */
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
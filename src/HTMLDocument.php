<?php
namespace Gt\DomTemplate;

use DOMDocument;
use DOMElement;
use DOMDocumentFragment;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;
use Gt\Dom\Attr as BaseAttr;
use Gt\Dom\HTMLCollection as BaseHTMLCollection;

/**
 * @property-read Element $head
 * @property-read Element $documentElement;
 * @property-read Node $firstChild;
 * @property-read Node $lastChild;
 * @property-read Element $firstElementChild;
 * @property-read Element $lastElementChild;
 * @property-read Element $body;
 *
 * @method Attr createAttribute(string $name)
 * @method Comment createComment(string $data)
 * @method DocumentFragment createDocumentFragment()
 * @method Element createElement(string $name)
 * @method Element createTextNode(string $content)
 * @method ?Element getElementById(string $id)
 *
 */
class HTMLDocument extends BaseHTMLDocument {
	use ParentNode,
		TemplateParent, Bindable;

	protected $componentDirectory;
	protected $templateFragmentMap;
	protected $templateParentStack;
	protected $boundAttributeList;

	public function __construct(string $document = "", string $componentDirectory = "") {
		parent::__construct($document);

		$this->registerNodeClass(DOMDocument::class, Document::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);

		$this->componentDirectory = $componentDirectory;
		$this->templateFragmentMap = [];
		$this->templateParentStack = [];
		$this->boundAttributeList = [];
	}

	public function getComponentDirectory():string {
		return $this->componentDirectory;
	}

	public function getNamedTemplate(string $name):?DocumentFragment {
		/** @var \Gt\DomTemplate\DocumentFragment $fragment */
		$fragment = $this->templateFragmentMap[$name] ?? null;

		if($fragment) {
			/** @var DocumentFragment $clone */
			$clone = $fragment->cloneNode(true);
			$clone->setTemplateProperties(
				$fragment->templateParentNode,
				$fragment->templateNextElementSibling,
				$fragment->templatePreviousElementSibling
			);

			return $clone;
		}

		return null;
	}

	public function getUnnamedTemplate(
		Element $element,
		bool $throwIfMoreThanOneMatch = true,
		bool $stripArraySyntax = true
	):?DocumentFragment {
		$path = $element->getNodePath();
// Unnamed templates can't have sibling elements of the same path, otherwise
// they would need to be named. Remove any index from the path.
		if($stripArraySyntax) {
			$path = preg_replace("/\[\d+\]/", "", $path);
		}
		$matches = [];

		foreach($this->templateFragmentMap as $name => $t) {
			if(strpos($name, $path) === 0) {
				$matches []= $t;
			}
		}

		if(count($matches) > 1
		&& $throwIfMoreThanOneMatch) {
			throw new NamelessTemplateSpecificityException();
		}

		if(!isset($matches[0])) {
			return null;
		}

		/** @var DocumentFragment $fragment */
		$fragment = $matches[0];
		/** @var DocumentFragment $clone */
		$clone = $fragment->cloneNode(true);
		$clone->setTemplateProperties(
			$fragment->templateParentNode,
			$fragment->templateNextElementSibling,
			$fragment->templatePreviousElementSibling
		);

		return $clone;
	}

	public function getParentOfUnnamedTemplate(
		Element $element,
		bool $requireMatchingPath = false
	):?Element {
		$path = $element->getNodePath();
// Unnamed templates can't have sibling elements of the same path, otherwise
// they would need to be named. Remove any index from the path.
		$path = preg_replace("/\[\d+\]/", "", $path);
		$pathToReturn = null;

		foreach($this->templateFragmentMap as $name => $t) {
			if(strpos($name, $path) !== 0) {
				continue;
			}

			$pathToReturn = substr(
				$name,
				0,
				strrpos($name, "/")
			);

			if($requireMatchingPath) {
				if(strpos($name, $path) === 0
				&& $path !== $name) {
					break;
				}
			}
			else {
				break;
			}
		}

		if(!$pathToReturn) {
			return null;
		}

		/** @var Element[] $matchingElements */
		$matchingElements =  $this->xPath($pathToReturn);
		return $matchingElements[count($matchingElements) - 1];
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

	public function storeBoundAttribute(BaseAttr $attr) {
		$this->boundAttributeList []= $attr;
	}

	public function validateBinds():void {
		$allBindableElements = $this->getAllDomTemplateElements();

		foreach($allBindableElements as $element) {
			foreach($element->attributes as $attr) {
				if(strpos($attr->name, "data-bind") !== 0) {
					continue;
				}

				if(in_array($attr, $this->boundAttributeList)) {
					throw new BoundDataNotSetException(
						$attr->value
					);
				}
			}
		}
	}

	public function removeTemplateAttributes():void {
		$allBindableElements = $this->getAllDomTemplateElements();

		foreach($allBindableElements as $element) {
			foreach($element->attributes as $attr) {
				/** @var \Gt\Dom\Attr $attr */
				if(strpos($attr->name, "data-bind") === 0
				|| strpos($attr->name, "data-template") === 0) {
					$attr->remove();
				}
			}
		}
	}

	protected function getAllDomTemplateElements():BaseHTMLCollection {
		return $this->documentElement->xPath(
			"descendant-or-self::*[@*[starts-with(name(), 'data-bind')]]|descendant-or-self::*[@*[starts-with(name(), 'data-template')]]"
		);
	}
}
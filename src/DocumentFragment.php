<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

class DocumentFragment extends BaseDocumentFragment {
	use TemplateParent;
	use Bindable;

	/** @var DOMNode */
	protected $templateParentNode;
	protected $templateNextSibling;
	protected $templatePreviousSibling;

	public function setTemplateProperties(
		DOMNode $parentNode = null,
		DOMNode $nextSibling = null,
		DOMNode $previousSibling = null
	):void {
		$this->templateParentNode = $parentNode;
		$this->templateNextSibling = $nextSibling;
		$this->templatePreviousSibling = $previousSibling;
	}

	public function insertTemplate():BaseElement {
		if(is_null($this->templateParentNode)) {
			throw new TemplateHasNoParentException();
		}

		$clone = $this->cloneNode(true);

		$inserted = $this->templateParentNode->insertBefore(
			$clone,
			$this->templateNextSibling
		);

		return $inserted;
	}

	public function prop_get_templateNextSibling():?BaseElement {
		return $this->templateProperties["nextSibling"];
	}

	public function prop_get_templatePreviousSibling():?BaseElement {
		return $this->templateProperties["previousSibling"];
	}

	public function prop_get_templateParentNode():?BaseElement {
		return $this->templateProperties["parentNode"];
	}
}
<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

class DocumentFragment extends BaseDocumentFragment {
	use TemplateParent;
	use Bindable;

	/** @var BaseElement */
	protected $templateParentNode;
	/** @var BaseElement */
	protected $templateNextSibling;
	/** @var BaseElement */
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

	public function insertTemplate(DOMNode $insertInto = null):BaseElement {
		$insertBefore = null;

		if(is_null($insertInto)) {
			$insertInto = $this->templateParentNode;
			$insertBefore = $this->templateNextSibling;
		}
		if(is_null($insertInto)) {
			throw new TemplateHasNoParentException();
		}

		$clone = $this->cloneNode(true);

		/** @var BaseElement $inserted */
		$inserted = $insertInto->insertBefore(
			$clone,
			$insertBefore
		);

		return $inserted;
	}

	public function prop_get_templateNextSibling():?BaseElement {
		return $this->templateNextSibling;
	}

	public function prop_get_templatePreviousSibling():?BaseElement {
		return $this->templatePreviousSibling;
	}

	public function prop_get_templateParentNode():?BaseElement {
		return $this->templateParentNode;
	}
}
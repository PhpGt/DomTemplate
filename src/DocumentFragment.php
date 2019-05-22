<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Node as BaseNode;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

/**
 * @method Node cloneNode(bool $deep)
 */
class DocumentFragment extends BaseDocumentFragment {
	use TemplateParent;
	use Bindable;

	/** @var BaseElement */
	public $templateParentNode;
	/** @var BaseElement */
	public $templateNextSibling;
	/** @var BaseElement */
	public $templatePreviousSibling;

	/**
	 * @param BaseNode $parentNode
	 * @param BaseNode $nextSibling
	 * @param BaseNode $previousSibling
	 */
	public function setTemplateProperties(
		$parentNode = null,
		$nextSibling = null,
		$previousSibling = null
	):void {
		$this->templateParentNode = $parentNode;
		$this->templateNextSibling = $nextSibling;
		$this->templatePreviousSibling = $previousSibling;
	}

	/**
	 * @return Node|Element
	 */
	public function insertTemplate(DOMNode $insertInto = null) {
		$insertBefore = null;

		if(is_null($insertInto)) {
			$insertInto = $this->templateParentNode;
			$insertBefore = $this->templateNextSibling;
		}
		if(is_null($insertInto)) {
			throw new TemplateHasNoParentException();
		}

		$clone = $this->cloneNode(true);

		/** @var Element $inserted */
		$inserted = $insertInto->insertBefore(
			$clone,
			$insertBefore
		);

		return $inserted;
	}

	public function prop_get_templateNextSibling() {
		return $this->templateNextSibling;
	}

	public function prop_get_templatePreviousSibling() {
		return $this->templatePreviousSibling;
	}

	public function prop_get_templateParentNode() {
		return $this->templateParentNode;
	}
}

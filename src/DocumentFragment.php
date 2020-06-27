<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Node as BaseNode;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

/**
 * @method Node cloneNode(bool $deep)
 */
class DocumentFragment extends BaseDocumentFragment {
	use ParentNode,
		TemplateParent, Bindable;

	/** @var Element */
	public $templateParentNode;
	/** @var Element */
	public $templateNextElementSibling;
	/** @var Element */
	public $templatePreviousElementSibling;

	/**
	 * @param BaseNode $parentNode
	 * @param BaseNode $nextElementSibling
	 * @param BaseNode $previousElementSibling
	 */
	public function setTemplateProperties(
		$parentNode = null,
		$nextElementSibling = null,
		$previousElementSibling = null
	):void {
		$this->templateParentNode = $parentNode;
		$this->templateNextElementSibling = $nextElementSibling;
		$this->templatePreviousElementSibling = $previousElementSibling;
	}

	/**
	 * @return Node|Element
	 */
	public function insertTemplate(DOMNode $insertInto = null) {
		$insertBefore = null;

		if(is_null($insertInto)) {
			$insertInto = $this->templateParentNode;
			$insertBefore = $this->templateNextElementSibling;
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
}

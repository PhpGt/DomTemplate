<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Node;

class TemplateElement {
	private ?Element $templateParent;
	private ?Node $templateNextSibling;

	public function __construct(
		private Element $originalElement
	) {
		$this->templateParent = $this->originalElement->parentElement;
		$this->templateNextSibling = $this->originalElement->nextSibling;

		$this->originalElement->remove();
// TODO: store template parent, siblings, etc. where necessary.
	}

	/**
	 * Inserts a deep clone of the original element in place where it was
	 * originally extracted from the document, returning the newly-inserted
	 * clone.
	 */
	public function insertTemplate():Element {
		/** @var Element $clone */
		$clone = $this->originalElement->cloneNode(true);
		$this->templateParent->insertBefore(
			$clone,
			$this->templateNextSibling
		);

		return $clone;
	}
}

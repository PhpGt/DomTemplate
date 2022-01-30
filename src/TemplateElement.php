<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Node;
use Gt\Dom\Text;

class TemplateElement {
	private string $templateParentPath;
	private ?string $templateNextSiblingPath;

	public function __construct(
		private Element $originalElement
	) {
		$this->templateParentPath = new NodePathCalculator($this->originalElement->parentElement);

		$siblingContext = $this->originalElement;
		while($siblingContext = $siblingContext->nextElementSibling) {
			/** @var Element|Text $siblingContext */
			if(!$siblingContext->hasAttribute("data-template")) {
				break;
			}
		}
		$this->templateNextSiblingPath =
			is_null($siblingContext)
			? null
			: new NodePathCalculator($this->originalElement->nextElementSibling);
	}

	public function removeOriginalElement():void {
		$this->originalElement->remove();
	}

	public function getClone():Element {
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		/** @var Element $element */
		$element = $this->originalElement->cloneNode(true);
		return $element;
	}

	/**
	 * Inserts a deep clone of the original element in place where it was
	 * originally extracted from the document, returning the newly-inserted
	 * clone.
	 */
	public function insertTemplate():Element {
		$clone = $this->getClone();
		$templateParent = $this->getTemplateParent();
		$templateParent->insertBefore(
			$clone,
			$this->getTemplateNextSibling()
		);

		return $clone;
	}

	public function getTemplateParent():Element {
		$matches = $this->originalElement->ownerDocument->evaluate(
			$this->templateParentPath
		);
		do {
			/** @var Element $parent */
			$parent = $matches->current();
			$matches->next();
		}
		while($matches->valid());
		return $parent;
	}

	public function getTemplateNextSibling():?Node {
		if(is_null($this->templateNextSiblingPath)) {
			return null;
		}

		$matches = $this->originalElement->ownerDocument->evaluate(
			$this->templateNextSiblingPath
		);
		$sibling = null;
		while($matches->valid()) {
			$sibling = $matches->current();
			$matches->next();
		}
		return $sibling;
	}

	public function getTemplateName():?string {
		$templateName = $this->originalElement->getAttribute("data-template");
		if(strlen($templateName) === 0) {
			return null;
		}
		elseif($templateName[0] === "/") {
			throw new InvalidTemplateElementNameException("A template's name must not start with a forward slash (\"$templateName\")");
		}

		return $templateName;
	}
}

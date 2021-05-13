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
	}

	public function getNewElement():Element {
		/** @var Element $element */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$element = $this->originalElement->cloneNode(true);
		return $element;
	}

	/**
	 * Inserts a deep clone of the original element in place where it was
	 * originally extracted from the document, returning the newly-inserted
	 * clone.
	 */
	public function insertTemplate():Element {
		$clone = $this->getNewElement();
		$this->templateParent->insertBefore(
			$clone,
			$this->templateNextSibling
		);

		return $clone;
	}

	public function getTemplateParent():Element {
		return $this->templateParent;
	}

	public function getTemplateName():string {
		$templateName = $this->originalElement->getAttribute("data-template");
		if($templateName === "") {
			return $this->calculateTemplatePath($this->templateParent);
		}
		elseif($templateName[0] === "/") {
			throw new InvalidTemplateElementNameException("A template's name must not start with a forward slash (\"$templateName\")");
		}

		return $templateName;
	}

	private function calculateTemplatePath(Element $element):string {
		return new NodePathExtractor($element);
	}
}

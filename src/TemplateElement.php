<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Facade\NodeClass\DOMElementFacade;
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

	public function getTemplateParent():Element {
		return $this->templateParent;
	}

	public function getTemplateName():string {
		return $this->originalElement->getAttribute("data-template")
			?: $this->calculateTemplatePath($this->templateParent);
	}

	private function calculateTemplatePath(Element $element):string {
		$refObj = new \ReflectionObject($element);
		$refProp = $refObj->getProperty("domNode");
		$refProp->setAccessible(true);
		/** @var DOMElementFacade $nativeDomNode */
		$nativeDomNode = $refProp->getValue($element);
		return $nativeDomNode->getNodePath();
	}
}

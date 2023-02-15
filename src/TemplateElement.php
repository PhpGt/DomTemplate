<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Node;
use Gt\Dom\Text;
use Throwable;

class TemplateElement {
	private string $templateParentPath;
	private null|Node|Element $templateNextSibling;
	private int $insertCount;

	public function __construct(
		private Node|Element $originalElement
	) {
		$parentElement = $this->originalElement->parentElement;
		if(!$parentElement->id) {
			$parentElement->id = uniqid("template-parent-");
		}

		$this->templateParentPath = new NodePathCalculator($parentElement);

		$siblingContext = $this->originalElement;
		while($siblingContext = $siblingContext->nextElementSibling) {
			if(!$siblingContext->hasAttribute("data-template")) {
				break;
			}
		}
		$this->templateNextSibling =
			is_null($siblingContext)
			? null
			: $siblingContext;

		$this->insertCount = 0;
	}

	public function removeOriginalElement():void {
		$this->originalElement->remove();
		try {
			$parent = $this->getTemplateParent();
			if(count($parent->children) === 0) {
				if($firstNode = $parent->childNodes[0] ?? null) {
					if(trim($firstNode->wholeText) === "") {
						$parent->innerHTML = "";
					}
				}
			}
		}
// In nested lists, there may not be an actual element attached to the document
// yet, but the parent still has a path - this outcome is expected and
// completely fine in this case.
		catch(Throwable) {}
	}

	public function getClone():Node|Element {
// TODO: Bug here - the template-parent-xxx ID is being generated the same for multiple instances.
		/** @var Element $element */
		$element = $this->originalElement->cloneNode(true);
//		foreach($this->originalElement->ownerDocument->evaluate("./*[starts-with(@id,'template-parent-')]", $element) as $existingTemplateElement) {
//			$existingTemplateElement->id = uniqid("template-parent-");
//		}
//		$this->templateParentPath = new NodePathCalculator($element->parentElement);
		return $element;
	}

	/**
	 * Inserts a deep clone of the original element in place where it was
	 * originally extracted from the document, returning the newly-inserted
	 * clone.
	 */
	public function insertTemplate():Node|Element {
		$clone = $this->getClone();
		$templateParent = $this->getTemplateParent();
		$templateParent->insertBefore(
			$clone,
			$this->getTemplateNextSibling()
		);
		$this->insertCount++;
		return $clone;
	}

	public function getTemplateParent():Node|Element {
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

	public function getTemplateNextSibling():null|Node|Element {
		return $this->templateNextSibling ?? null;
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

	public function getInsertCount():int {
		return $this->insertCount;
	}
}

<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Node;
use Gt\Dom\Text;
use Throwable;

class ListElement {
	const ATTRIBUTE_LIST_PARENT = "data-list-parent";

	private string $listItemParentPath;
	private null|Node|Element $listItemNextSibling;
	private int $insertCount;

	public function __construct(
		private readonly Node|Element $originalElement
	) {
		$parentElement = $this->originalElement->parentElement;
		if(!$parentElement->getAttribute(self::ATTRIBUTE_LIST_PARENT)) {
			$parentElement->setAttribute(self::ATTRIBUTE_LIST_PARENT, uniqid("template-parent-"));
		}

		$this->listItemParentPath = new NodePathCalculator($parentElement);

		$siblingContext = $this->originalElement;
		while($siblingContext = $siblingContext->nextElementSibling) {
			if(!$siblingContext->hasAttribute("data-template")) {
				break;
			}
		}
		$this->listItemNextSibling =
			is_null($siblingContext)
			? null
			: $siblingContext;

		$this->insertCount = 0;
	}

	public function removeOriginalElement():void {
		$this->originalElement->remove();
		try {
			$parent = $this->getListItemParent();
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
// TODO: #368 Bug here - the template-parent-xxx ID is being generated the same for multiple instances.
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
	public function insertListItem():Element {
		$clone = $this->getClone();
		$listItemParent = $this->getListItemParent();
		$listItemParent->insertBefore(
			$clone,
			$this->getListItemNextSibling()
		);
		$this->insertCount++;
		return $clone;
	}

	public function getListItemParent():Node|Element {
		$matches = $this->originalElement->ownerDocument->evaluate(
			$this->listItemParentPath
		);
		do {
			/** @var Element $parent */
			$parent = $matches->current();
			$matches->next();
		}
		while($matches->valid());
		return $parent;
	}

	public function getListItemNextSibling():null|Node|Element {
		return $this->listItemNextSibling ?? null;
	}

	public function getListItemName():?string {
		$templateName = $this->originalElement->getAttribute("data-list") ?? $this->originalElement->getAttribute("data-template");
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

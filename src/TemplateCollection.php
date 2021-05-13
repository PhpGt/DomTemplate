<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;

class TemplateCollection {
	/** @var array<string, TemplateElement> */
	private array $elementKVP;

	public function __construct(
		Document $document
	) {
		$this->elementKVP = [];
		$this->extractTemplates($document);
	}

	public function get(
		Element|Document $context,
		?string $templateName = null
	):TemplateElement {
		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		if($templateName) {
			return $this->elementKVP[$templateName];
		}

		return $this->findMatch($context);
	}

	private function extractTemplates(Document $document):void {
		foreach($document->querySelectorAll("[data-template]") as $element) {
			$templateElement = new TemplateElement($element);
			$this->elementKVP[$templateElement->getTemplateName()] = $templateElement;
		}
	}

	private function findMatch(Element $context):TemplateElement {
		foreach($this->elementKVP as $name => $element) {
			if($name[0] !== "/") {
				continue;
			}

			$xpathResult = $context->ownerDocument->evaluate($name);
			if($xpathResult->valid()) {
				return $element;
			}
		}
	}
}

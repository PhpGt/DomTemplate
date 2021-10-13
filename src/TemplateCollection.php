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
			if(!isset($this->elementKVP[$templateName])) {
				throw new TemplateElementNotFoundInContextException("Template element with name \"$templateName\" can not be found within the context {$context->tagName} element.");
			}
			return $this->elementKVP[$templateName];
		}

		return $this->findMatch($context);
	}

	private function extractTemplates(Document $document):void {
		$dataTemplateArray = [];
		foreach($document->querySelectorAll("[data-template]") as $element) {
			/** @var Element $element */
			$nodePath = (string)(new NodePathCalculator($element));
			$templateElement = new TemplateElement($element);
			$key = $templateElement->getTemplateName() ?? $nodePath;
			$dataTemplateArray[$key] = $templateElement;
		}

		uksort($dataTemplateArray,
			fn(string $a, string $b):int => (
				(substr_count($a, "/") > substr_count($b, "/"))
				? -1
				: 1
			)
		);

		foreach($dataTemplateArray as $template) {
			$template->removeOriginalElement();
		}

//		foreach($dataTemplateArray as $nodePath => $element) {
//			/** @var Element $element */
//			$templateElement = new TemplateElement($element);
//			$name = $templateElement->getTemplateName() ?? $nodePath;
//			$this->elementKVP[$name] = $templateElement;
//		}

		$this->elementKVP = array_reverse($dataTemplateArray, true);
	}

	private function findMatch(Element $context):TemplateElement {
		$contextPath = (string)(new NodePathCalculator($context));
		$contextPath = preg_replace(
			"/(\[\d+\])/",
			"",
			$contextPath
		);

		foreach($this->elementKVP as $name => $element) {
			if($contextPath === $name) {
				continue;
			}

			if(!str_starts_with($name, $contextPath)) {
				continue;
			}

			$xpathResult = $context->ownerDocument->evaluate(
				$contextPath
			);

			if($xpathResult->valid()) {
				return $element;
			}
		}

		throw new TemplateElementNotFoundInContextException(
			"There is no unnamed template element in the context element ({$context->tagName})."
		);
	}
}

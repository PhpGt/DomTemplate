<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
use DOMDocument;
use Gt\Dom\HTMLCollection;
use Gt\Dom\Element as BaseElement;

trait TemplateParent {

	public function extractTemplates():int {
		$i = null;
		/** @var HTMLCollection $templateElementList */
		$templateElementList = $this->querySelectorAll(
			"template,[data-template]"
		);

		foreach($templateElementList as $i => $templateElement) {
			$name = $this->getTemplateNameFromElement($templateElement);

			$parentNode = $templateElement->parentNode;
			$nextSibling = $templateElement->nextSibling;
			$previousSibling = $templateElement->previousSibling;
			$templateNodePath = $templateElement->getNodePath();

			$document = ($this instanceof DOMDocument)
				? $this
				: $this->ownerDocument;
			/** @var DocumentFragment $fragment */
			$fragment = $document->createTemplateFragment(
				$templateElement
			);
			$fragment->setTemplateProperties(
				$parentNode,
				$nextSibling,
				$previousSibling
			);

			/** @var HTMLDocument $rootDocument */
			$rootDocument = $this->getRootDocument();
			$rootDocument->setNamedTemplate($name, $fragment);
// Also set the template element with its path name.
			$rootDocument->setNamedTemplate(
				$templateNodePath,
				$fragment
			);

			if($templateElement->getAttribute("data-template")) {
				$templateElement->removeAttribute("data-template");
			}

			if($templateElement->parentNode === $parentNode) {
				$parentNode->removeChild($templateElement);
			}
		}

		if(is_null($i)) {
			return 0;
		}

		return $i + 1;
	}

	public function getTemplate(string $name, string $templateDirectory = null):DocumentFragment {
		/** @var HTMLDocument $rootDocument */
		$rootDocument = $this->getRootDocument();
		$docTemplate = $rootDocument->getNamedTemplate($name);
		if(!is_null($docTemplate)) {
			$docTemplate->expandComponents();
			return $docTemplate;
		}

		if(is_null($templateDirectory)) {
			$templateDirectory = $this->templateDirectory;
		}

		if(is_dir($templateDirectory)) {
			foreach(new DirectoryIterator($templateDirectory) as $fileInfo) {
				if(!$fileInfo->isFile()) {
					continue;
				}

				$fileName = $fileInfo->getFilename();
				$fileName = strtok($fileName, ".");

				if($name === $fileName) {
					return $this->loadComponent(
						$name,
						dirname($fileInfo->getRealPath())
					);
				}
			}
		}

		throw new TemplateComponentNotFoundException($name);
	}

	public function expandComponents(string $templateDirectory = null):int {
		if(is_null($templateDirectory)) {
			if($this instanceof HTMLDocument) {
				$templateDirectory = $this->templateDirectory;
			}
			else {
				$templateDirectory = $this->ownerDocument->getTemplateDirectory();
			}
		}
// Any HTML element is considered a "custom element" if it contains a hyphen in its name:
// @see https://www.w3.org/TR/custom-elements/#valid-custom-element-name
		/** @var HTMLCollection $componentList */
		$componentList = $this->xPath(
			"descendant-or-self::*[contains(local-name(), '-')]"
		);
		$count = 0;
		foreach($componentList as $component) {
			$name = $component->tagName;

			try {
				$fragment = $this->getTemplate(
					$name,
					$templateDirectory
				);
			}
			catch(TemplateComponentNotFoundException $exception) {
				continue;
			}

			$fragment->expandComponents($templateDirectory);
			foreach($fragment->children as $child) {
				$child->classList->add("t-" . $name);
			}
			$component->replaceWith($fragment);
			$count++;
		}

		return $count;
	}

	protected function loadComponent(string $name, string $path):DocumentFragment {
		$filePath = $this->getTemplateFilePath($name, $path);

		$html = file_get_contents($filePath);
		/** @var DocumentFragment $fragment */
		if(method_exists($this, "createDocumentFragment")) {
			$fragment = $this->createDocumentFragment();
		}
		else {
			/** @var HTMLDocument $ownerDocument */
			$ownerDocument = $this->ownerDocument;
			$fragment = $ownerDocument->createDocumentFragment();
		}

		$fragment->appendXML($html);
		$fragment->extractTemplates();
		$fragment->expandComponents();
		return $fragment;
	}

	protected function getTemplateFilePath(string $name, string $path):string {
		$templateFilePath = "";

		foreach(new DirectoryIterator($path) as $fileInfo) {
			if(!$fileInfo->isFile()) {
				continue;
			}

			$fileName = $fileInfo->getFilename();
			$noExt = strtok($fileName, ".");

			if($name === $noExt) {
				$templateFilePath = $fileInfo->getRealPath();
			}
		}

		return $templateFilePath;
	}

	protected function getTemplateNameFromElement(BaseElement $element):string {
		switch($element->tagName) {
		case "template":
			$name = $element->id;
			break;

		default:
			$name = $element->getAttribute("data-template");
			break;
		}

		if(strlen($name) === 0) {
			$name = $element->getNodePath();
		}

		return $name;
	}
}
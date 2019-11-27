<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
use DOMDocument;
use Gt\Dom\Element as BaseElement;

trait TemplateParent {
	public function extractTemplates(BaseElement $context = null):int {
		if(is_null($context)) {
			$context = $this;
		}

		$i = null;
		/** @var HTMLCollection $templateElementList */
		$templateElementList = $context->querySelectorAll(
			"template,[data-template]"
		);

		$count = count($templateElementList) - 1;

		for($i = $count; $i >= 0; $i--) {
			$templateElement = $templateElementList[$i];
			$name = $this->getTemplateNameFromElement($templateElement);

			$parentNode = $templateElement->parentNode;
			$nextSibling = $templateElement->nextSibling;
			$previousSibling = $templateElement->previousSibling;
			$templateNodePath = $templateElement->getNodePath();

			$nestedTemplateElementList = $templateElement->querySelectorAll(
				"template,[data-template]"
			);
			foreach($nestedTemplateElementList as $nestedTemplateElement) {
				$this->extractTemplates($nestedTemplateElement);
			}

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
			$fragment->expandComponents();

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

			if($name[0] !== "/") {
				$templateElement->classList->add("t-$name");
			}
		}

		if($this instanceof HTMLDocument) {
			ksort($this->templateFragmentMap);
		}

		if(is_null($count)) {
			return 0;
		}

		return $count + 1;
	}

	public function getTemplate(
		string $name = null,
		string $templateDirectory = null,
		bool $addTemplatePrefix = true
	):DocumentFragment {
		/** @var HTMLDocument $rootDocument */
		$rootDocument = $this->getRootDocument();

		if(is_null($name)) {
			$docTemplate = $rootDocument->getUnnamedTemplate($this, false);
		}
		else {
			$docTemplate = $rootDocument->getNamedTemplate($name);
		}
		if(!is_null($docTemplate)) {
			return $docTemplate;
		}

		if(is_null($templateDirectory)) {
			$templateDirectory = $this->componentDirectory;
		}

		if(is_dir($templateDirectory)) {
			foreach(new DirectoryIterator($templateDirectory) as $fileInfo) {
				if(!$fileInfo->isFile()) {
					continue;
				}

				$fileName = $fileInfo->getFilename();
				$fileName = strtok($fileName, ".");

				if($name === $fileName) {
					$component = $this->loadComponent(
						$name,
						dirname($fileInfo->getRealPath())
					);

					foreach($component->children as $child) {
						$child->classList->add("c-$name");

						if($addTemplatePrefix) {
							$child->classList->add("t-$name");
						}
					}

					return $component;
				}
			}
		}

		throw new TemplateComponentNotFoundException($name);
	}

	public function expandComponents(string $templateDirectory = null):int {
		if(is_null($templateDirectory)) {
			if($this instanceof HTMLDocument) {
				$templateDirectory = $this->componentDirectory;
			}
			else {
				$templateDirectory = $this->ownerDocument->getComponentDirectory();
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
					$templateDirectory,
					false
				);
			}
			catch(TemplateComponentNotFoundException $exception) {
				continue;
			}

			$fragment->expandComponents($templateDirectory);
			foreach($fragment->children as $child) {
				$existingClassName = $component->className ?? "";
				$child->classList->add($existingClassName);
				$child->classList->add("c-$name");
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

		$fragment->appendHTML($html);
		$fragment->extractTemplates();
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
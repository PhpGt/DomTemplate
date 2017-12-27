<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
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
			$templateElement->remove();

			$fragment = $this->createTemplateFragment(
				$templateElement
			);
			$this->getRootDocument()->setNamedTemplate($name, $fragment);
		}

		if(is_null($i)) {
			return 0;
		}

		return $i + 1;
	}

	public function getTemplate(string $name, string $templateDirectory = null):DocumentFragment {
		$docTemplate = $this->getRootDocument()->getNamedTemplate($name);
		if(!is_null($docTemplate)) {
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
			$templateDirectory = $this->templateDirectory;
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
				$fragment = $this->getTemplate($name, $templateDirectory);
			}
			catch(TemplateComponentNotFoundException $exception) {
				continue;
			}

			$fragment->expandComponents($templateDirectory);
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
<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
use Gt\Dom\HTMLCollection;
use Gt\Dom\Element as BaseElement;
use Gt\Dom\DocumentFragment as BaseDocumentFragment;

trait TemplateParent {
	protected $templateFragmentMap = [];

	public function extractTemplates():int {
		$i = null;
		/** @var HTMLCollection $templateElementList */
		$templateElementList = $this->querySelectorAll(
			"template,[data-template]"
		);

		foreach($templateElementList as $i => $templateElement) {
			$templateElement->remove();
			$name = $this->getTemplateNameFromElement($templateElement);

			$this->templateFragmentMap[$name] = $this->createTemplateFragment(
				$templateElement
			);
		}

		if(is_null($i)) {
			return 0;
		}

		return $i + 1;
	}

	public function getTemplate(string $name):?DocumentFragment {
		if(isset($this->templateFragmentMap[$name])) {
			return $this->templateFragmentMap[$name];
		}

		if(is_dir($this->templateDirectory)) {
			foreach(new DirectoryIterator($this->templateDirectory) as $fileInfo) {
				if(!$fileInfo->isFile()) {
					continue;
				}

				$fileName = $fileInfo->getFilename();
				if($name === $fileName) {
					return $this->loadTemplate(
						$name,
						$fileInfo->getPathname()
					);
				}
			}
		}

		return null;
	}

	public function expandComponents(string $templateFilePath):int {
// Any HTML element is considered a "custom element" if it contains a hyphen in its name:
// @see https://www.w3.org/TR/custom-elements/#valid-custom-element-name
		/** @var HTMLCollection $componentList */
		$componentList = $this->xPath(
			"descendant-or-self::*[contains(local-name(), '-')]"
		);

		$count = 0;
		foreach($componentList as $component) {
			$name = $component->tagName;

			if(!isset($this->templateFragmentMap[$name])) {
				try {
					$this->templateFragmentMap[$name] = $this->loadComponent(
						$name,
						$templateFilePath
					);
				}
				catch(TemplateComponentNotFoundException $exception) {}
			}

			/** @var DocumentFragment $fragment */
			$fragment = $this->templateFragmentMap[$name] ?? null;

			if(is_null($fragment)) {
				continue;
			}

			$fragment->expandComponents($templateFilePath);
			$component->replaceWith($fragment);
			$count++;
		}

		return $count;
	}

	protected function loadComponent(string $name, string $path):BaseDocumentFragment {
		$filePath = $this->getTemplateFilePath($name, $path);

		if(is_null($filePath)) {
			throw new TemplateComponentNotFoundException($filePath);
		}

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

	protected function getTemplateFilePath(string $name, string $path):?string {
		foreach(new DirectoryIterator($path) as $fileInfo) {
			if(!$fileInfo->isFile()) {
				continue;
			}

			$fileName = $fileInfo->getFilename();
			$noExt = strtok($fileName, ".");

			if($name === $noExt) {
				return $fileInfo->getRealPath();
			}
		}

		return null;
	}

	protected function getTemplateNameFromElement(BaseElement $element):string {
		switch($element->tagName) {
		case "template":
			return $element->id;

		default:
			return $element->getAttribute("data-template");
		}
	}
}
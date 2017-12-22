<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
use Gt\Dom\DocumentFragment;
use Gt\Dom\HTMLCollection;
use Gt\Dom\Element as BaseElement;

trait TemplateParent {
	protected $templateFragmentMap = [];
	protected $templateFilePath;

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

	public function setTemplateFilePath(string $path):void {
		$this->templateFilePath = $path;
	}

	public function getTemplate(string $name):?DocumentFragment {
		if(isset($this->templateFragmentMap[$name])) {
			return $this->templateFragmentMap[$name];
		}

		if(is_dir($this->templateFilePath)) {
			foreach(new DirectoryIterator($this->templateFilePath) as $fileInfo) {
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

	public function expandComponents():int {
		$count = 0;

		/** @var HTMLCollection $componentList*/
		$componentList = $this->xPath("//*[contains(local-name(), '-')]");

		foreach($componentList as $component) {
			$name = $component->tagName;

			if(!isset($this->templateFragmentMap[$name])) {
				$this->templateFragmentMap[$name] = $this->loadComponent($name);
			}

			$fragment = $this->templateFragmentMap[$name];
			if(is_null($fragment)) {
				continue;
			}

			$component->replaceWith($fragment);
			$count++;
		}

		return $count;
	}

	protected function loadComponent(string $name):?DocumentFragment {
		$filePath = $this->getTemplateFilePath($name);

		if(is_null($filePath)) {
			return null;
		}

		$html = file_get_contents($filePath);
		/** @var DocumentFragment $fragment */
		$fragment = $this->createDocumentFragment();
		$fragment->appendXML($html);
		return $fragment;
	}

	protected function getTemplateFilePath(string $name):?string {
		foreach(new DirectoryIterator($this->templateFilePath) as $fileInfo) {
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
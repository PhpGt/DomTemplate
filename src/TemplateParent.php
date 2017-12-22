<?php
namespace Gt\DomTemplate;

use DirectoryIterator;
use Gt\Dom\DocumentFragment;
use Gt\Dom\HTMLCollection;

trait TemplateParent {
	protected $templateFragments = [];
	protected $templateFilePath = "src/page/_template";

	public function extractTemplates():int {
		$i = null;
		/** @var HTMLCollection $templateElementList */
		$templateElementList = $this->querySelectorAll(
			"template,[data-template]"
		);

		foreach($templateElementList as $i => $templateElement) {
			$templateElement->remove();
			$name = $this->getTemplateNameFromElement($templateElement);

			$this->templateFragments[$name] = $this->createTemplateFragment(
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
		if(isset($this->templateFragments[$name])) {
			return $this->templateFragments[$name];
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

	protected function loadTemplate(string $name, string $path):?DocumentFragment {
		// TODO: Implement loading from file.
		return null;
	}

	protected function getTemplateNameFromElement(Element $element):string {
		switch($element->tagName) {
		case "template":
			return $element->id;

		default:
			return $element->getAttribute("data-template");
		}
	}
}
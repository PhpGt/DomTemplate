<?php
namespace Gt\DomTemplate;

trait Bindable {
	public function bind(iterable $data, string $templateName = null):void {
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		// TODO: Output data to element's children already in DOM with bind attributes.
		// TODO: Clone templates with matching bind attributes, output data to them, insert them.

		$templateFragmentMap = $this->templateFragmentMap ?? [];
		foreach($templateFragmentMap as $name => $fragment) {
			if($name !== $templateName) {
				continue;
			}

		}
	}
}
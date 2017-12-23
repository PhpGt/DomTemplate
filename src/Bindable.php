<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element as BaseElement;

trait Bindable {
	public function bind(iterable $data, string $templateName = null):void {
		/** @var BaseElement $element */
		$element = $this;
		if($element instanceof HTMLDocument) {
			$element = $element->documentElement;
		}

		$this->bindExisting($element, $data, $templateName);
		$this->bindTemplates($element, $data, $templateName);
	}

	protected function bindExisting(
		BaseElement $element,
		iterable $data,
		string $templateName = null
	):void {
		$elementsWithBindAttribute = $element->xPath(
			"descendant-or-self::*/@*[starts-with(name(.), 'data-bind')]"
		);

		foreach($elementsWithBindAttribute as $element) {
			// TODO: For some reason the XPath above is incorrect and doesn't match properly.
		}
		die("END");
	}

	protected function bindTemplates(
		BaseElement $element,
		iterable $data,
		string $templateName = null
	):void {
		$templateFragmentMap = $this->templateFragmentMap ?? [];
		foreach($templateFragmentMap as $name => $fragment) {
			if($name !== $templateName) {
				continue;
			}

		}
	}
}
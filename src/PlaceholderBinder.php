<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;

class PlaceholderBinder {
	private PlaceholderCollection $placeholderCollection;

	public function __construct(
		PlaceholderCollection $placeholderCollection = null
	) {
		$this->placeholderCollection = $placeholderCollection ?? new PlaceholderCollection();
	}

	public function bind(
		?string $key,
		mixed $value,
		Document|Element $context = null
	):void {
		if($context instanceof Document) {
			$context = $context->ownerDocument->documentElement;
		}

		$placeholderList = $this->placeholderCollection->extract($context);
		foreach($placeholderList as $placeholderTextArray) {
			foreach($placeholderTextArray as $placeholderText) {
				if(!$placeholderText->isWithinContext($context)) {
					continue;
				}

				$placeholderText->setKeyValue($key, $value);
			}
		}
	}
}

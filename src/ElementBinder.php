<?php
namespace Gt\DomTemplate;

use Gt\Dom\DOMTokenList;
use Gt\Dom\Element;
use Gt\Dom\Facade\DOMTokenListFactory;
use Gt\Dom\Node;
use Gt\Dom\XPathResult;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ElementBinder {
	private HTMLAttributeBinder $htmlAttributeBinder;
	private HTMLAttributeCollection $htmlAttributeCollection;
	private PlaceholderBinder $placeholderBinder;

	public function __construct(
		?HTMLAttributeBinder $htmlAttributeBinder = null,
		?HTMLAttributeCollection $htmlAttributeCollection = null,
		?PlaceholderBinder $placeholderBinder = null,
	) {
		$this->htmlAttributeBinder = $htmlAttributeBinder ?? new HTMLAttributeBinder();
		$this->htmlAttributeCollection = $htmlAttributeCollection ?? new HTMLAttributeCollection();
		$this->placeholderBinder = $placeholderBinder ?? new PlaceholderBinder();
	}

	/**
	 * Binds an Element and its children according to any HTML "data-bind"
	 * attributes found, and any {{placeholder}} text.
	 */
	public function bind(
		?string $key,
		mixed $value,
		Element $context
	):void {
		if(!is_null($value) && !is_scalar($value) && !is_iterable($value)) {
			$value = new BindValue($value);
		}

		/** @var Element $element */
		foreach($this->htmlAttributeCollection->find($context) as $element) {
			$this->htmlAttributeBinder->expandAttributes($element);
			$this->htmlAttributeBinder->bind($key, $value, $element);
		}

		$this->placeholderBinder->bind($key, $value, $context);
	}
}

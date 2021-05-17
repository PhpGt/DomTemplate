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
		foreach($this->htmlAttributeCollection->find($context) as $element) {
			/** @var Element $element */
			$this->htmlAttributeBinder->bind($key, $value, $element);
		}

		$this->placeholderBinder->bind($key, $value, $context);
	}

	/**
	 * A "bindable" object is any object with the Gt\DomTemplate\Bind
	 * Attribute applied to any of its public properties or methods.
	 * The Attribute's first parameter is required, which sets the property
	 * or method's bind key. For example, a method called "getTotalMessages"
	 * could be marked with the #[Bind("message-count")] Attribute, so the
	 * method will be called whenever the "message-count" bind key is used
	 * in the document.
	 */
	public function bindMethodPropertyAttributes(
		object $objectWithAttributes,
		Element $context
	):void {
		$bindKeyList = [];
		foreach($this->htmlAttributeCollection->find($context) as $bindElement) {
			/** @var Element $bindElement */
			array_push($bindKeyList, ...$this->getBindKeys($bindElement));
		}

		$refClass = new ReflectionClass($objectWithAttributes);
		foreach($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
			foreach($refMethod->getAttributes(Bind::class) as $refAttribute) {
				$args = $refAttribute->getArguments();
				$bindKey = $args[0];
				if(!in_array($bindKey, $bindKeyList)) {
					continue;
				}

				$this->bind(
					$bindKey,
					call_user_func([$objectWithAttributes, $refMethod->getName()]),
					$context
				);
			}
		}

		foreach($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $refProperty) {
			foreach($refProperty->getAttributes(Bind::class) as $refAttribute) {
				$args = $refAttribute->getArguments();
				$bindKey = $args[0];
				if(!in_array($bindKey, $bindKeyList)) {
					continue;
				}

				$this->bind(
					$bindKey,
					$objectWithAttributes->{$refProperty->getName()},
					$context
				);
			}
		}
	}

	/** @return array<int, string> */
	private function getBindKeys(Element $element):array {
		$bindKeyList = [];
		foreach($element->attributes as $attributeValue) {
			array_push($bindKeyList, $attributeValue);
		}

		return $bindKeyList;
	}
}

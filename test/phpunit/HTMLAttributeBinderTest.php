<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLElement\HTMLOptionElement;
use Gt\Dom\HTMLElement\HTMLSelectElement;
use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class HTMLAttributeBinderTest extends TestCase {
	public function testBind_wholeDocument():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LANGUAGE);
		$sut = new HTMLAttributeBinder();
		$sut->bind("language", "en_GB", $document);
		self::assertSame("en_GB", $document->documentElement->getAttribute("lang"));
	}

	public function testBind_selectValue():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SELECT_OPTIONS_WITH_VALUE);
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "tea";
		$sut->bind("drink", $valueToSelect, $select);

		/** @var HTMLOptionElement $option */
		foreach($document->querySelectorAll("select option") as $option) {
			$value = $option->getAttribute("value");
			if($value === $valueToSelect) {
				self::assertTrue($option->hasAttribute("selected"));
			}
			else {
				self::assertFalse($option->hasAttribute("selected"));
			}
		}
	}

	public function testBind_selectValue_noOptions():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SELECT_OPTIONS_WITHOUT_VALUE);
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "Tea";
		$sut->bind("drink", $valueToSelect, $select);

		/** @var HTMLOptionElement $option */
		foreach($document->querySelectorAll("select option") as $option) {
			if($option->value === $valueToSelect) {
				self::assertTrue($option->hasAttribute("selected"));
			}
			else {
				self::assertFalse($option->hasAttribute("selected"));
			}
		}
	}

	public function testBind_selectValue_optionDoesNotExist():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SELECT_OPTIONS_WITHOUT_VALUE);
		/** @var HTMLSelectElement $select */
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "Grape Juice";
		$select->options[2]->selected = true;
		$sut->bind("drink", $valueToSelect, $select);

		/** @var HTMLOptionElement $option */
		foreach($document->querySelectorAll("select option") as $option) {
			self::assertFalse($option->hasAttribute("selected"));
		}
	}
}

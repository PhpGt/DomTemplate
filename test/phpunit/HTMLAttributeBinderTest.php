<?php
namespace Gt\DomTemplate\Test;

use DateTime;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class HTMLAttributeBinderTest extends TestCase {
	public function testBind_wholeDocument():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_LANGUAGE);
		$sut = new HTMLAttributeBinder();
		$sut->bind("language", "en_GB", $document);
		self::assertSame("en_GB", $document->documentElement->getAttribute("lang"));
	}

	public function testBind_selectValue():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_SELECT_OPTIONS_WITH_VALUE);
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "tea";
		$sut->bind("drink", $valueToSelect, $select);

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
		$document = new HTMLDocument(DocumentTestFactory::HTML_SELECT_OPTIONS_WITHOUT_VALUE);
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "Tea";
		$sut->bind("drink", $valueToSelect, $select);

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
		$document = new HTMLDocument(DocumentTestFactory::HTML_SELECT_OPTIONS_WITHOUT_VALUE);
		$select = $document->querySelector("select[name='drink']");
		$sut = new HTMLAttributeBinder();
		$valueToSelect = "Grape Juice";
		$select->options[2]->selected = true;
		$sut->bind("drink", $valueToSelect, $select);

		foreach($document->querySelectorAll("select option") as $i => $option) {
			self::assertFalse($option->hasAttribute("selected"), $i);
		}
	}

	public function testBind_modifierColonNamedProperty_null():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_TODO);
		$sut = new HTMLAttributeBinder();
		$li = $document->querySelector("ul li");
		$sut->bind("completedAt", null, $li);
		self::assertFalse($li->classList->contains("completed"));
	}

	public function testBind_modifierColonNamedProperty():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_TODO);
		$sut = new HTMLAttributeBinder();
		$li = $document->querySelector("ul li");
		$sut->bind("completedAt", new DateTime(), $li);
		self::assertTrue($li->classList->contains("completed"));
	}

	public function testBind_modifierColon():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new HTMLAttributeBinder();
		$img = $document->getElementById("img1");
		$sut->bind("size", "size-large", $img);
		self::assertTrue($img->classList->contains("size-large"));
	}

	public function testBind_modifierQuestion():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new HTMLAttributeBinder();
		$btn1 = $document->getElementById("btn1");
		$btn2 = $document->getElementById("btn2");
		$sut->bind("isBtn1Disabled", true, $btn1);
		$sut->bind("isBtn1Disabled", true, $btn2);
		$sut->bind("isBtn2Disabled", true, $btn1);
		$sut->bind("isBtn2Disabled", true, $btn2);

		self::assertTrue($btn1->disabled);
		self::assertFalse($btn2->disabled);
	}

	public function testBind_modifierQuestion_withNullValue():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new HTMLAttributeBinder();
		$img = $document->getElementById("img3");
		$sut->bind("alternativeText", null, $img);
		self::assertSame("Not bound", $img->alt);
	}

	public function testBind_modifierQuestion_withValue():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new HTMLAttributeBinder();
		$img = $document->getElementById("img3");
		$testMessage = "This is a test message";
		$sut->bind("alternativeText", $testMessage, $img);
		self::assertSame($testMessage, $img->alt);
	}
}

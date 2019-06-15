<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\BoundAttributeDoesNotExistException;
use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\DomTemplateException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use Gt\DomTemplate\Test\Helper\TodoListExampleObject;
use stdClass;

class BindableTest extends TestCase {
	public function testBindMethodAvailable() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$outputTo = $document->querySelector("dl");

		self::assertTrue(
			method_exists($document, "bindKeyValue"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($document, "bindData"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($document, "bindList"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($document, "bindNestedList"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bindKeyValue"),
			"Template container element (dl) is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bindData"),
			"Template container element (dl) is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bindList"),
			"Template container element (dl) is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bindNestedList"),
			"Template container element (dl) is not bindable"
		);
	}

	public function testBindMethodOnTemplateElement() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$template = $document->getTemplate("title-definition");

		self::assertTrue(
			method_exists($template, "bindKeyValue"),
			"Template element is not bindable"
		);
		self::assertTrue(
			method_exists($template, "bindData"),
			"Template element is not bindable"
		);
		self::assertTrue(
			method_exists($template, "bindList"),
			"Template element is not bindable"
		);
		self::assertTrue(
			method_exists($template, "bindNestedList"),
			"Template element is not bindable"
		);
	}

	public function testBindKeyValueExistingElements() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Winston Smith";
		$age = 39;
		$document->bindKeyValue("name", $name);
		$document->bindKeyValue("age", $age);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name, $spanChildren[0]->innerText);
		self::assertEquals($age, $spanChildren[1]->innerText);
	}

	public function testBindDataExistingElements() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Winston Smith";
		$age = 39;
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name,$spanChildren[0]->innerText);
		self::assertEquals($age,$spanChildren[1]->innerText);
	}

	public function testBindDataOnUnknownProperty() {
		$document = new HTMLDocument(Helper::HTML_BIND_UNKNOWN_PROPERTY);
		$name = "Winston Smith";
		$age = 39;
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);

		$test1 = $document->querySelector(".test1");
		$test2 = $document->querySelector(".test2");

		self::assertEquals($name, $test1->getAttribute("unknown"));
		self::assertEquals($age, $test2->textContent);
	}

	public function testBindDataAttributeLookup() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES_BIND_ATTR);
		$name = "Julia Dixon";
		$age = 26;
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name,$spanChildren[0]->innerText);
		self::assertEquals($age,$spanChildren[1]->innerText);
	}

	public function testBindDataAttributeNoMatch() {
		self::expectException(BoundAttributeDoesNotExistException::class);
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES_BIND_ATTR);
		$name = "Julia Dixon";
		$age = 26;
		$document->querySelector("[name=person_id]")->setAttribute(
			"data-bind:text",
			"@does-not-exist"
		);
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);
	}

	public function testBindDataNoMatch() {
		self::expectException(BoundDataNotSetException::class);
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Julia Dixon";
		$age = 26;
		$document->querySelector("span")->setAttribute(
			"data-bind:text",
			"nothing"
		);
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);

		$document->validateBinds();
	}

	public function testBindDataRemoved() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Julia Dixon";
		$age = 26;
		$document->querySelector("span")->setAttribute(
			"data-bind:text",
			"nothing"
		);
		$document->bindData([
			"name" => $name,
			"age" => $age,
		]);

		$document->removeBinds();

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertFalse($spanChildren[0]->hasAttribute("data-bind:text"));
		self::assertFalse($spanChildren[1]->hasAttribute("data-bind:text"));
	}

	public function testInjectAttributePlaceholder() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS);
		$userId = 101;
		$username = "thoughtpolice";
		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("username", $username);

		$link = $document->querySelector("a");
		$img = $document->querySelector("img");
		self::assertEquals("/user/101", $link->href);
		self::assertEquals("/img/profile/$userId.jpg", $img->src);
		self::assertEquals("thoughtpolice's profile picture", $img->alt);
	}
}
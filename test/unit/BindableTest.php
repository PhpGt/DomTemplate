<?php
namespace Gt\DomTemplate\Test;

use EmptyIterator;
use Gt\DomTemplate\IncompatibleBindDataException;
use Gt\DomTemplate\BoundAttributeDoesNotExistException;
use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\NamelessTemplateSpecificityException;
use Gt\DomTemplate\Test\Helper\Helper;
use NumberFormatter;
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

	public function testBindValue() {
		$document = new HTMLDocument(Helper::HTML_KEYLESS_BIND_ATTRIBUTE);
		$document->bindValue("PHPUnit");
		$h1 = $document->querySelector("h1");
		self::assertStringContainsString("Welcome, PHPUnit", $h1->innerText);
	}

	public function testBindValueOnActualElement() {
		$document = new HTMLDocument(Helper::HTML_KEYLESS_BIND_ATTRIBUTE);
		$h1 = $document->querySelector("h1");
		$h1->bindValue("PHPUnit");
		self::assertStringContainsString("Welcome, PHPUnit", $h1->innerText);
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

		$document->removeTemplateAttributes();

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertFalse($spanChildren[0]->hasAttribute("data-bind:text"));
		self::assertFalse($spanChildren[1]->hasAttribute("data-bind:text"));
	}

	public function testBindDataIndexedArray() {
		self::expectException(IncompatibleBindDataException::class);
		$document = new HTMLDocument();
		$document->bindData(["one", "two", "three"]);
	}

	public function testBindDataIterator() {
		self::expectException(IncompatibleBindDataException::class);
		$document = new HTMLDocument();
		$document->bindData(new EmptyIterator());
	}

	public function testInjectAttributePlaceholderNoDataBindParameters() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$username = "thoughtpolice";
		$link = $document->querySelector("a");
		$img = $document->querySelector("img");
		$originalHref = $link->href;
		$originalSrc = $img->src;
		$originalAlt = $img->alt;

		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("username", $username);

		self::assertEquals($originalHref, $link->href);
		self::assertEquals($originalSrc, $img->src);
		self::assertEquals($originalAlt, $img->alt);
	}

	public function testBindParametersPlaceholder() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$username = "thoughtpolice";
		$link = $document->querySelector("a");
		$img = $document->querySelector("img");
		$link->setAttribute("data-bind-parameters", true);
		$img->setAttribute("data-bind-parameters", true);

		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("username", $username);

		self::assertEquals("/user/101", $link->href);
		self::assertEquals("/img/profile/$userId.jpg", $img->src);
		self::assertEquals("thoughtpolice's profile picture", $img->alt);
	}

	public function testBindParametersMultiple() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$userType = "thinkpol";
		$h1 = $document->querySelector("h1");
		$h1->setAttribute("data-bind-parameters", true);
		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("userType", $userType);

		self::assertEquals("heading-thinkpol-101", $h1->id);
	}

	public function testBindParametersMultipleInHref() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$userType = "thinkpol";
		$footer = $document->querySelector("footer");

		$link = $footer->querySelector("a");
		$link->setAttribute("data-bind-parameters", true);
		$footer->bindKeyValue("userId", $userId);
		self::assertNotNull($link->href);
		$footer->bindKeyValue("userType", $userType);
		self::assertNotNull($link->href);
		self::assertEquals(
			"/user.php?id=101&type=thinkpol",
			$link->href
		);
	}

	public function testBindClass() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS);
		$isComplete = true;

		$li = $document->querySelector(".existing-class");
		$todoListElement = $document->getElementById("todo-list");
		$todoListElement->bindKeyValue(
			"complete",
			$isComplete ? "task-complete" : "task-to-do"
		);

		$classList = $li->classList;
		self::assertTrue($classList->contains("existing-class"));
		self::assertTrue($classList->contains("task-complete"));

// If there is already a class on the element, binding to it again will remove it.
		$todoListElement->bindKeyValue(
			"complete",
			$isComplete ? "task-complete" : "task-to-do"
		);
		self::assertFalse($classList->contains("task-complete"));
	}

	public function testBindObjectValue() {
		$dataObj = new StdClass();
		$dataObj->name = "Test name";
		$dataObj->age = 123;

		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$document->bindData($dataObj);

		$spans = $document->querySelectorAll(".bound-data-test span");
		self::assertEquals($dataObj->name, $spans[0]->innerText);
		self::assertEquals($dataObj->age, $spans[1]->innerText);
	}

	public function testBindObjectValueParameter() {
		$dataObj = new StdClass();
		$dataObj->userId = 123;
		$dataObj->username = "Testname";

		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS);
		$document->bindData($dataObj);

		$img = $document->querySelector("img");
		self::assertStringContainsString(
			"{$dataObj->userId}.jpg",
			$img->src
		);
		self::assertEquals(
			"{$dataObj->username}'s profile picture",
			$img->alt
		);
	}

	public function testBindListMultipleDataTemplateElementsNoName() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_NAMELESS_BIND_LIST);
		$document->extractTemplates();
// This will fail, because I am not passing a template name to bind to, and there
// are more than one nameless template elements in the document.
// Instead, I should either pass a template name, or bind to a more specific element,
// such as the UL itself.
		self::expectException(NamelessTemplateSpecificityException::class);
// No need for any actual data during this test.
		$document->bindList([[]]);
	}

	public function testBindListMultipleDataTemplateElementsWithName() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_NAMES_BIND_LIST);
		$document->extractTemplates();
		$stateList = [
			["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			["ministry-name" => "Peace", "ministry-id" => 123],
			["ministry-name" => "Plenty", "ministry-id" => 511],
			["ministry-name" => "Truth", "ministry-id" => 141],
			["ministry-name" => "Love", "ministry-id" => 610],
		];

		$firstList = $document->getElementById("list-1");
		$secondList = $document->getElementById("list-2");

// Note that the difference here to the test above and below this one is that
// we're actually passing a template name, even though we're still binding to
// the root document node.
		$document->bindList($stateList, "state");
		$document->bindList($ministryList, "ministry");

		self::assertCount(count($stateList), $firstList->children);
		self::assertCount(count($ministryList), $secondList->children);

		self::assertEquals($stateList[1]["state-name"], $firstList->querySelectorAll("li")[1]->innerText);
		self::assertEquals($ministryList[2]["ministry-name"], $secondList->querySelectorAll("li")[2]->innerText);
	}

	public function testBindListMultipleDataTemplateElements() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_NAMELESS_BIND_LIST);
		$document->extractTemplates();
		$stateList = [
			["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			["ministry-name" => "Peace", "ministry-id" => 123],
			["ministry-name" => "Plenty", "ministry-id" => 511],
			["ministry-name" => "Truth", "ministry-id" => 141],
			["ministry-name" => "Love", "ministry-id" => 610],
		];

		$firstList = $document->getElementById("list-1");
		$secondList = $document->getElementById("list-2");

		$secondList->bindList($ministryList);
		$firstList->bindList($stateList);

		self::assertCount(count($stateList), $firstList->children);
		self::assertCount(count($ministryList), $secondList->children);

		self::assertEquals($stateList[1]["state-name"], $firstList->querySelectorAll("li")[1]->innerText);
		self::assertEquals($ministryList[2]["ministry-name"], $secondList->querySelectorAll("li")[2]->innerText);
	}

	public function testBindListAttributeWithNoValue() {
		$document = new HTMLDocument(Helper::HTML_SELECT);
		$document->extractTemplates();
		$sut = $document->getElementById("sut");
		$data = [];

		$formatter = new NumberFormatter(
			"en_GB",
			NumberFormatter::SPELLOUT
		);

		for($i = 0; $i < 10; $i++) {
			$row = [
				"text" => $formatter->format($i),
				"value" => $i,
				"isDisabled" => (bool)($i % 2),
			];

			if($i === 5) {
				$row["isDisabled"] = true;
			}

			$data []= $row;
		}

		$sut->bindList($data);

		foreach($sut->querySelectorAll("option") as $i => $option) {
			$shouldBeDisabled = (bool)($i % 2);
			if($i === 5) {
				$shouldBeDisabled = true;
			}

			$text = $option->innerText;
			$value = $option->value;

			if($shouldBeDisabled) {
				self::assertTrue(
					$option->hasAttribute("disabled"),
					"Option should have 'disabled' attribute"
				);
			}
			else {
				self::assertFalse(
					$option->hasAttribute("disabled"),
					"Option should not have 'disabled' attribute"
				);
			}

			$expectedText = $formatter->format($i);
			self::assertEquals($expectedText, $text);
			self::assertEquals($i, $value);
		}
	}

	public function testBindObjectValue() {
		$dataObj = new StdClass();
		$dataObj->name = "Test name";
		$dataObj->age = 123;

		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$document->bindData($dataObj);

		$spans = $document->querySelectorAll(".bound-data-test span");
		self::assertEquals($dataObj->name, $spans[0]->innerText);
		self::assertEquals($dataObj->age, $spans[1]->innerText);
	}

	public function testBindObjectValueParameter() {
		$dataObj = new StdClass();
		$dataObj->userId = 123;
		$dataObj->username = "Testname";

		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS);
		$document->bindData($dataObj);

		$img = $document->querySelector("img");
		self::assertStringContainsString(
			"{$dataObj->userId}.jpg",
			$img->src
		);
		self::assertEquals(
			"{$dataObj->username}'s profile picture",
			$img->alt
		);
	}
}
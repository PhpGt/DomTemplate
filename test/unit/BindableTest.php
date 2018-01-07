<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use PHPUnit\Framework\TestCase;

class BindableTest extends TestCase {
	public function testBindMethodAvailable() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$outputTo = $document->querySelector("dl");

		self::assertTrue(
			method_exists($document, "bind"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bind"),
			"Template container element (dl) is not bindable"
		);
	}

	public function testBindMethodOnTemplateElement() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$template = $document->getTemplate("title-definition");

		self::assertTrue(
			method_exists($template, "bind"),
			"Template element is not bindable"
		);
	}

	public function testBindExistingElements() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Winston Smith";
		$age = 39;
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name,$spanChildren[0]->innerText);
		self::assertEquals($age,$spanChildren[1]->innerText);
	}

	/**
	 * @expectedException \Gt\DomTemplate\InvalidBindProperty
	 */
	public function testBindOnUnknownProperty() {
		$document = new HTMLDocument(Helper::HTML_BIND_UNKNOWN_PROPERTY);
		$name = "Winston Smith";
		$age = 39;
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);
	}

	public function testBindAttributeLookup() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES_BIND_ATTR);
		$name = "Julia Dixon";
		$age = 26;
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name,$spanChildren[0]->innerText);
		self::assertEquals($age,$spanChildren[1]->innerText);
	}

	/**
	 * @expectedException \Gt\DomTemplate\BoundAttributeDoesNotExistException
	 */
	public function testBindAttributeNoMatch() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES_BIND_ATTR);
		$name = "Julia Dixon";
		$age = 26;
		$document->querySelector("[name=person_id]")->setAttribute(
			"data-bind:text",
			"@does-not-exist"
		);
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);
	}

	/**
	 * @expectedException \Gt\DomTemplate\BoundDataNotSetException
	 */
	public function testBindDataNoMatch() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Julia Dixon";
		$age = 26;
		$document->querySelector("span")->setAttribute(
			"data-bind:text",
			"nothing"
		);
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);
	}

	public function testTemplateTodoList() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$todoData = [
			["id" => 1, "title" => "Write tests", "complete" => true],
			["id" => 2, "title" => "Implement features", "complete" => false],
			["id" => 3, "title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");
		$todoListElement->bind($todoData);

		$liChildren = $todoListElement->querySelectorAll("li");

		self::assertCount(
			count($todoData),
			$liChildren,
			"There should be the same amount of li elements as there are rows of data"
		);

		foreach($todoData as $i => $row) {
			self::assertContains(
				$row["title"],
				$liChildren[$i]->innerHTML
			);
		}
	}

	public function testBoundTemplatesCleanedUpAfterAdding() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$todoData = [
			["id" => 1, "title" => "Write tests", "complete" => true],
			["id" => 2, "title" => "Implement features", "complete" => false],
			["id" => 3, "title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");
		$todoListElement->bind($todoData);

		self::assertContains("Implement features", $todoListElement->innerHTML);
		self::assertNotContains("data-bind", $todoListElement->innerHTML);
	}

	public function testBindWithInlineNamedTemplate() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_INLINE_NAMED_TEMPLATE);
		$todoData = [
			["id" => 1, "title" => "Write tests", "complete" => true],
			["id" => 2, "title" => "Implement features", "complete" => false],
			["id" => 3, "title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");
		$todoListElement->bind($todoData);

		self::assertContains("Implement features", $todoListElement->innerHTML);
		self::assertNotContains("data-bind", $todoListElement->innerHTML);
	}

	public function testBindWithInlineNamedTemplateWhenAnotherTemplateExists() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_INLINE_NAMED_TEMPLATE_DOUBLE);
		$todoData = [
			["id" => 1, "title" => "Write tests", "complete" => true],
			["id" => 2, "title" => "Implement features", "complete" => false],
			["id" => 3, "title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();

		$todoListElement = $document->getElementById("todo-list");
		$todoListElement->bind($todoData);
		self::assertContains("Implement features", $todoListElement->innerHTML);
		self::assertNotContains("Use the other template instead!", $todoListElement->innerHTML);

		$todoListElement = $document->getElementById("todo-list-2");
		$todoListElement->bind($todoData, "todo-list-item");

		self::assertContains("Implement features", $todoListElement->innerHTML);
		self::assertNotContains("Use the other template instead!", $todoListElement->innerHTML);
	}

	public function testBindWithNonOptionalKey() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$todoData = [
			["title" => "Write tests", "complete" => true],
			["title" => "Implement features", "complete" => false],
			["title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		self::expectException(BoundDataNotSetException::class);
		$todoListElement->bind($todoData);
	}

	public function testBindWithOptionalKey() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_OPTIONAL_ID);
		$todoData = [
			["title" => "Write tests", "complete" => true],
			["title" => "Implement features", "complete" => false],
			["title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		$todoListElement->bind($todoData);
		$items = $todoListElement->querySelectorAll("li");
		self::assertCount(3, $items);

		self::assertEquals(
			"Implement features",
			$items[1]->querySelector("input[name=title]")->value
		);
		self::assertNull(
			$items[1]->querySelector("input[name=id]")->value
		);
	}

	public function testBindClass() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS);
		$todoData = [
			["title" => "Write tests", "complete" => true],
			["title" => "Implement features", "complete" => false],
			["title" => "Pass tests", "complete" => false],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		$todoListElement->bind($todoData);
		$items = $todoListElement->querySelectorAll("li");

		foreach($todoData as $i => $todoDatum) {
			self::assertEquals(
				$todoDatum["complete"],
				$items[$i]->classList->contains("complete")
			);

			self::assertTrue($items[$i]->classList->contains("existing-class"));
		}
	}

	public function testBindClassColon() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS_COLON);
		$todoData = [
			["title" => "Write tests", "dateTimeCompleted" => "2018-07-01 19:46:00"],
			["title" => "Implement features", "dateTimeCompleted" => null],
			["title" => "Pass tests", "dateTimeCompleted" => "2018-07-01 19:49:00"],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		$todoListElement->bind($todoData);
		$items = $todoListElement->querySelectorAll("li");

		foreach($todoData as $i => $todoDatum) {
			$completed = (bool)$todoDatum["dateTimeCompleted"];
			self::assertEquals(
				$completed,
				$items[$i]->classList->contains("complete")
			);

			self::assertTrue($items[$i]->classList->contains("existing-class"));
		}
	}

	public function testBindClassColonMultiple() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS_COLON);
		$todoData = [
			[
				"title" => "Write tests",
				"dateTimeCompleted" => "2018-07-01 19:46:00",
				"dateTimeDeleted" => null,
			],
			[
				"title" => "Implement features",
				"dateTimeCompleted" => null,
				"dateTimeDeleted" => "2018-07-01 19:54:00",
			],
			[
				"title" => "Pass tests",
				"dateTimeCompleted" => "2018-07-01 19:49:00",
				"dateTimeDeleted" => null,
			],
		];
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		$todoListElement->bind($todoData);
		$items = $todoListElement->querySelectorAll("li");

		foreach($todoData as $i => $todoDatum) {
			$completed = (bool)$todoDatum["dateTimeCompleted"];
			self::assertEquals(
				$completed,
				$items[$i]->classList->contains("complete")
			);

			$deleted = (bool)$todoDatum["dateTimeDeleted"];
			self::assertEquals(
				$completed,
				$items[$i]->classList->contains("deleted")
			);

			self::assertTrue($items[$i]->classList->contains("existing-class"));
		}
	}
}
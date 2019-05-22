<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\BoundAttributeDoesNotExistException;
use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\DomTemplateException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use stdClass;

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

	public function testBindOnUnknownProperty() {
		$document = new HTMLDocument(Helper::HTML_BIND_UNKNOWN_PROPERTY);
		$name = "Winston Smith";
		$age = 39;
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);

		$test1 = $document->querySelector(".test1");
		$test2 = $document->querySelector(".test2");

		self::assertEquals($name, $test1->getAttribute("unknown"));
		self::assertEquals($age, $test2->textContent);
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

	public function testBindAttributeNoMatch() {
		self::expectException(BoundAttributeDoesNotExistException::class);
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

	public function testBindDataNoMatch() {
		self::expectException(BoundDataNotSetException::class);
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
			self::assertStringContainsString(
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

		self::assertStringContainsString(
			"Implement features",
			$todoListElement->innerHTML
		);
		self::assertStringNotContainsString(
			"data-bind",
			$todoListElement->innerHTML
		);
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

		self::assertStringContainsString(
			"Implement features",
			$todoListElement->innerHTML
		);
		self::assertStringNotContainsString(
			"data-bind",
			$todoListElement->innerHTML
		);
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
		self::assertStringContainsString(
			"Implement features",
			$todoListElement->innerHTML
		);
		self::assertStringNotContainsString(
			"Use the other template instead!",
			$todoListElement->innerHTML
		);

		$todoListElement = $document->getElementById("todo-list-2");
		$todoListElement->bind($todoData, "todo-list-item");

		self::assertStringContainsString(
			"Implement features",
			$todoListElement->innerHTML
		);
		self::assertStringNotContainsString(
			"Use the other template instead!",
			$todoListElement->innerHTML
		);
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
			["id" => 1, "title" => "Write tests", "complete" => true],
			["id" => 2, "title" => "Implement features", "complete" => false],
			["id" => 3, "title" => "Pass tests", "complete" => false],
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
			["id" => 1, "title" => "Write tests", "dateTimeCompleted" => "2018-07-01 19:46:00"],
			["id" => 2, "title" => "Implement features", "dateTimeCompleted" => null],
			["id" => 3, "title" => "Pass tests", "dateTimeCompleted" => "2018-07-01 19:49:00"],
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
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS_COLON_MULTIPLE);
		$todoData = [
			[
				"id" => 1,
				"title" => "Write tests",
				"dateTimeCompleted" => "2018-07-01 19:46:00",
				"dateTimeDeleted" => null,
			],
			[
				"id" => 2,
				"title" => "Implement features",
				"dateTimeCompleted" => null,
				"dateTimeDeleted" => "2018-07-01 19:54:00",
			],
			[
				"id" => 3,
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
			self::assertTrue($items[$i]->classList->contains("existing-class"));

			$completed = (bool)$todoDatum["dateTimeCompleted"];
			self::assertEquals(
				$completed,
				$items[$i]->classList->contains("complete")
			);

			$deleted = (bool)$todoDatum["dateTimeDeleted"];
			self::assertEquals(
				$deleted,
				$items[$i]->classList->contains("deleted")
			);
		}
	}

	public function testBindWithObjectData() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_BIND_CLASS_COLON_MULTIPLE);
		$todoData = [
			[
				"id" => 1,
				"title" => "Write tests",
				"dateTimeCompleted" => "2018-07-01 19:46:00",
				"dateTimeDeleted" => null,
			],
			[
				"id" => 2,
				"title" => "Implement features",
				"dateTimeCompleted" => null,
				"dateTimeDeleted" => "2018-07-01 19:54:00",
			],
			[
				"id" => 3,
				"title" => "Pass tests",
				"dateTimeCompleted" => "2018-07-01 19:49:00",
				"dateTimeDeleted" => null,
			],
		];

		$todoObjData = [];

		foreach($todoData as $todo) {
			$obj = new StdClass();
			foreach($todo as $key => $value) {
				$obj->$key = $value;
			}

			$todoObjData []= $obj;
		}

		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");

		$todoListElement->bind($todoObjData);
		$items = $todoListElement->querySelectorAll("li");

		foreach($todoObjData as $i => $todo) {
			self::assertTrue($items[$i]->classList->contains("existing-class"));

			$completed = (bool)$todo->dateTimeCompleted;
			self::assertEquals(
				$completed,
				$items[$i]->classList->contains("complete")
			);

			$deleted = (bool)$todo->dateTimeDeleted;
			self::assertEquals(
				$deleted,
				$items[$i]->classList->contains("deleted")
			);
		}
	}

// For issue #52:
	public function testBindingDataWithBindableParentElement() {
		$document = new HTMLDocument(Helper::HTML_PARENT_HAS_DATA_BIND_ATTR);
		$document->extractTemplates();

		$data = [
			["example-key" => "example-value-1","target-key" => "target-value-1"],
			["example-key" => "example-value-2","target-key" => "target-value-2"],
			["example-key" => "example-value-3","target-key" => "target-value-3"],
		];

		$exception = null;

		foreach($data as $row) {
			$t = $document->getTemplate("target-template");
			try {
				$t->bind($row);
			}
			catch(DomTemplateException $exception) {}

			$t->insertTemplate();
		}

		self::assertNull($exception);
	}

	public function testBindingDataWithBindableParentElementDoesNotAddMoreNodes() {
		$document = new HTMLDocument(Helper::HTML_PARENT_HAS_DATA_BIND_ATTR);
		$document->extractTemplates();

		$document->querySelector("label>span")->bind(
			["outside-scope" => "example content"]
		);

		$data = [
			["example-key" => "example-value-1","target-key" => "target-value-1"],
			["example-key" => "example-value-2","target-key" => "target-value-2"],
			["example-key" => "example-value-3","target-key" => "target-value-3"],
		];

		$exception = null;

		try {
			$document->querySelector("ul")->bind($data);
		}
		catch(DomTemplateException $exception) {}
		self::assertNull($exception);

		self::assertCount(3, $document->querySelectorAll("ul li"));
		self::assertEquals(
			"example content",
			$document->querySelector("label>span")->textContent
		);
	}

	public function testMultipleListBindSameDocument() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_BINDABLE_LIST);
		$document->extractTemplates();

		$oneToTen = [];
		for($i = 1; $i <= 10; $i++) {
			$oneToTen []= [
				"i" => $i,
			];
		}

		$document->querySelector(".area-1 ul")->bind($oneToTen);
		$document->querySelector("h1")->bind([
			"name" => "Example Name",
		]);

		$startingNumber = rand(100, 1000);
		$document->querySelector(".area-2 p")->bind([
			"start" => $startingNumber,
		]);

		for($i = $startingNumber; $i <= $startingNumber + 10; $i++) {
			$t = $document->getTemplate("dynamic-list-item");
			$t->bind(["i" => $i]);
			$t->insertTemplate();
		}

		foreach($document->querySelectorAll(".area-1 ul li") as $i => $li) {
			$number = $i + 1;
			self::assertStringContainsString($number, $li->textContent);
		}

		foreach($document->querySelectorAll(".area-2 ul li") as $i => $li) {
			$number = $i + $startingNumber;
			self::assertStringContainsString($number, $li->textContent);
		}

		self::assertStringContainsString("Example Name", $document->querySelector("h1")->textContent);
		self::assertStringContainsString($startingNumber, $document->querySelector(".area-2 p")->textContent);
	}

	public function testBindingTodoListFromObject() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();

//		$list = new
	}
}
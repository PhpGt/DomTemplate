<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLElement\HTMLButtonElement;
use Gt\Dom\HTMLElement\HTMLImageElement;
use Gt\Dom\HTMLElement\HTMLParagraphElement;
use Gt\Dom\HTMLElement\HTMLTableCellElement;
use Gt\Dom\HTMLElement\HTMLTableElement;
use Gt\Dom\HTMLElement\HTMLTableRowElement;
use Gt\Dom\HTMLElement\HTMLTableSectionElement;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\IncompatibleBindDataException;
use Gt\DomTemplate\IncorrectTableDataFormat;
use Gt\DomTemplate\InvalidBindPropertyException;
use Gt\DomTemplate\TableElementNotFoundInContextException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

class DocumentBinderTest extends TestCase {
	/**
	 * If the developer forgets to add a bind property (the bit after the
	 * colon in `data-bind:text`, we should let them know with a friendly
	 * error message.
	 */
	public function testBindValue_missingBindProperty():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_NO_BIND_PROPERTY);
		$sut = new DocumentBinder($document);
		self::expectException(InvalidBindPropertyException::class);
		self::expectExceptionMessage("<output> Element has a data-bind attribute with missing bind property - did you mean `data-bind:text`?");
		$sut->bindValue("Test!");
	}

	public function testBindValue_singleElement():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SINGLE_ELEMENT);
		$sut = new DocumentBinder($document);
		$output = $document->querySelector("output");
		self::assertSame("Nothing is bound", $output->textContent);
		$sut->bindValue("Test!");
		self::assertSame("Test!", $output->textContent);
	}

	public function testBindValue_multipleElements():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_ELEMENTS);
		$sut = new DocumentBinder($document);
		$output1 = $document->getElementById("o1");
		$output2 = $document->getElementById("o2");
		$output3 = $document->getElementById("o3");
		$sut->bindValue("Test!");
		self::assertSame("Test!", $output1->textContent);
		self::assertSame("Test!", $output2->textContent);
		self::assertSame("Test!", $output3->textContent);
	}

	public function testBindValue_multipleNestedElements():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_NESTED_ELEMENTS);
		$sut = new DocumentBinder($document);
		$container1 = $document->getElementById("container1");
		$container2 = $document->getElementById("container2");
		$sut->bindValue("Test!", $container1);

		foreach($container1->querySelectorAll("output") as $output) {
			self::assertSame("Test!", $output->textContent);
		}
		foreach($container2->querySelectorAll("output") as $output) {
			self::assertNotSame("Test!", $output->textContent);
		}

		$sut->bindValue("Test!", $container2);
		foreach($container1->querySelectorAll("output") as $output) {
			self::assertSame("Test!", $output->textContent);
		}
		foreach($container2->querySelectorAll("output") as $output) {
			self::assertSame("Test!", $output->textContent);
		}
	}

	public function testBindValue_multipleNestedElements_skipsElementWithBindProperty():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_NESTED_ELEMENTS);
		$sut = new DocumentBinder($document);
		$container3 = $document->getElementById("container3");
		$sut->bindValue("Test!", $container3);
		self::assertSame("Default title", $document->querySelector("#container3 h1")->textContent);
		self::assertSame("Test!", $document->getElementById("o7")->textContent);
	}

	public function testBindValue_synonymousProperties():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SYNONYMOUS_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);
		$sut->bindValue("updated <b>bold</b>");

		self::assertSame("updated &lt;b&gt;bold&lt;/b&gt;", $document->getElementById("o1")->innerHTML);
		self::assertSame("updated &lt;b&gt;bold&lt;/b&gt;", $document->getElementById("o2")->innerHTML);
		self::assertSame("updated &lt;b&gt;bold&lt;/b&gt;", $document->getElementById("o3")->innerHTML);
		self::assertSame("updated &lt;b&gt;bold&lt;/b&gt;", $document->getElementById("o4")->innerHTML);
		self::assertSame("updated &lt;b&gt;bold&lt;/b&gt;", $document->getElementById("o5")->innerHTML);
		self::assertSame("updated <b>bold</b>", $document->getElementById("o6")->innerHTML);
		self::assertSame("updated <b>bold</b>", $document->getElementById("o7")->innerHTML);
		self::assertSame("updated <b>bold</b>", $document->getElementById("o8")->innerHTML);
		self::assertSame("updated <b>bold</b>", $document->getElementById("o9")->innerHTML);
	}

	public function testBindKeyValue_noMatches():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SINGLE_ELEMENT);
		$sut = new DocumentBinder($document);
		$sut->bindKeyValue("missing", "example");
		self::assertSame("Nothing is bound", $document->querySelector("output")->innerHTML);
	}

	public function testBindKeyValue_noMatchesInDifferentHierarchy():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_NESTED_ELEMENTS);
		$sut = new DocumentBinder($document);
// The "title" bind element is actually within the #c3 hierarchy so should not be bound.
		$sut->bindKeyValue("title", "This should not bind", $document->getElementById("container1"));
		self::assertSame("Default title", $document->querySelector("#container3 h1")->textContent);
	}

	public function testBindKeyValue():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_NESTED_ELEMENTS);
		$sut = new DocumentBinder($document);
		$sut->bindKeyValue("title", "This should bind");
		self::assertSame("This should bind", $document->querySelector("#container3 h1")->textContent);
		self::assertSame("This should bind", $document->querySelector("#container3 p span")->textContent);
	}

	public function testBindData_assocArray():void {
		$username = uniqid("user");
		$email = uniqid() . "@example.com";
		$category = uniqid("category-");

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new DocumentBinder($document);
		$sut->bindData([
			"username" => $username,
			"email" => $email,
			"category" => $category,
		]);

		self::assertSame($username, $document->getElementById("dd1")->textContent);
		self::assertSame($email, $document->getElementById("dd2")->textContent);
		self::assertSame($category, $document->getElementById("dd3")->textContent);
	}

	public function testBindData_indexedArray():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new DocumentBinder($document);
		self::expectException(IncompatibleBindDataException::class);
		self::expectExceptionMessage("bindData is only compatible with key-value-pair data, but it was passed an indexed array.");
		$sut->bindData(["one", "two", "three"]);
	}

	public function testBindData_object():void {
		$userObject = new StdClass();
		$userObject->username = "g105b";
		$userObject->email = "greg.bowler@g105b.com";
		$userObject->category = "maintainer";

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new DocumentBinder($document);
		$sut->bindData($userObject);

		self::assertSame($userObject->username, $document->getElementById("dd1")->textContent);
		self::assertSame($userObject->email, $document->getElementById("dd2")->textContent);
		self::assertSame($userObject->category, $document->getElementById("dd3")->textContent);
	}

	public function testBindData_indexArray_shouldThrowException():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new DocumentBinder($document);
		self::expectException(IncompatibleBindDataException::class);
		self::expectExceptionMessage("bindData is only compatible with key-value-pair data, but it was passed an indexed array.");
		$sut->bindData(["one", "two", "three"]);
	}

	public function testBindData_outOfContext():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new DocumentBinder($document);
		$sut->bindData([
			"username" => "will-not-bind",
			"email" => "will-not-bind",
			"category" => "will-not-bind",
		], $document->getElementById("audit-trail"));

		self::assertNotSame("will-not-bind", $document->getElementById("dd1")->textContent);
		self::assertNotSame("will-not-bind", $document->getElementById("dd2")->textContent);
		self::assertNotSame("will-not-bind", $document->getElementById("dd3")->textContent);
	}

	public function testBindKeyValue_arbitraryAttributes():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);
		/** @var HTMLImageElement $img */
		$img = $document->getElementById("img1");

		$sut->bindKeyValue("photoURL", "/cat.jpg");
		self::assertSame("/cat.jpg", $img->src);

		$sut->bindKeyValue("altText", "My cat");
		self::assertSame("My cat", $img->alt);
	}

	public function testBindKeyValue_classAttribute():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLImageElement $img */
		$img = $document->getElementById("img1");

		self::assertSame("main", $img->className);
		$sut->bindKeyValue("size", "large");
		self::assertSame("main large", $img->className);
		$sut->bindKeyValue("size", "large");
		self::assertSame("main large", $img->className);
	}

	public function testBindKeyValue_classToggle():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLImageElement $img */
		$img = $document->getElementById("img2");

		self::assertSame("secondary", $img->className);
		$sut->bindKeyValue("is-selected", true, $img);
		self::assertSame("secondary is-selected", $img->className);
		$sut->bindKeyValue("is-selected", false, $img);
		self::assertSame("secondary", $img->className);
	}

	public function testBindKeyValue_classToggle_differentClassNameToBindKey():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLImageElement $img */
		$img = $document->getElementById("img3");

		self::assertSame("secondary", $img->className);
		$sut->bindKeyValue("isSelected", true, $img);
		self::assertSame("secondary selected-image", $img->className);
		$sut->bindKeyValue("isSelected", false, $img);
		self::assertSame("secondary", $img->className);
	}

	public function testBindKeyValue_toggleArbitraryAttribute():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLParagraphElement $paragraph */
		$paragraph = $document->getElementById("p1");

		self::assertSame("funny friendly", $paragraph->dataset->params);
		$sut->bindKeyValue("isMagic", false, $paragraph);
		self::assertSame("funny friendly", $paragraph->dataset->params);
		$sut->bindKeyValue("isMagic", true, $paragraph);
		self::assertSame("funny friendly magical", $paragraph->dataset->params);
		$sut->bindKeyValue("isMagic", false, $paragraph);
		self::assertSame("funny friendly", $paragraph->dataset->params);
	}

	/**
	 * This tests the `data-bind:disabled="?isDisabled" functionality. The
	 * question mark at the start of the bind parameter indicates that the
	 * bind attribute will be toggled depending on a bound boolean value.
	 */
	public function testBindKeyValue_toggleDisabled():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLButtonElement $button */
		$button = $document->getElementById("btn1");

		self::assertFalse($button->disabled);
		$sut->bindKeyValue("isBtn1Disabled", true);
		self::assertTrue($button->disabled);
		$sut->bindKeyValue("isBtn1Disabled", false);
		self::assertFalse($button->disabled);
	}

	/**
	 * This tests the inverse logic of the above test. The bind parameter
	 * is prefixed with a question mark AND an exclamation mark, meaning to
	 * use the inverse of what is passed. This makes sense for the
	 * "disabled" attribute, because it is likely that the data represents
	 * whether the element should be enabled (but there's no "enabled"
	 * HTML attribute).
	 */
	public function testBindKeyValue_toggleDisabled_inverseLogic():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_DIFFERENT_BIND_PROPERTIES);
		$sut = new DocumentBinder($document);

		/** @var HTMLButtonElement $button */
		$button = $document->getElementById("btn2");

		self::assertFalse($button->disabled);
		$sut->bindKeyValue("isBtn2Enabled", false);
		self::assertTrue($button->disabled);
		$sut->bindKeyValue("isBtn2Enabled", true);
		self::assertFalse($button->disabled);
	}

	/**
	 * Binding table data into an empty table will create all the
	 * appropriate <thead>, <tbody>, <tr>, <th>, and <td> elements.
	 */
	public function testBindKeyValue_table_emptyTable():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");

		self::assertEmpty($table->innerHTML);
		$sut->bindKeyValue("tableData", [
			["Column 1", "Column 2", "Column 3"],
			["c1 val1", "c2 val1", "c3 val1"],
			["c1 val2", "c2 val2", "c3 val2"],
			["c1 val3", "c2 val3", "c3 val3"],
		], $table);

		self::assertSame("Column 1", $table->tHead->rows[0]->children[0]->textContent);
		self::assertSame("Column 2", $table->tHead->rows[0]->children[1]->textContent);
		self::assertSame("Column 3", $table->tHead->rows[0]->children[2]->textContent);

		/** @var HTMLTableSectionElement $tBody */
		$tBody = $table->tBodies[0];
		self::assertCount(3, $tBody->children);

		self::assertSame("c1 val2", $tBody->rows[1]->children[0]->textContent);
		self::assertSame("c2 val2", $tBody->rows[1]->children[1]->textContent);
		self::assertSame("c3 val2", $tBody->rows[1]->children[2]->textContent);
	}

	/**
	 * Binding table data into a table that already has a <thead> element
	 * will use the existing <th> values to limit which columns are output.
	 */
	public function testBindKeyValue_table_existingTHead():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl2");

		$thead = $table->tHead;
		$originalTheadHTML = $thead->innerHTML;

		$tableData = [
// Notice that there are more columns here than in the actual HTML.
			["id", "firstName", "lastName", "username", "email"],
			[34, "Derek", "Rethans", "derek", "derek@php.net"],
			[35, "Christoph", "Becker", "cmbecker69", "cmbecker69@php.net"],
			[25, "Sara", "Golemon", "pollita", "pollita@php.net"],
		];
		$sut->bindKeyValue("tableData", $tableData, $table);

		/** @var HTMLTableSectionElement $tbody */
		$tbody = $table->tBodies[0];

		self::assertSame($originalTheadHTML, $thead->innerHTML);
		self::assertCount(count($tableData), $tbody->rows);
		/** @var HTMLTableRowElement $row0 */
		$row0 = $tbody->rows[0];
		/** @var HTMLTableRowElement $row1 */
		$row1 = $tbody->rows[1];
		/** @var HTMLTableRowElement $row2 */
		$row2 = $tbody->rows[2];
		/** @var HTMLTableRowElement $row3 */
		$row3 = $tbody->rows[3];
		self::assertCount(3, $row0->cells);
		self::assertCount(3, $row1->cells);
		self::assertCount(3, $row2->cells);
		self::assertCount(3, $row3->cells);

		self::assertSame("Greg", $row0->cells[0]->textContent);
		self::assertSame("Bowler", $row0->cells[1]->textContent);
		self::assertSame("greg@php.gt", $row0->cells[2]->textContent);
		self::assertSame("Derek", $row1->cells[0]->textContent);
		self::assertSame("Rethans", $row1->cells[1]->textContent);
		self::assertSame("derek@php.net", $row1->cells[2]->textContent);
		self::assertSame("Christoph", $row2->cells[0]->textContent);
		self::assertSame("Becker", $row2->cells[1]->textContent);
		self::assertSame("cmbecker69@php.net", $row2->cells[2]->textContent);
		self::assertSame("Sara", $row3->cells[0]->textContent);
		self::assertSame("Golemon", $row3->cells[1]->textContent);
		self::assertSame("pollita@php.net", $row3->cells[2]->textContent);
	}

	public function testBindKeyValue_tableDataNormalised():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl2");

		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];
		$sut->bindKeyValue("tableData", $tableData, $table);

		/** @var HTMLTableSectionElement $tbody */
		$tbody = $table->tBodies[0];
		/** @var HTMLTableRowElement $row0 */
		$row0 = $tbody->rows[0];
		/** @var HTMLTableRowElement $row1 */
		$row1 = $tbody->rows[1];
		/** @var HTMLTableRowElement $row2 */
		$row2 = $tbody->rows[2];
		/** @var HTMLTableRowElement $row3 */
		$row3 = $tbody->rows[3];

		self::assertSame("Greg", $row0->cells[0]->textContent);
		self::assertSame("Bowler", $row0->cells[1]->textContent);
		self::assertSame("greg@php.gt", $row0->cells[2]->textContent);
		self::assertSame("Derek", $row1->cells[0]->textContent);
		self::assertSame("Rethans", $row1->cells[1]->textContent);
		self::assertSame("derek@php.net", $row1->cells[2]->textContent);
		self::assertSame("Christoph", $row2->cells[0]->textContent);
		self::assertSame("Becker", $row2->cells[1]->textContent);
		self::assertSame("cmbecker69@php.net", $row2->cells[2]->textContent);
		self::assertSame("Sara", $row3->cells[0]->textContent);
		self::assertSame("Golemon", $row3->cells[1]->textContent);
		self::assertSame("pollita@php.net", $row3->cells[2]->textContent);
	}

	/**
	 * A "double header" is a term I use to describe tables that have
	 * header data in the first column going along the top, but also another
	 * header line as the first row. See the MDN example for why <th> might
	 * be present in the first column of a table:
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/tbody
	 */
	public function testBindKeyValue_doubleHeaderTable_shouldEmitTHElementsInRows():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");

		$tableData = [
			["Item", "Price", "Stock Level"],
			[
				"Washing machine" => [698_00, 24],
				"Television" => [998_00, 7],
				"Laptop" => [799_99, 60],
			]
		];
		$sut->bindKeyValue("tableData", $tableData, $table);

		/** @var HTMLTableSectionElement $tbody */
		$tbody = $table->tBodies[0];
		/** @var HTMLTableRowElement $row0 */
		$row0 = $tbody->rows[0];
		/** @var HTMLTableRowElement $row1 */
		$row1 = $tbody->rows[1];
		/** @var HTMLTableRowElement $row2 */
		$row2 = $tbody->rows[2];

		foreach($row0->cells as $i => $cell) {
			/** @var HTMLTableCellElement $cell */
			if($i === 0) {
				self::assertSame("TH", $cell->tagName);
				self::assertSame("Washing machine", $cell->textContent);
			}
			else {
				self::assertSame("TD", $cell->tagName);
				self::assertEquals($tableData[1]["Washing machine"][$i - 1], $cell->textContent);
			}
		}

		foreach($row1->cells as $i => $cell) {
			/** @var HTMLTableCellElement $cell */
			if($i === 0) {
				self::assertSame("TH", $cell->tagName);
				self::assertSame("Television", $cell->textContent);
			}
			else {
				self::assertSame("TD", $cell->tagName);
				self::assertEquals($tableData[1]["Television"][$i - 1], $cell->textContent);
			}
		}

		foreach($row2->cells as $i => $cell) {
			/** @var HTMLTableCellElement $cell */
			if($i === 0) {
				self::assertSame("TH", $cell->tagName);
				self::assertSame("Laptop", $cell->textContent);
			}
			else {
				self::assertSame("TD", $cell->tagName);
				self::assertEquals($tableData[1]["Laptop"][$i - 1], $cell->textContent);
			}
		}
	}

	public function testBindKeyValue_tableData_nonIterableValue():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");

		$tableData = [
			["Item", "Price", "Stock Level"],
			"incorrect",
			"table",
			"format",
		];
		self::expectException(IncorrectTableDataFormat::class);
		self::expectExceptionMessage("Row 1 data is not iterable.");
		$sut->bindKeyValue("tableData", $tableData, $table);
	}

	public function testBindKeyValue_tableData_doubleHeaderNonIterableValue():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");

		$tableData = [
			["Item", "Price", "Stock Level"],
			[
// Note that the inner array does not represent its data as an array.
				"Washing machine" => 698_00,
				"Television" => 998_00,
				"Laptop" => 799_99,
			]
		];
		self::expectException(IncorrectTableDataFormat::class);
		self::expectExceptionMessage("Row 1 has a string key (Washing machine) but the value is not iterable.");
		$sut->bindKeyValue("tableData", $tableData, $table);
	}

	public function testBindKeyValue_tableData_assocArrayWithoutIterableColumns():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");
		$tableData = [
// This is emulating a common syntax mistake - the columns are not within an
// array, but from a glance the shape looks OK.
			"Item" => ["Washing machine", "Television", "Laptop"],
			"Price" => 698_00, 998_00, 799_99,
			"Stock Level", [24, 7, 60],
		];
		self::expectException(IncorrectTableDataFormat::class);
		self::expectExceptionMessage("Column data \"Price\" is not iterable.");
		$sut->bindKeyValue("tableData", $tableData);
	}

	public function testBindKeyValue_tableData_noTable():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_NO_TABLE);
		$sut = new DocumentBinder($document);
		self::expectException(TableElementNotFoundInContextException::class);
		$sut->bindKeyValue("tableData", []);
	}

	public function testBindKeyValue_tableData_multipleTables():void {
		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);
		$sut->bindKeyValue(
			"tableData",
			$tableData,
			$document->getElementById("multi-table-container")
		);

		$tableList = $document->querySelectorAll("#multi-table-container table");
		self::assertCount(3, $tableList);

		foreach($tableList as $tableIndex => $table) {
			/** @var HTMLTableElement $table */
			if($table->parentElement->id === "s2") {
				continue;
			}

			/** @var HTMLTableSectionElement $tbody */
			$tbody = $table->tBodies[0];
			$tableDataKeys = array_keys($tableData);
			foreach($tbody->rows as $rowIndex => $row) {
				/** @var HTMLTableRowElement $row */
				foreach($row->cells as $cellIndex => $cell) {
					$key = $tableDataKeys[$cellIndex];
					self::assertEquals(
						$tableData[$key][$rowIndex],
						$cell->textContent
					);
				}
			}
		}
	}

	public function testBindTable_multipleTables():void {
		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);
		$sut->bindTable(
			$tableData,
			$document->getElementById("multi-table-container")
		);

		$tableList = $document->querySelectorAll("#multi-table-container table");
		self::assertCount(3, $tableList);

		foreach($tableList as $tableIndex => $table) {
			/** @var HTMLTableElement $table */
			/** @var HTMLTableSectionElement $tbody */
			$tbody = $table->tBodies[0];
			$tableDataKeys = array_keys($tableData);
			foreach($tbody->rows as $rowIndex => $row) {
				/** @var HTMLTableRowElement $row */
				foreach($row->cells as $cellIndex => $cell) {
					$key = $tableDataKeys[$cellIndex];
					self::assertEquals(
						$tableData[$key][$rowIndex],
						$cell->textContent
					);
				}
			}
		}
	}

	public function testBindTable_keyNamesInTHead():void {
		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];


		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl3");
		$sut = new DocumentBinder($document);
		$sut->bindTable(
			$tableData,
			$table
		);

		$tableDataKeys = [];
		foreach($table->rows as $rowIndex => $row) {
			/** @var $row HTMLTableRowElement */
			if($rowIndex === 1) {
				self::assertEquals("Greg", $row->cells[0]->textContent);
				continue;
			}

			foreach($row->cells as $cellIndex => $cell) {
				if($rowIndex === 0) {
					array_push($tableDataKeys, $cell->textContent);
					continue;
				}

				$key = $tableDataKeys[$cellIndex];
				self::assertEquals(
// `$rowIndex - 2` because we're counting the header, and the pre-existing row.
					$tableData[$key][$rowIndex - 2],
					$cell->textContent
				);
			}
		}
	}
}

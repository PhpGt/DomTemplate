<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLElement\HTMLInputElement;
use Gt\Dom\HTMLElement\HTMLTableCellElement;
use Gt\Dom\HTMLElement\HTMLTableElement;
use Gt\Dom\HTMLElement\HTMLTableRowElement;
use Gt\Dom\HTMLElement\HTMLTableSectionElement;
use Gt\DomTemplate\IncorrectTableDataFormat;
use Gt\DomTemplate\TableBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class TableBinderTest extends TestCase {
	/**
	 * Binding table data into an empty table will create all the
	 * appropriate <thead>, <tbody>, <tr>, <th>, and <td> elements.
	 */
	public function testBindTable_emptyTable():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");

		self::assertEmpty($table->innerHTML);
		$sut->bindTableData([
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
	public function testBindTable_existingTHead():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

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
		$sut->bindTableData($tableData, $table);

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

	public function testBindTable_dataNormalised():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl2");

		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];
		$sut->bindTableData(
			$tableData,
			$table
		);

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
	public function testBindTable_doubleHeader_shouldEmitTHElementsInRows():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

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
		$sut->bindTableData(
			$tableData,
			$table
		);

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

	public function testBindTable_nonIterableValue():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

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
		$sut->bindTableData(
			$tableData,
			$table
		);
	}

	public function testBindTable_doubleHeaderNonIterableValue():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

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
		$sut->bindTableData($tableData, $table);
	}

	public function testBindTable_assocArrayWithoutIterableColumns():void {
		$sut = new TableBinder();
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);

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
		$sut->bindTableData($tableData, $table);
	}

	public function testBindTable_multipleTables():void {
		$sut = new TableBinder();
		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut->bindTableData(
			$tableData,
			$document->getElementById("multi-table-container")
		);

		$tableList = $document->querySelectorAll("#multi-table-container table");
		self::assertCount(3, $tableList);

		foreach($tableList as $table) {
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

	public function testBindTable_keyNamesInTHead():void {
		$sut = new TableBinder();
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
		$sut->bindTableData(
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

	public function testBindTableData_documentContext():void {
		$sut = new TableBinder();
		$tableData = [
			"id" => [34, 35, 25],
			"firstName" => ["Derek", "Christoph", "Sara"],
			"lastName" => ["Rethans", "Becker", "Golemon"],
			"username" => ["derek", "cmbecker69", "pollita"],
			"email" => ["derek@php.net", "cmbecker69@php.net", "pollita@php.net"],
		];

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLE_NO_BIND_KEY);
		$sut->bindTableData($tableData, $document);

		self::assertCount(4, $document->querySelectorAll("table tr"));
	}

	public function testBindTableData_emptyHeader():void {
		$sut = new TableBinder();
		$tableData = [
			["ID", "Name", "Code"],
		];
		for($i = 1; $i <= 10; $i++) {
			$name = "Thing $i";
			array_push($tableData, [$i, $name, md5($name)]);
		}

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLE_ID_NAME_CODE);
		$sut->bindTableData($tableData, $document);

		/** @var HTMLTableElement $table */
		$table = $document->querySelector("table");
		/** @var HTMLTableRowElement $theadRow */
		$theadRow = $table->tHead->rows[0];
		self::assertCount(4, $theadRow->cells);
		self::assertSame("Delete", $theadRow->cells[3]->textContent);

		/** @var HTMLTableSectionElement $tbody */
		$tbody = $table->tBodies[0];
		/** @var HTMLTableRowElement $row */
		foreach($tbody->rows as $rowIndex => $row) {
			foreach($row->cells as $cellIndex => $cell) {
				$expected = $tableData[$rowIndex + 1][$cellIndex] ?? "";
				self::assertSame((string)$expected, $cell->textContent);
			}
		}
	}

	public function testBindTableData_existingBodyRow():void {
		$tableData = [
			["id", "code", "name", "deleted"],
		];
// 3, 6 and 9 will be marked as "Deleted".
		for($i = 1; $i <= 10; $i++) {
			$name = "Thing $i";
			array_push($tableData, [$i, md5($name), $name, $i % 3 === 0]);
		}

		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLE_EXISTING_CELLS);
		$sut = new TableBinder();

		$sut->bindTableData($tableData, $document);

		/** @var HTMLTableSectionElement $tbody */
		$tbody = $document->querySelector("table tbody");

		$headers = array_shift($tableData);

		/** @var HTMLTableRowElement $tr */
		foreach($tbody->rows as $rowIndex => $tr) {
			$rowData = array_combine($headers, $tableData[$rowIndex]);

			self::assertSame((string)$rowData["id"], $tr->cells[1]->textContent);
			self::assertSame((string)$rowData["name"], $tr->cells[2]->textContent);
			self::assertSame((string)$rowData["code"], $tr->cells[3]->textContent);

			/** @var HTMLInputElement $input */
			$input = $tr->cells[0]->querySelector("input");
			self::assertSame((string)$rowData["id"], $input->value);

			/** @var HTMLInputElement $input */
			$input = $tr->cells[4]->querySelector("input");
			self::assertSame((string)$rowData["id"], $input->value);

			if(($rowIndex + 1) % 3 === 0) {
				self::assertTrue($tr->cells[0]->classList->contains("deleted"));
			}
			else {
				self::assertFalse($tr->cells[0]->classList->contains("deleted"));
			}
		}
	}
}

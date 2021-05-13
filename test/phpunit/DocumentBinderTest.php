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

	public function testBindKeyValue_tableData_noTable():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_NO_TABLE);
		$sut = new DocumentBinder($document);
		self::expectException(TableElementNotFoundInContextException::class);
		$sut->bindKeyValue("tableData", []);
	}

	public function testBindTable():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		$tableData = [
			["Name", "Position"],
			["Alan Statham", "Head of Radiology"],
			["Sue White", "Staff Liason Officer"],
			["Mac Macartney", "General Surgeon"],
			["Joanna Clore", "HR"],
			["Caroline Todd", "Surgical Registrar"],
		];

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");
		$sut->bindTable($tableData, $table);

		foreach($tableData as $rowIndex => $rowData) {
			/** @var HTMLTableRowElement $row */
			$row = $table->rows[$rowIndex];

			foreach($rowData as $cellIndex => $cellValue) {
				self::assertSame(
					$cellValue,
					$row->cells[$cellIndex]->textContent
				);
			}
		}
	}

	public function testBindKeyValue_tableData():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TABLES);
		$sut = new DocumentBinder($document);

		$tableData = [
			["Name", "Position"],
			["Alan Statham", "Head of Radiology"],
			["Sue White", "Staff Liason Officer"],
			["Mac Macartney", "General Surgeon"],
			["Joanna Clore", "HR"],
			["Caroline Todd", "Surgical Registrar"],
		];

		/** @var HTMLTableElement $table */
		$table = $document->getElementById("tbl1");
		$sut->bindKeyValue("tableData", $tableData, $table);

		foreach($tableData as $rowIndex => $rowData) {
			/** @var HTMLTableRowElement $row */
			$row = $table->rows[$rowIndex];

			foreach($rowData as $cellIndex => $cellValue) {
				self::assertSame(
					$cellValue,
					$row->cells[$cellIndex]->textContent
				);
			}
		}
	}
}

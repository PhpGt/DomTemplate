<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\Document;
use Gt\DomTemplate\DataBinder;
use Gt\DomTemplate\InvalidBindPropertyException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class DataBinderTest extends TestCase {
	/**
	 * If the developer forgets to add a bind property (the bit after the
	 * colon in `data-bind:text`, we should let them know with a friendly
	 * error message.
	 */
	public function testBindValue_missingBindProperty():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_NO_BIND_PROPERTY);
		$sut = new DataBinder($document);
		self::expectException(InvalidBindPropertyException::class);
		self::expectExceptionMessage("OUTPUT Element has a data-bind attribute with missing bind property - did you mean `data-bind:text`?");
		$sut->bindValue("Test!");
	}

	public function testBindValue_singleElement():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_SINGLE_ELEMENT);
		$sut = new DataBinder($document);
		$output = $document->querySelector("output");
		self::assertSame("Nothing is bound", $output->textContent);
		$sut->bindValue("Test!");
		self::assertSame("Test!", $output->textContent);
	}

	public function testBindValue_multipleElements():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MULTIPLE_ELEMENTS);
		$sut = new DataBinder($document);
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
		$sut = new DataBinder($document);
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
		$sut = new DataBinder($document);
		$container3 = $document->getElementById("container3");
		$sut->bindValue("Test!", $container3);
		self::assertSame("Default title", $document->querySelector("#container3 h1")->textContent);
		self::assertSame("Test!", $document->getElementById("o7")->textContent);
	}

	public function testBindValue_invalidPropertyTextContent():void {
		$document = DocumentTestFactory::createHTML();
		$badElement = $document->createElement("example");
		$badElement->setAttribute("data-bind:textContent", "");
		$document->body->appendChild($badElement);
		$sut = new DataBinder($document);
		self::expectException(InvalidBindPropertyException::class);
		self::expectExceptionMessage("Unknown bind property `textContent` on EXAMPLE Element - did you mean `data-bind:text`?");
		$sut->bindValue("Test!");
	}

	public function testBindValue_invalidProperty():void {
		$document = DocumentTestFactory::createHTML();
		$badElement = $document->createElement("example");
		$badElement->setAttribute("data-bind:unknown", "");
		$document->body->appendChild($badElement);
		$sut = new DataBinder($document);
		self::expectException(InvalidBindPropertyException::class);
		self::expectExceptionMessage("Unknown bind property `unknown` on EXAMPLE Element");
		$sut->bindValue("Test!");
	}
}

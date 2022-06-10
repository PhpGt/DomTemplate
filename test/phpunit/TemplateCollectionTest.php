<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\TemplateCollection;
use Gt\DomTemplate\TemplateElementNotFoundInContextException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class TemplateCollectionTest extends TestCase {
	public function testGet_noName_noMatch():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$sut = new TemplateCollection($document);

		self::expectException(TemplateElementNotFoundInContextException::class);
		$sut->get($document->querySelector("ol"));
	}

	public function testGet_noName():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$ul = $document->querySelector("ul");
		$ol = $document->querySelector("ol");
		self::assertCount(1, $ul->children);
		self::assertCount(1, $ol->children);
		$sut = new TemplateCollection($document);
		self::assertCount(0, $ul->children);
		self::assertCount(1, $ol->children);
		$templateElement = $sut->get($document);
		$inserted = $templateElement->insertTemplate();
		self::assertSame("li", $inserted->tagName);
		self::assertSame($ul, $templateElement->getTemplateParent());
		self::assertSame($ul, $inserted->parentElement);
	}

	public function testGet_name_noMatch():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_TWO_LISTS);
		$sut = new TemplateCollection($document);

		self::expectException(TemplateElementNotFoundInContextException::class);
		self::expectExceptionMessage('Template element with name "unknown-list" can not be found within the context html element.');
		$sut->get($document, "unknown-list");
	}

	public function testGet_name():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_TWO_LISTS);
		$sut = new TemplateCollection($document);

		$templateElement = $sut->get($document, "prog-lang");
		self::assertSame(
			$document->getElementById("prog-lang-list"),
			$templateElement->getTemplateParent()
		);
	}

	/**
	 * Baby steps...(this comment was written as part of DomTemplate's TDD).
	 * Instead of jumping into the implementation of recursive nested list
	 * binding, we're going to manually iterate over a data source and
	 * bind the appropriate elements by their explicit template name.
	 *
	 * This is a manually-bound version of
	 * ListBinderTest::testBindListData_nestedList()
	 */
	public function testBindListData_nestedList_manual():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_MUSIC_EXPLICIT_TEMPLATE_NAMES);
		$templateCollection = new TemplateCollection($document);
		$elementBinder = new ElementBinder();

		foreach(TestData::MUSIC as $artistName => $albumList) {
			$artistTemplate = $templateCollection->get($document, "artist");
			$artistElement = $artistTemplate->insertTemplate();
			$elementBinder->bind(null, $artistName, $artistElement);

			foreach($albumList as $albumName => $trackList) {
				$albumTemplate = $templateCollection->get($document, "album");
				$albumElement = $albumTemplate->insertTemplate();
				$elementBinder->bind(null, $albumName, $albumElement);

				foreach($trackList as $trackName) {
					$trackTemplate = $templateCollection->get($document, "track");
					$trackElement = $trackTemplate->insertTemplate();
					$elementBinder->bind(
						null,
						$trackName,
						$trackElement
					);
				}
			}
		}

		$artistNameArray = array_keys(TestData::MUSIC);
		foreach($document->querySelectorAll("body>ul>li") as $i => $artistElement) {
			$artistName = $artistNameArray[$i];
			self::assertEquals(
				$artistName,
				$artistElement->querySelector("h2")->textContent
			);

			$albumNameArray = array_keys(TestData::MUSIC[$artistName]);
			foreach($artistElement->querySelectorAll("ul>li") as $j => $albumElement) {
				$albumName = $albumNameArray[$j];
				self::assertEquals(
					$albumName,
					$albumElement->querySelector("h3")->textContent
				);

				$trackNameArray = TestData::MUSIC[$artistName][$albumName];
				foreach($albumElement->querySelectorAll("ol>li") as $k => $trackElement) {
					$trackName = $trackNameArray[$k];
					self::assertEquals(
						$trackName,
						$trackElement->textContent
					);
				}
			}
		}
	}
}

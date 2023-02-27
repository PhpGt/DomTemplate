<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\ListElementCollection;
use Gt\DomTemplate\ListElementNotFoundInContextException;
use Gt\DomTemplate\Test\TestHelper\HTMLPageContent;
use Gt\DomTemplate\Test\TestHelper\TestData;
use PHPUnit\Framework\TestCase;

class TemplateCollectionTest extends TestCase {
	public function testGet_noName_noMatch():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_LIST_TEMPLATE);
		$sut = new ListElementCollection($document);

		self::expectException(ListElementNotFoundInContextException::class);
		$sut->get($document->querySelector("ol"));
	}

	public function testGet_noName():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_LIST_TEMPLATE);
		$ul = $document->querySelector("ul");
		$ol = $document->querySelector("ol");
		self::assertCount(1, $ul->children);
		self::assertCount(1, $ol->children);
		$sut = new ListElementCollection($document);
		self::assertCount(0, $ul->children);
		self::assertCount(1, $ol->children);
		$templateElement = $sut->get($document);
		$inserted = $templateElement->insertListItem();
		self::assertSame("li", $inserted->tagName);
		self::assertSame($ul, $templateElement->getListItemParent());
		self::assertSame($ul, $inserted->parentElement);
	}

	public function testGet_name_noMatch():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TWO_LISTS);
		$sut = new ListElementCollection($document);

		self::expectException(ListElementNotFoundInContextException::class);
		self::expectExceptionMessage('Template element with name "unknown-list" can not be found within the context html element.');
		$sut->get($document, "unknown-list");
	}

	public function testGet_name():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TWO_LISTS);
		$sut = new ListElementCollection($document);

		$templateElement = $sut->get($document, "prog-lang");
		self::assertSame(
			$document->getElementById("prog-lang-list"),
			$templateElement->getListItemParent()
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
		$document = new HTMLDocument(HTMLPageContent::HTML_MUSIC_EXPLICIT_TEMPLATE_NAMES);
		$templateCollection = new ListElementCollection($document);
		$elementBinder = new ElementBinder();

		foreach(TestData::MUSIC as $artistName => $albumList) {
			$artistTemplate = $templateCollection->get($document, "artist");
			$artistElement = $artistTemplate->insertListItem();
			$elementBinder->bind(null, $artistName, $artistElement);

			foreach($albumList as $albumName => $trackList) {
				$albumTemplate = $templateCollection->get($document, "album");
				$albumElement = $albumTemplate->insertListItem();
				$elementBinder->bind(null, $albumName, $albumElement);

				foreach($trackList as $trackName) {
					$trackTemplate = $templateCollection->get($document, "track");
					$trackElement = $trackTemplate->insertListItem();
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

	public function testConstructor_removesWhitespace():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_LIST_TEMPLATE);
		new ListElementCollection($document);
		self::assertSame("", $document->querySelector("ul")->innerHTML);
	}

	public function testConstructor_nonTemplateChildrenArePreserved():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_LIST_WITH_TEXTNODE);
		new ListElementCollection($document);
		$ulChildren = $document->querySelector("ul")->children;
		self::assertCount(1, $ulChildren);
		self::assertSame("This list item will always show at the end", $ulChildren[0]->textContent);
	}

	public function testConstructor_nonTemplateChildrenArePreservedInOrder():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_LIST_WITH_TEXTNODE);
		$sut = new ListElementCollection($document);
		$ulChildren = $document->querySelector("ul")->children;
		$template = $sut->get($document);
		$template->insertListItem();
		$template->insertListItem();
		$template->insertListItem();
		self::assertCount(4, $ulChildren);
		self::assertSame("This list item will always show at the end", $ulChildren[3]->textContent);
	}
}

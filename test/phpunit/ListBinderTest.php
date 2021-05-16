<?php
namespace Gt\DomTemplate\Test;

use ArrayIterator;
use Gt\Dom\HTMLElement\HTMLLiElement;
use Gt\DomTemplate\Bind;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\TableElementNotFoundInContextException;
use Gt\DomTemplate\TemplateCollection;
use Gt\DomTemplate\TemplateElement;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class ListBinderTest extends TestCase {
	public function testBindList_emptyList():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);

		$templateCollection = new TemplateCollection($document);
		$sut = new ListBinder($templateCollection);
		$boundCount = $sut->bindListData(
			[],
			$document
		);
		self::assertSame(0, $boundCount);
	}

	public function testBindList_emptyList_iterator():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);

		$templateCollection = self::createMock(TemplateCollection::class);

		$sut = new ListBinder($templateCollection);
		$boundCount = $sut->bindListData(
			new ArrayIterator([]),
			$document
		);
		self::assertSame(0, $boundCount);
	}

	public function testBindList_noMatchingTemplate():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->expects(self::once())
			->method("get")
			->with($document->documentElement, "missing")
			->willReturnCallback(function() {
				throw new TableElementNotFoundInContextException();
			});

		$sut = new ListBinder($templateCollection);
		self::expectException(TableElementNotFoundInContextException::class);
		$sut->bindListData(
			["one", "two", "three"],
			$document,
			"missing"
		);
	}

	public function testBindList_simpleList():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$templateElement = new TemplateElement($document->querySelector("li[data-template]"));

		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->expects(self::once())
			->method("get")
			->with($document->documentElement, null)
			->willReturn($templateElement);

		$ul = $document->querySelector("ul");
		self::assertCount(
			0,
			$ul->children,
			"There should be no LI elements in the UL at the start of the test"
		);

		$testData = ["one", "two", "three"];
		$sut = new ListBinder($templateCollection);
		$boundCount = $sut->bindListData(
			$testData,
			$document
		);

		self::assertSame(count($testData), $boundCount);
		self::assertCount(
			$boundCount,
			$ul->children,
			"The correct number of LI elements should have been inserted into the UL"
		);

		foreach($testData as $i => $value) {
			self::assertSame($value, $ul->children[$i]->textContent);
		}
	}

	/**
	 * This tests what happens when the context element has more than one
	 * element with a data-template attribute. In this test, we expect the
	 * two template elements to have different template names.
	 */
	public function testBindListData_twoLists():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TWO_LISTS);
		$templateElementProgLang = new TemplateElement(
			$document->querySelector("#favourites li[data-template='prog-lang']")
		);
		$templateElementGame = new TemplateElement(
			$document->querySelector("#favourites li[data-template='game']")
		);

		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->expects(self::exactly(2))
			->method("get")
			->withConsecutive(
				[$document->documentElement, "prog-lang"],
				[$document->documentElement, "game"]
			)
			->willReturnOnConsecutiveCalls($templateElementProgLang, $templateElementGame);

		$sut = new ListBinder($templateCollection);
		$progLangData = ["PHP", "HTML", "bash"];
		$sut->bindListData($progLangData, $document, "prog-lang");
		$gameData = ["Pac Man", "Mega Man", "Tetris"];
		$sut->bindListData($gameData, $document, "game");

		foreach($progLangData as $i => $progLang) {
			self::assertSame($progLang, $document->querySelectorAll("#prog-lang-list li")[$i]->textContent);
		}

		foreach($gameData as $i => $game) {
			self::assertSame($game, $document->querySelectorAll("#game-list li")[$i]->textContent);
		}
	}

	public function testBindListData_empty_parentShouldBeEmpty():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$templateElement = new TemplateElement($document->querySelector("li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData([], $document);

		self::assertSame("", $document->querySelector("ul")->innerHTML);
	}

	public function testBindListData_kvpList_array():void {
		$kvpList = [
			["userId" => 543, "username" => "win95", "orderCount" => 55],
			["userId" => 559, "username" => "seafoam", "orderCount" => 30],
			["userId" => 274, "username" => "hammatime", "orderCount" => 23],
		];
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_ORDER_LIST);
		$orderList = $document->querySelector("ul");

		$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData($kvpList, $orderList);

		foreach($orderList->children as $i => $li) {
			/** @var HTMLLiElement $li */
			self::assertEquals($kvpList[$i]["userId"], $li->querySelector("h3 span")->textContent);
			self::assertEquals($kvpList[$i]["username"], $li->querySelector("h2 span")->textContent);
			self::assertEquals($kvpList[$i]["orderCount"], $li->querySelector("p span")->textContent);
		}
	}

	public function testBindListData_kvpList_object():void {
		$kvpList = [
			(object)["userId" => 543, "username" => "win95", "orderCount" => 55],
			(object)["userId" => 559, "username" => "seafoam", "orderCount" => 30],
			(object)["userId" => 274, "username" => "hammatime", "orderCount" => 23],
		];
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_ORDER_LIST);
		$orderList = $document->querySelector("ul");

		$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData($kvpList, $orderList);

		foreach($orderList->children as $i => $li) {
			/** @var HTMLLiElement $li */
			self::assertEquals($kvpList[$i]->{"userId"}, $li->querySelector("h3 span")->textContent);
			self::assertEquals($kvpList[$i]->{"username"}, $li->querySelector("h2 span")->textContent);
			self::assertEquals($kvpList[$i]->{"orderCount"}, $li->querySelector("p span")->textContent);
		}
	}

	public function testBindListData_kvpList_instanceObject():void {
		$kvpList = [
			new class { public int $userId = 543; public string $username = "win95"; public int $orderCount = 55; },
			new class { public int $userId = 559; public string $username = "seafoam"; public int $orderCount = 30; },
			new class { public int $userId = 274; public string $username = "hammatime"; public int $orderCount = 23; },
		];
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_ORDER_LIST);
		$orderList = $document->querySelector("ul");

		$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData($kvpList, $orderList);

		foreach($orderList->children as $i => $li) {
			/** @var HTMLLiElement $li */
			self::assertEquals($kvpList[$i]->{"userId"}, $li->querySelector("h3 span")->textContent);
			self::assertEquals($kvpList[$i]->{"username"}, $li->querySelector("h2 span")->textContent);
			self::assertEquals($kvpList[$i]->{"orderCount"}, $li->querySelector("p span")->textContent);
		}
	}

	public function testBindListData_kvpList_instanceObjectWithBindAttributeMethods():void {
		$kvpList = [
			new class {
				#[Bind("userId")]
				public function getId():int {
					return 534;
				}
				#[Bind("username")]
				public function getUsername():string {
					return "win95";
				}
				#[Bind("this-matches-nothing")]
				public function getNothing():string {
					return "nothing!";
				}
				#[Bind("orderCount")]
				public function getTotalOrders():int {
					return 55;
				}
			},
			new class {
				#[Bind("userId")]
				public function getId():int {
					return 559;
				}
				#[Bind("username")]
				public function getUsername():string {
					return "seafoam";
				}
				#[Bind("orderCount")]
				public function getTotalOrders():int {
					return 30;
				}
			},
			new class {
				#[Bind("userId")]
				public function getId():int {
					return 274;
				}
				#[Bind("username")]
				public function getUsername():string {
					return "hammatime";
				}
				#[Bind("orderCount")]
				public function getTotalOrders():int {
					return 23;
				}
			},
		];
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_ORDER_LIST);
		$orderList = $document->querySelector("ul");

		$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData($kvpList, $orderList);

		foreach($orderList->children as $i => $li) {
			/** @var HTMLLiElement $li */
			self::assertEquals($kvpList[$i]->getId(), $li->querySelector("h3 span")->textContent);
			self::assertEquals($kvpList[$i]->getUsername(), $li->querySelector("h2 span")->textContent);
			self::assertEquals($kvpList[$i]->getTotalOrders(), $li->querySelector("p span")->textContent);
		}
	}

	public function testBindListData_kvpList_instanceObjectWithBindAttributeProperties():void {
		$kvpList = [
			new class {
				#[Bind("userId")]
				public int $id = 534;

				#[Bind("username")]
				public string $user = "win95";

				#[Bind("this-matches-nothing")]
				public string $nothing = "nothing!";

				#[Bind("orderCount")]
				public int $totalOrders = 55;
			},
			new class {
				#[Bind("userId")]
				public int $id = 559;

				#[Bind("username")]
				public string $user = "seafoam";

				#[Bind("orderCount")]
				public int $totalOrders = 30;
			},
			new class {
				#[Bind("userId")]
				public int $id = 274;

				#[Bind("username")]
				public string $user = "hammatime";

				#[Bind("orderCount")]
				public int $totalOrders = 23;
			},
		];
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_USER_ORDER_LIST);
		$orderList = $document->querySelector("ul");

		$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->method("get")
			->willReturn($templateElement);

		$sut = new ListBinder($templateCollection);
		$sut->bindListData($kvpList, $orderList);

		foreach($orderList->children as $i => $li) {
			/** @var HTMLLiElement $li */
			self::assertEquals($kvpList[$i]->id, $li->querySelector("h3 span")->textContent);
			self::assertEquals($kvpList[$i]->user, $li->querySelector("h2 span")->textContent);
			self::assertEquals($kvpList[$i]->totalOrders, $li->querySelector("p span")->textContent);
		}
	}

	public function testBindListData_nestedList():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_MUSIC_NO_TEMPLATE_NAMES);
		$templateCollection = new TemplateCollection($document);
		$sut = new ListBinder($templateCollection);
		$sut->bindListData(TestData::MUSIC, $document);

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

// TODO: Test <ul> <li data-template="good">Good item</li> <li data-template="bad">Bad item</li> </ul>
// The next sibling of "good" will not exist any more - so if there's a data-template tag on the next sibling, go to the next next sibling.
}

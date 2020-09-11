<?php /** @noinspection PhpComposerExtensionStubsInspection */
namespace Gt\DomTemplate\Test;

use EmptyIterator;
use Gt\DomTemplate\IncompatibleBindDataException;
use Gt\DomTemplate\BoundAttributeDoesNotExistException;
use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\NamelessTemplateSpecificityException;
use Gt\DomTemplate\Test\Helper\BindDataGetter\TodoItem;
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

	public function testInjectAttributePlaceholderNoDataBindAttributes() {
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

	public function testBindAttributesPlaceholder() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$username = "thoughtpolice";
		$link = $document->querySelector("a");
		$img = $document->querySelector("img");
		$link->setAttribute("data-bind-attributes", true);
		$img->setAttribute("data-bind-attributes", true);

		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("username", $username);

		self::assertEquals("/user/101", $link->href);
		self::assertEquals("/img/profile/$userId.jpg", $img->src);
		self::assertEquals("thoughtpolice's profile picture", $img->alt);
	}

	public function testBindAttributesMultiple() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$userType = "thinkpol";
		$h1 = $document->querySelector("h1");
		$h1->setAttribute("data-bind-attributes", true);
		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("userType", $userType);

		self::assertEquals("heading-thinkpol-101", $h1->id);
	}

	public function testBindAttributesMultipleInHref() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS_NO_BIND);
		$userId = 101;
		$userType = "thinkpol";
		$footer = $document->querySelector("footer");

		$link = $footer->querySelector("a");
		$link->setAttribute("data-bind-attributes", true);
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
			(object)["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			(object)["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			(object)["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			(object)["ministry-name" => "Peace", "ministry-id" => 123],
			(object)["ministry-name" => "Plenty", "ministry-id" => 511],
			(object)["ministry-name" => "Truth", "ministry-id" => 141],
			(object)["ministry-name" => "Love", "ministry-id" => 610],
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

		self::assertEquals(
			$stateList[1]->{"state-name"},
			$firstList->querySelectorAll("li")[1]->innerText
		);
		self::assertEquals(
			$ministryList[2]->{"ministry-name"},
			$secondList->querySelectorAll("li")[2]->innerText
		);
	}

	public function testBindListMultipleDataTemplateElements() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_NAMELESS_BIND_LIST);
		$document->extractTemplates();
		$stateList = [
			(object)["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			(object)["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			(object)["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			(object)["ministry-name" => "Peace", "ministry-id" => 123],
			(object)["ministry-name" => "Plenty", "ministry-id" => 511],
			(object)["ministry-name" => "Truth", "ministry-id" => 141],
			(object)["ministry-name" => "Love", "ministry-id" => 610],
		];

		$firstList = $document->getElementById("list-1");
		$secondList = $document->getElementById("list-2");

		$secondList->bindList($ministryList);
		$firstList->bindList($stateList);

		self::assertCount(count($stateList), $firstList->children);
		self::assertCount(count($ministryList), $secondList->children);

		self::assertEquals(
			$stateList[1]->{"state-name"},
			$firstList->querySelectorAll("li")[1]->innerText
		);
		self::assertEquals(
			$ministryList[2]->{"ministry-name"},
			$secondList->querySelectorAll("li")[2]->innerText
		);
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
			$row = (object)[
				"text" => $formatter->format($i),
				"value" => $i,
				"isDisabled" => (bool)($i % 2),
			];

			if($i === 5) {
				$row->{"isDisabled"} = true;
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

	public function testBindValueManualTemplate() {
		$employeeData = [
			"Alan Statham" => (object)[
				"id" => 1742,
				"title" => "Consultant Radiologist",
			],
			"Caroline Todd" => (object)[
				"id" => 3010,
				"title" => "Surgical Registrar",
			],
			"Guy Secretan" => (object)[
				"id" => 2019,
				"title" => "Anaesthetist",
			],
			"Karen Ball" => (object)[
				"id" => 836,
				"title" => "Human Resources",
			],
		];

		$document = new HTMLDocument(Helper::HTML_KEYLESS_BIND_ATTRIBUTE_TEMPLATE_NAMED);
		$document->extractTemplates();

		foreach($employeeData as $name => $data) {
			$t = $document->getTemplate("employee-template");
			$t->bindValue($name);
			$t->insertTemplate();
		}

		self::assertCount(
			count($employeeData),
			$document->querySelector("ul")->children
		);

		$i = 0;
		foreach($employeeData as $name => $data) {
			$element = $document->querySelector("ul")->children[$i];
			self::assertEquals($name, $element->querySelector("h1")->innerText);
			$i++;
		}
	}

	public function testBindListStringKeys() {
		$employeeData = [
			"Alan Statham" => (object)[
				"id" => 1742,
				"title" => "Consultant Radiologist",
			],
			"Caroline Todd" => (object)[
				"id" => 3010,
				"title" => "Surgical Registrar",
			],
			"Guy Secretan" => (object)[
				"id" => 2019,
				"title" => "Anaesthetist",
			],
			"Karen Ball" => (object)[
				"id" => 836,
				"title" => "Human Resources",
			],
		];

		$document = new HTMLDocument(Helper::HTML_KEYLESS_BIND_ATTRIBUTE_TEMPLATE);
		$document->extractTemplates();
		$document->bindList($employeeData);

		$empListElement = $document->getElementById("emp-list");
		self::assertCount(count($employeeData), $empListElement->children);

		$i = 0;
		foreach($employeeData as $name => $data) {
			$empElement = $empListElement->children[$i];
			$h1 = $empElement->querySelector("h1");
			self::assertEquals($name, $h1->textContent);
			$i++;
		}
	}

	public function testBindNestedList() {
		$document = new HTMLDocument(Helper::HTML_MUSIC);
		$document->extractTemplates();
		$document->bindNestedList(Helper::LIST_MUSIC);

		$ulArtists = $document->querySelector(".artist-list");

		self::assertCount(
			count(Helper::LIST_MUSIC),
			$ulArtists->children
		);

		foreach(Helper::LIST_MUSIC as $artistName => $albumList) {
			$liArtist = $ulArtists->querySelector("li[data-artist-name=$artistName]");
			$h2ArtistName = $liArtist->querySelector("h2");
			self::assertEquals($artistName, trim($h2ArtistName->innerText));

			$ulAlbums = $liArtist->querySelector(".album-list");
			foreach($albumList as $albumName => $trackList) {
				$liAlbum = $ulAlbums->querySelector("li[data-album-name=$albumName]");
				$h3AlbumName = $liAlbum->querySelector("h3");
				self::assertEquals($albumName, trim($h3AlbumName->innerText));

				$olTracks = $liAlbum->querySelector(".track-list");
				foreach($trackList as $i => $trackName) {
					$trackLi = $olTracks->children[$i];
					self::assertEquals($trackName, trim($trackLi->innerText));
				}
			}
		}
	}

	public function testBindNestedListWithinObject() {
		$document = new HTMLDocument(Helper::HTML_SHOP);
		$document->extractTemplates();
		$data = Helper::LIST_SHOP;
		foreach($data as $key => $value) {
			$data[$key] = (object)$value;
		}
		$document->bindNestedList($data);

		$ulProductList = $document->querySelector(".product-list");
		self::assertCount(
			count($data),
			$ulProductList->children
		);

		$index = 0;
		foreach($data as $itemName => $itemData) {
			$liProduct = $ulProductList->children[$index];

			self::assertEquals(
				$itemName,
				$liProduct->querySelector("h2")->innerText
			);
			self::assertEquals(
				$itemData->{"description"},
				$liProduct->querySelector("p")->innerText
			);
			self::assertEquals(
				$itemData->{"price"},
				$liProduct->querySelector(".price")->innerText
			);

			$ulCategories = $liProduct->querySelector("ul.categories");
			self::assertCount(
				count($itemData->{"categories"}),
				$ulCategories->children
			);

			foreach($itemData->{"categories"}
			as $categoryIndex => $categoryName) {
				$liCategory = $ulCategories->children[$categoryIndex];
				self::assertEquals($categoryName, trim($liCategory->innerText));

				$link = $liCategory->querySelector("a");
				self::assertEquals($categoryName, trim($link->innerText));
				self::assertEquals("/shop/category/$categoryName", $link->href);
			}

			$index++;
		}
	}

	public function testTemplateNameIsAddedWhenNamed() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();

		foreach(["one", "two", "three"] as $num) {
			$t = $document->getTemplate("list-item");
			$t->innerHTML = $num;
			$inserted = $t->insertTemplate();

			self::assertStringContainsString("t-list-item", $inserted->getAttribute("class"));
		}
	}

	public function testTemplateNameIsNotAddedWhenNotNamed() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST_INLINE_NAMED_TEMPLATE_DOUBLE);
		$document->extractTemplates();
		$unnamedTemplateContainer = $document->getElementById("todo-list-2");
		$unnamedTemplateContainer->bindList([1, 2, 3]);
		self::assertCount(3, $unnamedTemplateContainer->children);
	}

	public function testBindKeyValueBool() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();
		$todoIncomplete = new TodoItem(1, "Example not complete", false);
		$todoComplete = new TodoItem(2, "Example complete", true);

		$document->bindList([
			$todoIncomplete,
			$todoComplete,
		]);

		$listItems = $document->querySelectorAll("li");
		self::assertFalse($listItems[0]->classList->contains("completed"));
		self::assertTrue($listItems[1]->classList->contains("completed"));
	}

	public function testBindListEmptySetsInnerHtmlToEmpty() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();
		$ul = $document->getElementById("todo-list");
		$ul->bindList([]);
		self::assertEquals("", $ul->innerHTML);
	}

	public function testBindKvpList() {
		$kvpData = [
			"Name" => "Alan",
			"Occupation" => "Cryptanalyst",
			"Place of work" => "Bletchley Park",
		];

		$document = new HTMLDocument(Helper::HTML_KVP_LIST);
		$document->extractTemplates();
		$ul = $document->getElementById("list");
		$ul->bindList($kvpData);

		$expectedKeys = array_keys($kvpData);
		$expectedValues = array_values($kvpData);
		foreach($ul->querySelectorAll("li") as $i => $li) {
			$keySpan = $li->querySelector(".key span");
			$valueSpan = $li->querySelector(".value span");

			self::assertEquals($expectedKeys[$i], $keySpan->textContent);
			self::assertEquals($expectedValues[$i], $valueSpan->textContent);
		}
	}
}
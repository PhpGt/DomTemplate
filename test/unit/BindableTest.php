<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\BoundAttributeDoesNotExistException;
use Gt\DomTemplate\BoundDataNotSetException;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\NamelessTemplateSpecificityException;
use Gt\DomTemplate\Test\Helper\Helper;

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
			method_exists($document, "bindNestedList"),
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
		self::assertTrue(
			method_exists($outputTo, "bindNestedList"),
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
		self::assertTrue(
			method_exists($template, "bindNestedList"),
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

	public function testInjectAttributePlaceholderNoDataBindParameters() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS);
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

	public function testInjectAttributePlaceholder() {
		$document = new HTMLDocument(Helper::HTML_ATTRIBUTE_PLACEHOLDERS);
		$userId = 101;
		$username = "thoughtpolice";
		$link = $document->querySelector("a");
		$img = $document->querySelector("img");
		$link->setAttribute("data-bind-parameters", true);
		$img->setAttribute("data-bind-parameters", true);

		$document->bindKeyValue("userId", $userId);
		$document->bindKeyValue("username", $username);

		self::assertEquals("/user/101", $link->href);
		self::assertEquals("/img/profile/$userId.jpg", $img->src);
		self::assertEquals("thoughtpolice's profile picture", $img->alt);
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
			["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			["ministry-name" => "Peace", "ministry-id" => 123],
			["ministry-name" => "Plenty", "ministry-id" => 511],
			["ministry-name" => "Truth", "ministry-id" => 141],
			["ministry-name" => "Love", "ministry-id" => 610],
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

		self::assertEquals($stateList[1]["state-name"], $firstList->querySelectorAll("li")[1]->innerText);
		self::assertEquals($ministryList[2]["ministry-name"], $secondList->querySelectorAll("li")[2]->innerText);
	}

	public function testBindListMultipleDataTemplateElements() {
		$document = new HTMLDocument(Helper::HTML_DOUBLE_NAMELESS_BIND_LIST);
		$document->extractTemplates();
		$stateList = [
			["state-name" => "Oceania", "ideology" => "Ingsoc", "main-territory" => "Western Hemisphere"],
			["state-name" => "Eurasia", "ideology" => "Neo-Bolshevism", "main-territory" => "Continental Europe"],
			["state-name" => "Eastasia", "ideology" => "Death Worship", "main-territory" => "China"],
		];
		$ministryList = [
			["ministry-name" => "Peace", "ministry-id" => 123],
			["ministry-name" => "Plenty", "ministry-id" => 511],
			["ministry-name" => "Truth", "ministry-id" => 141],
			["ministry-name" => "Love", "ministry-id" => 610],
		];

		$firstList = $document->getElementById("list-1");
		$secondList = $document->getElementById("list-2");

		$secondList->bindList($ministryList);
		$firstList->bindList($stateList);

		self::assertCount(count($stateList), $firstList->children);
		self::assertCount(count($ministryList), $secondList->children);

		self::assertEquals($stateList[1]["state-name"], $firstList->querySelectorAll("li")[1]->innerText);
		self::assertEquals($ministryList[2]["ministry-name"], $secondList->querySelectorAll("li")[2]->innerText);
	}

	public function testBindNestedList() {
		$document = new HTMLDocument(Helper::HTML_MUSIC);
		$document->extractTemplates();
		$document->bindNestedList(Helper::LIST_MUSIC);

		foreach(Helper::LIST_MUSIC as $artistName => $albumList) {
			$domArtist = $document->querySelector("[data-artist-name='$artistName']");
			$h2 = $domArtist->querySelector("h2");
			self::assertEquals($artistName, $h2->innerText);

			foreach($albumList as $albumName => $trackList) {
				$domAlbum = $domArtist->querySelector("[data-album-name='$albumName']");
				$h3 = $domAlbum->querySelector("h3");
				self::assertEquals($albumName, $h3->innerText);

				foreach($trackList as $i => $trackName) {
					$domTrack = $domAlbum->querySelector("[data-track-name='$trackName']");
					self::assertStringContainsString($trackName, $domTrack->innerText);
					$child = $domAlbum->querySelector("ol")->children[$i];
					self::assertSame($domTrack, $child);
				}
			}
		}
	}

	public function testBindNestedListWithBadData() {
		$document = new HTMLDocument(Helper::HTML_MUSIC);
		$document->extractTemplates();
		$data = Helper::LIST_MUSIC;
		$data["Bongo and The Bronks"] = 123;
		$document->bindNestedList($data);

		unset($data["Bongo and The Bronks"]);

		foreach($data as $artistName => $albumList) {
			$domArtist = $document->querySelector("[data-artist-name='$artistName']");
			$h2 = $domArtist->querySelector("h2");
			self::assertEquals($artistName, $h2->innerText);

			foreach($albumList as $albumName => $trackList) {
				$domAlbum = $domArtist->querySelector("[data-album-name='$albumName']");
				$h3 = $domAlbum->querySelector("h3");
				self::assertEquals($albumName, $h3->innerText);

				foreach($trackList as $i => $trackName) {
					$domTrack = $domAlbum->querySelector("[data-track-name='$trackName']");
					self::assertStringContainsString($trackName, $domTrack->innerText);
					$child = $domAlbum->querySelector("ol")->children[$i];
					self::assertSame($domTrack, $child);
				}
			}
		}
	}
}
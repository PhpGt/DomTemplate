<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\BindObject;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\BindDataGetter\TodoItem;
use Gt\DomTemplate\Test\Helper\Helper;
use PHPUnit\Framework\TestCase;

class BindDataGetterTest extends TestCase {
	public function testBindFunction() {
		$id = rand(100, 1000);
		$name = uniqid();
		$sut = new TodoItem($id, $name);

		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();
		$document->bindList([$sut]);

		$li = $document->querySelector("li");
		self::assertEquals($id, $li->querySelector("[name='id']")->value);
		self::assertEquals($name, $li->querySelector("[name='title']")->value);
	}
}
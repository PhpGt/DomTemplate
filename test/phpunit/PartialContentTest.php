<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\PartialContent;
use Gt\DomTemplate\PartialContentDirectoryNotFoundException;
use Gt\DomTemplate\PartialContentFileNotFoundException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class PartialContentTest extends TestCase {
	private string $baseDir;

	protected function setUp():void {
		$this->baseDir = tempnam(sys_get_temp_dir(), "phpgt-domtemplate-test");
		$this->removeTempDir();
		mkdir($this->baseDir, 0775, true);
	}

	protected function tearDown():void {
		$this->removeTempDir();
	}

	public function testConstruct_throwsIfDirectoryNotExist():void {
		$dir = $this->baseDir . "/" . uniqid("random-");
		self::expectException(PartialContentDirectoryNotFoundException::class);
		self::expectExceptionMessage("The partial content path does not exist: $dir");
		new PartialContent($dir);
	}

	public function testGetContent_notExists():void {
		$dir = $this->baseDir . "/" . uniqid("_partial");
		mkdir($dir);
		$sut = new PartialContent($dir);
		self::expectException(PartialContentFileNotFoundException::class);
		self::expectExceptionMessage("The partial content file does not exist: $dir/nothing.html");
		$sut->getContent("nothing");
	}

	public function testGetContent():void {
		$expectedContent = "Test file contents";
		$dir = $this->baseDir . "/" . uniqid("_partial");
		mkdir($dir);
		file_put_contents("$dir/test.html", $expectedContent);
		$sut = new PartialContent($dir);
		self::assertSame(
			$expectedContent,
			$sut->getContent("test")
		);
	}

	public function testGetHTMLDocument():void {
		$expectedContent = "<!doctype html><h1>Test file contents</h1>";
		$dir = $this->baseDir . "/" . uniqid("_partial");
		mkdir($dir);
		file_put_contents("$dir/test.html", $expectedContent);
		$sut = new PartialContent($dir);
		$document = $sut->getHTMLDocument("test");
		self::assertSame(
			"Test file contents",
			$document->querySelector("h1")->textContent
		);
	}

	protected function removeTempDir():void {
		exec("rm -rf " . $this->baseDir);
	}
}

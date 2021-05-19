<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentDirectoryNotFoundException;
use Gt\DomTemplate\ModularContentFileNotFoundException;
use PHPUnit\Framework\TestCase;

class ModularContentTest extends TestCase {
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
		self::expectException(ModularContentDirectoryNotFoundException::class);
		self::expectExceptionMessage("The modular content path does not exist: $dir");
		new ModularContent($dir);
	}

	public function testGetContent_notExists():void {
		$dir = $this->baseDir . "/" . uniqid("_partial");
		mkdir($dir);
		$sut = new ModularContent($dir);
		self::expectException(ModularContentFileNotFoundException::class);
		self::expectExceptionMessage("The modular content file does not exist: $dir/nothing.html");
		$sut->getContent("nothing");
	}

	public function testGetContent():void {
		$expectedContent = "Test file contents";
		$dir = $this->baseDir . "/" . uniqid("_partial");
		mkdir($dir);
		file_put_contents("$dir/test.html", $expectedContent);
		$sut = new ModularContent($dir);
		self::assertSame(
			$expectedContent,
			$sut->getContent("test")
		);
	}

	protected function removeTempDir():void {
		exec("rm -rf " . $this->baseDir);
	}
}

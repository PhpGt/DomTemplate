<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentFileNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModularContentTestCase extends TestCase {
	/**
	 * @param array<string, string> $contentFiles Associative array where
	 * the key is the modular content's name, and the value is its content.
	 */
	protected function mockModularContent(
		string $dirName,
		array $contentFiles = []
	):MockObject|ModularContent {
		$mock = self::createMock(ModularContent::class);
		$mock->method("getContent")
			->willReturnCallback(
				function(string $name) use($contentFiles) {
					$content = $contentFiles[$name] ?? null;
					if(is_null($content)) {
						throw new ModularContentFileNotFoundException();
					}

					return $content;
				}
			);
		$mock->method("getHTMLDocument")
			->willReturnCallback(
				function(string $name) use($contentFiles) {
					$content = $contentFiles[$name] ?? null;
					if(is_null($content)) {
						throw new ModularContentFileNotFoundException();
					}

					return new HTMLDocument($content);
				}
			);
		return $mock;
	}
}

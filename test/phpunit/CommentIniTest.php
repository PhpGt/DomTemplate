<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\CommentIni;
use Gt\DomTemplate\CommentIniInvalidDocumentLocationException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class CommentIniTest extends TestCase {
	public function testConstruct_nullWhenNoCommentBlock():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_USER_PROFILE);
		$sut = new CommentIni($document);
		self::assertFalse($sut->containsIniData());
	}

	public function testConstruct_throwsIfCommentBlockNotFirst():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_INCORRECTLY_EXTENDS_PARTIAL_VIEW);
		self::expectException(CommentIniInvalidDocumentLocationException::class);
		self::expectExceptionMessage("A Comment INI must only appear as the first node of the HTML.");
		new CommentIni($document);
	}

	public function testContainsIniData_emptyIfNoIniData():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_COMMENT_WITHOUT_INI_DATA_PARTIAL_VIEW);
		$sut = new CommentIni($document);
		self::assertFalse($sut->containsIniData());
	}

	public function testGet_noMatchingData():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);
		$sut = new CommentIni($document);
		self::assertNull($sut->get("no-match"));
	}

	public function testGet():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);
		$sut = new CommentIni($document);
		self::assertEquals("base-page", $sut->get("extends"));
	}

	public function testGetNested():void {
		$document = new HTMLDocument(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);
		$sut = new CommentIni($document);
		self::assertEquals("My website, extended...", $sut->get("vars.title"));
	}
}

<?php
namespace Gt\DomTemplate\Test;

use PHPUnit\Framework\TestCase;

class TestTest extends TestCase {
	public function testTrueNotFalse() {
		self::assertNotFalse(true);
	}
}
<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindableCache;
use PHPUnit\Framework\TestCase;
use stdClass;

class BindableCacheTest extends TestCase {
	public function testIsBindable_nonBindableCached():void {
		$obj = new StdClass();
		$sut = new BindableCache();
		self::assertFalse($sut->isBindable($obj));
		self::assertFalse($sut->isBindable($obj));
	}

	public function testIsBindable_bindableCached():void {
		$obj1 = new class extends StdClass {
			#[Bind("name")]
			public function getName():string {
				return "Test 1";
			}
		};

		$obj2 = new class extends StdClass {
			#[Bind("name")]
			public function getName():string {
				return "Test 2";
			}
		};

		$sut = new BindableCache();
		self::assertTrue($sut->isBindable($obj1));
		self::assertTrue($sut->isBindable($obj1));
		self::assertTrue($sut->isBindable($obj2));
	}
}

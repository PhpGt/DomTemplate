<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\Bind;
use Gt\DomTemplate\BindGetter;
use Gt\DomTemplate\BindableCache;
use Gt\DomTemplate\BindGetterMethodDoesNotStartWithGetException;
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

	public function testConvertToKvp_getter():void {
		$obj = new class {
			#[BindGetter]
			public function getName():string {
				return "Test Name";
			}
		};

		$sut = new BindableCache();
		$kvp = $sut->convertToKvp($obj);
		self::assertEquals("Test Name", $kvp["name"]);
	}

	public function testConvertToKvp_getterDoesNotStartWithGet():void {
		$obj = new class {
			#[BindGetter]
			public function retrieveName():string {
				return "Test Name";
			}
		};

		$sut = new BindableCache();
		self::expectException(BindGetterMethodDoesNotStartWithGetException::class);
		self::expectExceptionMessage("Method retrieveName has the BindGetter Attribute, but its name doesn't start with \"get\".");
		$sut->convertToKvp($obj);
	}

	public function testConvertToKvp_notBindable():void {
		$obj = new class {
			public function getName():string {
				return "Test";
			}
		};

		$sut = new BindableCache();
		self::assertSame([], $sut->convertToKvp($obj));
	}

	public function testConvertToKvp_publicReadOnly():void {
		$obj = new class {
			public readonly string $id;
			public readonly string $name;
			public readonly int $age;

			public function __construct() {
				$this->id = "test-id";
				$this->name = "test-name";
			}
		};

		$sut = new BindableCache();
		self::assertSame([
			"id" => "test-id",
			"name" => "test-name",
		], $sut->convertToKvp($obj));
	}
}

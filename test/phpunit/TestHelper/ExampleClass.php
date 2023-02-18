<?php
namespace Gt\DomTemplate\Test\TestHelper;

class ExampleClass {
	public function __construct(
		public readonly int $userId,
		public readonly string $username,
		public readonly int $orderCount,
	) {}
}

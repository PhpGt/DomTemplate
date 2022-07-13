<?php
namespace Gt\DomTemplate\Test\TestFactory;

class ExampleClass {
	public function __construct(
		public readonly int $userId,
		public readonly string $username,
		public readonly int $orderCount,
	) {}
}

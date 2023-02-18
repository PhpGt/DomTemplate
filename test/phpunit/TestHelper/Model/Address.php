<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

class Address {
	public function __construct(
		public readonly string $street,
		public readonly string $line2,
		public readonly string $cityState,
		public readonly string $postcodeZip,
		public readonly string $country,
	) {}
}

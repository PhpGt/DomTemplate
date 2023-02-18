<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

use Gt\DomTemplate\BindGetter;

class Address {
	public function __construct(
		public readonly string $street,
		public readonly string $line2,
		public readonly string $cityState,
		public readonly string $postcodeZip,
		public readonly string $country,
	) {}

	#[BindGetter]
	public function getCountryName():string {
		return match($this->country) {
			"RU" => "Russia",
			"CA" => "Canada",
			"CN" => "China",
			"US" => "United States",
			"BR" => "Brazil",
			"AU" => "Australia",
			"IN" => "India",
			"AR" => "Argentina",
			"KZ" => "Kazakhstan",
			"DZ" => "Algeria",
			default => "Unknown",
		};
	}
}

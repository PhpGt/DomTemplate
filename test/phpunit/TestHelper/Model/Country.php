<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

use Gt\DomTemplate\BindGetter;

class Country {
	public function __construct(
		public readonly string $code,
	) {}

	#[BindGetter]
	public function getName():string {
		return match($this->code) {
			"US" => "United States",
			"CN" => "China",
			"JP" => "Japan",
			"DE" => "Germany",
			"GB" => "United Kingdom",
			"FR" => "France",
			"IN" => "India",
			"CA" => "Canada",
			"IT" => "Italy",
			"AU" => "Australia",
			"KR" => "South Korea",
			default => "Unknown",
		};
	}
}

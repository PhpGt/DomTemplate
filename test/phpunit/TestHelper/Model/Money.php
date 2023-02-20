<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

use Stringable;

class Money implements Stringable {

	public function __construct(
		public readonly float $value,
		public readonly Currency $currency,
		public readonly int $decimalAccuracy = 2,
	) {}

	public function __toString():string {
		return Currency::getSymbol($this->currency)
			. number_format($this->value, 2);
	}

	public function withAddition(Money $add):self {
		$newValue = round(
			$add->value,
			$this->decimalAccuracy,
		) + round(
			$this->value,
			$this->decimalAccuracy,
		);
		return new self(
			$newValue,
			$this->currency,
		);
	}
}

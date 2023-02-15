<?php
namespace Gt\DomTemplate;

use DateTimeInterface;
use Stringable;

class BindValue implements Stringable {
	public function __construct(
		private readonly mixed $rawValue
	) {}

	public function __toString():string {
		$value = $this->rawValue ?? "";

		if($value instanceof DateTimeInterface) {
			$value = $value->format(DateTimeInterface::RFC7231);
		}

		return $value;
	}
}

<?php
namespace Gt\DomTemplate;

use Attribute;

#[Attribute]
class Bind {
	public function __construct(
		private string $key
	) {
	}
}

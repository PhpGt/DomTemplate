<?php /** @noinspection PhpPropertyOnlyWrittenInspection */
namespace Gt\DomTemplate;

use Attribute;

#[Attribute]
class Bind {
	public function __construct(
		public string $key
	) {
	}
}

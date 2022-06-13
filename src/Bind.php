<?php /** @noinspection PhpPropertyOnlyWrittenInspection */
namespace Gt\DomTemplate;

use Attribute;

/** @codeCoverageIgnore */
#[Attribute]
class Bind {
	public function __construct(
		public string $key
	) {
	}
}

<?php
namespace Gt\DomTemplate\Test\TestHelper\Model\MusicIteratorAggregate;

class Track {
	public function __construct(
		public string $name,
		public ?int $durationSeconds = null,
	) {
	}
}

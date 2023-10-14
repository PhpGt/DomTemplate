<?php
namespace Gt\DomTemplate\Test\TestHelper\Model\MusicIteratorAggregate;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Artist implements IteratorAggregate {
	/** @param array<Album> $albumList */
	public function __construct(
		public readonly string $name,
		public readonly array $albumList,
	) {
	}

	public function getIterator():Traversable {
		return new ArrayIterator($this->albumList);
	}
}

<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;

abstract class PartialContentExpander {
	public function __construct(
		protected Document $document,
		protected PartialContent $partialContent
	) {
	}

	/** @return array<int, mixed> */
	abstract public function expand():array;
}

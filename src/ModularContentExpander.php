<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;

abstract class ModularContentExpander {
	public function __construct(
		protected Document $document,
		protected ModularContent $modularContent
	) {
	}

	abstract public function expand():array;
}

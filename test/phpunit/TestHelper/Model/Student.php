<?php
namespace Gt\DomTemplate\Test\TestHelper\Model;

use Gt\DomTemplate\BindGetter;

class Student {
	public function __construct(
		public readonly string $firstName,
		public readonly string $lastName,
		private readonly array $moduleList,
	) {
	}

	#[BindGetter]
	public function getModuleList():array {
		return $this->moduleList;
	}
}

<?php
namespace Gt\DomTemplate\Test\Helper;

class TodoItemExampleObject {
	public $id;
	public $title;

	public function __construct(int $id, string $title) {
		$this->id = $id;
		$this->title = $title;
	}
}
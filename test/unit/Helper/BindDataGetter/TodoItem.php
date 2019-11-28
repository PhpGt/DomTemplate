<?php
namespace Gt\DomTemplate\Test\Helper\BindDataGetter;

use Gt\DomTemplate\BindObject;

class TodoItem implements BindObject {
	private $id;
	private $title;

	public function __construct(
		int $id,
		string $title
	) {
		$this->id = $id;
		$this->title = $title;
	}

	public function getTitle():string {
		return $this->title;
	}


	public function bindId():int {
		return $this->id;
	}
	public function bindTitle():string {
		return $this->getTitle();
	}
}
<?php
namespace Gt\DomTemplate\Test\Helper\BindDataGetter;

use Gt\DomTemplate\BindObject;

class TodoItem implements BindObject {
	private $id;
	private $title;
	private $completed;

	public function __construct(
		int $id,
		string $title,
		bool $completed = false
	) {
		$this->id = $id;
		$this->title = $title;
		$this->completed = $completed;
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

	public function bindCompleted():bool {
		return $this->completed;
	}
}
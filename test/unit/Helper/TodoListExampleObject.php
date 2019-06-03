<?php
namespace Gt\DomTemplate\Test\Helper;

use Iterator;

class TodoListExampleObject implements Iterator {
	/** @var TodoItemExampleObject[] */
	protected $iterator;
	/** @var int */
	protected $iteratorKey;

	public function __construct(array $items = []) {
		$this->iterator = [];
		$this->iteratorKey = 0;

		foreach($items as $i => $title) {
			$this->iterator []= new TodoItemExampleObject($i, $title);
		}
	}

	/** @link https://php.net/manual/en/iterator.current.php */
	public function current():TodoItemExampleObject {
		return $this->iterator[$this->iteratorKey];
	}

	/** @link https://php.net/manual/en/iterator.next.php */
	public function next():void {
		$this->iteratorKey++;
	}

	/** @link https://php.net/manual/en/iterator.key.php */
	public function key():int {
		return $this->iteratorKey;
	}

	/** @link https://php.net/manual/en/iterator.valid.php */
	public function valid():bool {
		return isset($this->iterator[$this->iteratorKey]);
	}

	/** @link https://php.net/manual/en/iterator.rewind.php */
	public function rewind():void {
		$this->iteratorKey = 0;
	}
}
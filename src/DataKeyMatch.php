<?php
namespace Gt\DomTemplate;

class DataKeyMatch {
	public function __construct(string $key, bool $required) {
		$this->key = $key;
		$this->required = $required;
	}

	public function checkDataExists(iterable $data) {
		if(!$this->required) {
			return;
		}

		if(!isset($data[$this->key])) {
			throw new BoundDataNotSetException($this->key);
		}
	}

	public function getValue(iterable $data):?string {
		$this->checkDataExists($data);
		return $data[$this->key] ?? null;
	}
}
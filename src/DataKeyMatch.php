<?php
namespace Gt\DomTemplate;

use DateTimeInterface;

class DataKeyMatch {
	protected $key;
	protected $required;

	public function __construct(string $key, bool $required) {
		$this->key = $key;
		$this->required = $required;
	}

	public function checkDataExists($data) {
		if(!$this->required) {
			return;
		}

		$value = $data->{$this->key};

		if(is_null($value)) {
			throw new BoundDataNotSetException($this->key);
		}
	}

	public function getValue($data):?string {
		$this->checkDataExists($data);
		$value = $data->{$this->key} ?? null;

		if($value instanceof DateTimeInterface) {
			$value = $value->format("Y-m-d H:i:s");
		}

		return $value;
	}
}
<?php
namespace Gt\DomTemplate;

interface BindDataMapper {
	/**
	 * @return array An associative array that describes the key-values
	 * required to bind to the DomTemplate document.
	 *
	 * Example: [
	 * 	"fullName" => $this->forename . " " . $this->surname,
	 * 	"age" => date("Y") - $this->dob->format("Y"),
	 * ]
	 */
	public function bindDataMap():iterable;
}
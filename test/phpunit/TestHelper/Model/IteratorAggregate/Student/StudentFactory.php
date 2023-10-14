<?php

namespace Gt\DomTemplate\Test\TestHelper\Model\IteratorAggregate\Student;

class StudentFactory {
	/**
	 * @param array<int, array<string, string|array<string>>> $input
	 * @return array<Student>
	 */
	public function buildStudentArray(array $input):array {
		$studentArray = [];

		foreach($input as $inputStudent) {
			$moduleList = [];
			foreach($inputStudent["modules"] as $moduleName) {
				array_push(
					$moduleList,
					new Module($moduleName),
				);
			}

			array_push(
				$studentArray,
				new Student(
					$inputStudent["firstName"],
					$inputStudent["lastName"],
					$moduleList,
				),
			);
		}

		return $studentArray;
	}
}

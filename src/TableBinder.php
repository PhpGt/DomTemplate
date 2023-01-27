<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\ElementType;
use Stringable;
use Traversable;

class TableBinder {
	public function __construct(
		private ?TemplateCollection $templateCollection = null,
		private ?ElementBinder $elementBinder = null,
		private ?HTMLAttributeBinder $htmlAttributeBinder = null,
		private ?HTMLAttributeCollection $htmlAttributeCollection = null,
		private ?PlaceholderBinder $placeholderBinder = null
	) {}

	/**
	 * @param array<int, array<int, string>>|array<int, array<int|string, string|array<int, mixed>>> $tableData
	 * @param Element $context
	 */
	public function bindTableData(
		array $tableData,
		Document|Element $context,
		?string $bindKey = null
	):void {
		$tableData = $this->normaliseTableData($tableData);

		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		$this->initBinders();

		$tableArray = [$context];
		if($context->elementType !== ElementType::HTMLTableElement) {
			$tableArray = [];
			foreach($context->querySelectorAll("table") as $table) {
				array_push($tableArray, $table);
			}
		}

		foreach($tableArray as $i => $table) {
			$dataBindTableAttr = "data-bind:table";
			$dataBindTableElement = $table;
			if(!$dataBindTableElement->hasAttribute($dataBindTableAttr)) {
				$dataBindTableElement = $table->closest("[data-bind:table]") ?? $table;
			}

			if(!$dataBindTableElement
			|| $dataBindTableElement->getAttribute("data-bind:table") != $bindKey) {
				unset($tableArray[$i]);
			}
		}

		if(empty($tableArray)) {
			throw new TableElementNotFoundInContextException();
		}

		$headerRow = array_shift($tableData);
		foreach($tableArray as $table) {
			$allowedHeaders = $headerRow;

			$tHead = $table->tHead;
			if($tHead) {
				$allowedHeaders = [];

				$tHeadRow = $tHead->rows[0];
				foreach($tHeadRow->cells as $cell) {
					$headerKey = $cell->hasAttribute("data-table-key")
						? $cell->getAttribute("data-table-key")
						: trim($cell->textContent);
					array_push($allowedHeaders, $headerKey);
				}
			}
			else {
				$tHead = $table->createTHead();
				$theadTr = $tHead->insertRow();

				foreach($headerRow as $value) {
					$th = $theadTr->insertCell();
					$th->textContent = $value;
				}
			}

			$tbody = $table->tBodies[0] ?? null;
			if(!$tbody) {
				$tbody = $table->createTBody();
			}

			$templateCollection = $this->templateCollection
				?? new TemplateCollection($context->ownerDocument);

			foreach($tableData as $rowData) {
				try {
					$trTemplate = $templateCollection->get($tbody);
					$tr = $trTemplate->insertTemplate();
				}
				catch(TemplateElementNotFoundInContextException) {
					$tr = $tbody->insertRow();
				}

				/** @var int|string|null $firstKey */
				$firstKey = key($rowData);

				foreach($allowedHeaders as $headerIndex => $allowedHeader) {
					$rowIndex = array_search($allowedHeader, $headerRow);
					$cellTypeToCreate = "td";

					if(is_string($firstKey)) {
						if($rowIndex === 0) {
							$columnValue = $firstKey;
							$cellTypeToCreate = "th";
						}
						else {
							$columnValue = $rowData[$firstKey][$rowIndex - 1];
						}
					}
					else {
						if(false === $rowIndex) {
							$columnValue = "";
						}
						else {
							$columnValue = $rowData[$rowIndex];
						}
					}

					if($headerIndex < $tr->cells->length - 1) {
						if(false === $rowIndex) {
							continue;
						}

						$cellElement = $tr->cells[$headerIndex];
					}
					else {
						$cellElement = $tr->ownerDocument->createElement($cellTypeToCreate);
					}

					$cellElement->textContent = $columnValue ?? "";

					if(!$cellElement->parentElement) {
						$tr->appendChild($cellElement);
					}
				}

				foreach($rowData as $index => $value) {
					$headerRowIndex = $index;
					if(!is_int($index)) {
						$headerRowIndex = null;
						foreach($tableData as $tableDataIndex => $tableDatum) {
							if($index === key($tableDatum)) {
								$headerRowIndex = $tableDataIndex;
								break;
							}
						}
					}

					$key = $headerRow[$headerRowIndex];
					$this->elementBinder->bind(
						$key,
						$value,
						$tr
					);
				}
			}
		}
	}

	/**
	 * @param iterable<int,iterable<int,string>>|iterable<int,iterable<string,string>>|iterable<string,iterable<int,string>>|iterable<int, iterable<int,string>|iterable<string,string>> $bindValue
	 * The structures allowed by this method are:
	 *
	 * 1) iterable<int, iterable<int,string>> If $bindValue has keys of type
	 * int, and the value of index 0 is an iterable of strings, then the
	 * value of index 0 must represent the columnHeaders; subsequent values
	 * must represent the columnValues.
	 * 2) iterable<int, iterable<int,string>|iterable<string,string>>
	 * Similar to structure 1, but with a key difference. If the value of
	 * index 0 is an iterable of strings, BUT the next value is an iterable
	 * with keys of type string, this represents "double header" data - the
	 * returned normalised value retains this double header data so the
	 * binder can insert <th> elements in the <tbody>.
	 * 3) iterable<int, iterable<string,string>> If $bindValue has keys of
	 * type int, and the value of index 0 is associative, then the value of
	 * each index must represent the individual rows, where the
	 * columnHeaders are the string key of the inner iterable, and the
	 * columnValues are the string value of the inner iterable.
	 * 4) iterable<string,iterable<int,string>> If $bindValue has keys of
	 * type string, the keys must represent the columnHeaders and the values
	 * must represent the columnValues.
	 *
	 * @return array<int, array<int, string>|array<string,array<int,string>>>
	 * A two-dimensional array where the outer array represents the rows,
	 * the inner array represents the columns. The first index's value is
	 * always the columnHeaders. The other index's values are always the
	 * columnValues. Typically, columnValues will be array<int,string> apart
	 * from when the data represents double-header tables, in which case the
	 * columnValues will be within array<string,array<int,string>>.
	 */
	private function normaliseTableData(iterable $bindValue):array {
		$normalised = [];
		if($bindValue instanceof Traversable) {
			$bindValue = iterator_to_array($bindValue);
		}

		$structureType = $this->detectTableDataStructureType($bindValue);
		if($structureType === TableDataStructureType::NORMALISED) {
			$normalised = $bindValue;
		}
		elseif($structureType === TableDataStructureType::ASSOC_ROW) {
			$headers = [];
			foreach($bindValue as $row) {
				if(empty($headers)) {
					$headers = array_keys($row);
					array_push($normalised, $headers);
				}
				$normalisedRow = [];
				foreach($headers as $h) {
					array_push($normalisedRow, $row[$h]);
				}
				array_push($normalised, $normalisedRow);
			}
		}
		elseif($structureType === TableDataStructureType::HEADER_VALUE_LIST) {
			$headers = array_keys($bindValue);
			array_push($normalised, $headers);

			foreach($bindValue[$headers[0]] as $rowIndex => $ignored) {
				$row = [];
				foreach($headers as $h) {
					array_push($row, $bindValue[$h][$rowIndex]);
				}
				array_push($normalised, $row);
			}
		}
		elseif($structureType === TableDataStructureType::DOUBLE_HEADER) {
			$headers = $bindValue[0];
			$rows = [];
			foreach($bindValue[1] ?? [] as $thValue => $bindValueRow) {
				array_push($rows, [
					$thValue => $bindValueRow,
				]);
			}
			$normalised = [
				$headers,
				...$rows,
			];
		}

		return $normalised;
	}

	/** @param array<int,array<int,string>>|array<int,array<string,string>>|array<string,array<int,string>>|array<int, array<int,string>|array<string,string>> $bindValue */
	public function detectTableDataStructureType(array $array):TableDataStructureType {
		if(empty($array)) {
			return TableDataStructureType::NORMALISED;
		}

		reset($array);

		if(array_is_list($array)) {
			$allRowsAreLists = true;
			$allRowDataAreLists = true;
			$allRowDataAreAssoc = true;

			foreach($array as $rowIndex => $rowData) {
				if(!is_array($rowData)) {
					throw new IncorrectTableDataFormat("Row $rowIndex data is not iterable");
				}

				if(array_is_list($rowData)) {
					$allRowDataAreAssoc = false;
				}
				else {
					$allRowsAreLists = false;
				}

				foreach($rowData as $cellIndex => $cellData) {
					if($rowIndex > 0) {
						if(isset($array[0]) && is_array($array[0]) && array_is_list($array[0]) && !array_is_list($rowData)) {
							if(!is_iterable($cellData)) {
								throw new IncorrectTableDataFormat("Row $rowIndex has a string key ($cellIndex) but the value is not iterable.");
							}
						}

						if(!is_array($cellData) || !array_is_list($cellData)) {
							$allRowDataAreLists = false;
						}
					}
				}
			}

			if($allRowsAreLists) {
				return TableDataStructureType::NORMALISED;
			}
			else {
				if($allRowDataAreLists) {
					return TableDataStructureType::DOUBLE_HEADER;
				}
				elseif($allRowDataAreAssoc) {
					return TableDataStructureType::ASSOC_ROW;
				}
			}
		}
		else {
			$allRowDataAreLists = true;
			foreach($array as $rowIndex => $rowData) {
				if(!is_array($rowData)) {
					throw new IncorrectTableDataFormat("Column data \"$rowIndex\" is not iterable.");
				}
				if(!array_is_list($rowData)) {
					$allRowDataAreLists = false;
				}
			}

			if($allRowDataAreLists) {
				return TableDataStructureType::HEADER_VALUE_LIST;
			}
		}

		throw new IncorrectTableDataFormat();
	}

	private function OLD_normaliseTableData(array $bindValue):array {
		$normalised = [];

		reset($bindValue);
		$firstKey = key($bindValue);

// Structure 1: iterable<int, iterable<int,string>>
// Structure 2: iterable<int, iterable<int,string>|iterable<string,string>>
// Structure 3: iterable<int, iterable<string,string>>
		$headerRow = [];
		if(is_int($firstKey)) {
			foreach($bindValue as $rowIndex => $rowData) {
				if(!is_iterable($rowData)) {
					throw new IncorrectTableDataFormat("Row $rowIndex data is not iterable.");
				}
				$row = [];

				$isDoubleHeader = true;
				foreach($rowData as $columnIndex => $columnValue) {
					if(is_string($columnIndex)) {
						if(!is_iterable($columnValue)
						&& $headerRow
						&& count($headerRow) === count($rowData)
						&& array_keys($rowData) !== $headerRow) {
							throw new IncorrectTableDataFormat("Row $rowIndex has a string key ($columnIndex) but the value is not iterable.");
						}
					}

					$isDoubleHeader = false;
					break;
//					|| !is_iterable($columnValue)) {
//						$isDoubleHeader = false;
//					}
				}

				if(!$headerRow && is_iterable($rowData)) {
					$rowDataFirstIndex = key($rowData);
					if(is_int($rowDataFirstIndex)) {
						$headerRow = $rowData;
					}
					else {
						$headerRow = array_keys($rowData);
					}
				}

				foreach($rowData as $columnIndex => $columnValue) {
					if($isDoubleHeader) {
						$doubleHeader = [$columnIndex => []];

						foreach($columnValue as $cellValue) {
							array_push($doubleHeader[$columnIndex], $cellValue);
						}
						array_push($normalised, $doubleHeader);
					}
					else {
						if(empty($normalised)) {
							array_push($normalised, $headerRow);
						}
						array_push($row, $columnValue);
					}
				}
				if(!empty($row)) {
					array_push($normalised, $row);
				}
			}
		}
// Structure 4: iterable<string,iterable<int,string>>
		else {
			array_push($normalised, array_keys($bindValue));
			$rows = [];

			foreach($bindValue as $colName => $colValueList) {
				if(!is_iterable($colValueList)) {
					throw new IncorrectTableDataFormat("Column data \"$colName\" is not iterable.");
				}

				foreach($colValueList as $i => $colValue) {
					if(!isset($rows[$i])) {
						$rows[$i] = [];
					}

					array_push($rows[$i], $colValue);
				}
			}

			array_push($normalised, ...$rows);
		}

		return $normalised;
	}

	private function initBinders():void {
		if(!$this->htmlAttributeBinder) {
			$this->htmlAttributeBinder = new HTMLAttributeBinder();
		}
		if(!$this->htmlAttributeCollection) {
			$this->htmlAttributeCollection = new HTMLAttributeCollection();
		}
		if(!$this->placeholderBinder) {
			$this->placeholderBinder = new PlaceholderBinder();
		}
		if(!$this->elementBinder) {
			$this->elementBinder = new ElementBinder(
				$this->htmlAttributeBinder,
				$this->htmlAttributeCollection,
				$this->placeholderBinder
			);
		}
	}
}

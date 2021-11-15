<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Gt\Dom\HTMLElement\HTMLTableCellElement;
use Gt\Dom\HTMLElement\HTMLTableElement;
use Gt\Dom\HTMLElement\HTMLTableRowElement;
use Gt\Dom\HTMLElement\HTMLTableSectionElement;
use Stringable;

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
		Document|Element $context
	):void {
		$tableData = $this->normaliseTableData($tableData);

		if($context instanceof Document) {
			$context = $context->documentElement;
		}

		$this->initBinders();

		$tableArray = [$context];
		if(!$context instanceof HTMLTableElement) {
			$tableArray = [];
			foreach($context->querySelectorAll("table") as $table) {
				array_push($tableArray, $table);
			}
		}

		if(empty($tableArray)) {
			throw new TableElementNotFoundInContextException();
		}

		$headerRow = array_shift($tableData);
		/** @var HTMLTableElement $table */
		foreach($tableArray as $table) {
			$allowedHeaders = $headerRow;

			$tHead = $table->tHead;
			if($tHead) {
				$allowedHeaders = [];

				/** @var HTMLTableRowElement $tHeadRow */
				$tHeadRow = $tHead->rows[0];
				foreach($tHeadRow->cells as $cell) {
					/** @var HTMLTableCellElement $cell */
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

			/** @var ?HTMLTableSectionElement $tbody */
			$tbody = $table->tBodies[0] ?? null;
			if(!$tbody) {
				$tbody = $table->createTBody();
			}

			$templateCollection = $this->templateCollection
				?? new TemplateCollection($context->ownerDocument);

			foreach($tableData as $rowData) {
				try {
					$trTemplate = $templateCollection->get($tbody);
					/** @var HTMLTableRowElement $tr */
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
					$key = $headerRow[$index];
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
	 * @param iterable<int,iterable<int,string>>|iterable<string,iterable<int, string>>|iterable<string,iterable<string, iterable<int, string>>> $bindValue
	 * The three structures allowed by this method are:
	 * 1) If $bindValue has int keys, the first value must represent an
	 * iterable of columnHeaders, and subsequent values must represent an
	 * iterable of columnValues.
	 * 2) If $bindValue has string keys, the keys must represent the column
	 * headers and the value must be an iterable of columnValues.
	 * 3) If columnValues has int keys, each item represents the value of
	 * a column <td> element.
	 * 4) If columnValues has a string keys, each key represents a <th> and
	 * each sub-iterable represents the remaining column values.
	 * @return array<int, array<int|string, string|Stringable>> A
	 * two-dimensional array where the outer array represents the rows, the
	 * inner array represents the columns.
	 */
	private function normaliseTableData(iterable $bindValue):array {
		$normalised = [];

		reset($bindValue);
		$key = key($bindValue);

		if(is_int($key)) {
			foreach($bindValue as $i => $value) {
				if(!is_iterable($value)) {
					throw new IncorrectTableDataFormat("Row $i data is not iterable.");
				}
				$row = [];

				foreach($value as $j => $columnValue) {
// A string key within the inner array indicates "double header" table data.
					if(is_string($j)) {
						$doubleHeader = [$j => []];
						if(!is_iterable($columnValue)) {
							throw new IncorrectTableDataFormat("Row $i has a string key ($j) but the value is not iterable.");
						}

						foreach($columnValue as $cellValue) {
							array_push($doubleHeader[$j], $cellValue);
						}
						array_push($normalised, $doubleHeader);
					}
					else {
						array_push($row, $columnValue);
					}
				}
				if(!empty($row)) {
					array_push($normalised, $row);
				}
			}
		}
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

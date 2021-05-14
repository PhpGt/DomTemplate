<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;

class DocumentBinder {
	private ElementBinder $elementBinder;
	private PlaceholderBinder $placeholderBinder;
	private TableBinder $tableBinder;
	private ListBinder $listBinder;
	private TemplateCollection $templateCollection;

	public function __construct(
		private Document $document,
		private array $config = [],
		?ElementBinder $elementBinder = null,
		?PlaceholderBinder $placeholderBinder = null,
		?TableBinder $tableBinder = null,
		?ListBinder $listBinder = null,
		?TemplateCollection $templateCollection = null,
	) {
		$this->templateCollection = $templateCollection ?? new TemplateCollection($document);
		$this->elementBinder = $elementBinder ?? new ElementBinder();
		$this->placeholderBinder = $placeholderBinder ?? new PlaceholderBinder($document);
		$this->tableBinder = $tableBinder ?? new TableBinder();
		$this->listBinder = $listBinder ?? new ListBinder($this->templateCollection);
	}

	/**
	 * Applies the string value of $value anywhere within $context that
	 * has a data-bind attribute with no specified key.
	 */
	public function bindValue(
		mixed $value,
		Element $context = null
	):void {
		$this->bind(null, $value, $context);
	}

	/**
	 * Applies the string value of $value to any elements within $context
	 * that have the data-bind attribute matching the provided key.
	 */
	public function bindKeyValue(
		string $key,
		mixed $value,
		Element $context = null
	):void {
		$this->bind($key, $value, $context);
	}

	/**
	 * Binds multiple key-value-pairs to any matching elements within
	 * the $context element.
	 */
	public function bindData(
		mixed $kvp,
		Element $context = null
	):void {
		if($this->isIndexedArray($kvp)) {
			throw new IncompatibleBindDataException("bindData is only compatible with key-value-pair data, but it was passed an indexed array.");
		}

		foreach($kvp as $key => $value) {
			$this->bindKeyValue($key, $value, $context);
		}
	}

	public function bindTable(
		mixed $tableData,
		Element $context = null
	):void {
		$this->tableBinder->bindTableData($tableData, $context);
	}

	private function bind(
		?string $key,
		mixed $value,
		?Element $context = null
	):void {
		if(!$context) {
			$context = $this->document->documentElement;
		}

		$this->elementBinder->bind($key, $value, $context);
		$this->placeholderBinder->bind($key, $value, $context);
	}

	private function isIndexedArray(mixed $data):bool {
		if(!is_array($data)) {
			return false;
		}

		foreach(array_keys($data) as $key) {
			if(!is_int($key)) {
				return false;
			}
		}

		return true;
	}
}

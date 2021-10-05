<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document;
use Gt\Dom\Element;
use Iterator;
use ReflectionObject;

class DocumentBinder {
	private ElementBinder $elementBinder;
	private PlaceholderBinder $placeholderBinder;
	private TableBinder $tableBinder;
	private ListBinder $listBinder;
	private TemplateCollection $templateCollection;

	/**
	 * @param array<string, string> $config
	 */
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
		$this->placeholderBinder = $placeholderBinder ?? new PlaceholderBinder();
		$this->tableBinder = $tableBinder ?? new TableBinder();
		$this->listBinder = $listBinder ?? new ListBinder($this->templateCollection);
	}

	/**
	 * Applies the string value of $value anywhere within $context that
	 * has a data-bind attribute with no specified key.
	 */
	public function bindValue(
		mixed $value,
		?Element $context = null
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
		?Element $context = null
	):void {
		$this->bind($key, $value, $context);
	}

	/**
	 * Binds multiple key-value-pairs to any matching elements within
	 * the $context element.
	 */
	public function bindData(
		mixed $kvp,
		?Element $context = null
	):void {
		if($this->isIndexedArray($kvp)) {
			throw new IncompatibleBindDataException("bindData is only compatible with key-value-pair data, but it was passed an indexed array.");
		}

		if(is_object($kvp) && method_exists($kvp, "asArray")) {
			$kvp = $kvp->asArray();
		}

		if(is_object($kvp) && !is_iterable($kvp)) {
			$refObj = new ReflectionObject($kvp);
			foreach($refObj->getMethods() as $refMethod) {
				foreach($refMethod->getAttributes(Bind::class) as $refAttr) {
					$bindName = $refAttr->getArguments()[0];
					$kvp->$bindName = $refMethod->getClosure($kvp);
				}
			}
		}

		foreach($kvp as $key => $value) {
			$this->bindKeyValue($key, $value, $context);
		}
	}

	public function bindTable(
		mixed $tableData,
		?Element $context = null
	):void {
		$this->tableBinder->bindTableData($tableData, $context);
	}

	/**
	 * @param Iterator<mixed> $listData
	 */
	public function bindList(
		iterable $listData,
		?Element $context = null,
		?string $templateName = null
	):int {
		if(!$context) {
			$context = $this->document;
		}

		return $this->listBinder->bindListData($listData, $context, $templateName);
	}

	private function bind(
		?string $key,
		mixed $value,
		?Element $context = null
	):void {
		if(!$context) {
			$context = $this->document->documentElement;
		}

		if(is_callable($value)) {
			$value = call_user_func($value);
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

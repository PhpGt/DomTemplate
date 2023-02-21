<?php
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Document;
use Gt\Dom\Element;

class DocumentBinder {
	private ElementBinder $elementBinder;
	private PlaceholderBinder $placeholderBinder;
	private TableBinder $tableBinder;
	private ListBinder $listBinder;
	private TemplateCollection $templateCollection;
	private BindableCache $bindableCache;

	/**
	 * @param array<string, string> $config
	 */
	public function __construct(
		private readonly Document $document,
		private array $config = [],
		?ElementBinder $elementBinder = null,
		?PlaceholderBinder $placeholderBinder = null,
		?TableBinder $tableBinder = null,
		?ListBinder $listBinder = null,
		?TemplateCollection $templateCollection = null,
		?BindableCache $bindableCache = null
	) {
		$this->templateCollection = $templateCollection ?? new TemplateCollection($document);
		$this->elementBinder = $elementBinder ?? new ElementBinder();
		$this->placeholderBinder = $placeholderBinder ?? new PlaceholderBinder();
		$this->tableBinder = $tableBinder ?? new TableBinder($this->templateCollection);
		$this->listBinder = $listBinder ?? new ListBinder($this->templateCollection);
		$this->bindableCache = $bindableCache ?? new BindableCache();

// This is temporary, to suppress PHPStan's complaints for declaring a variable
// without using it. There are plans to use the config variable, but it is
// currently not yet used, and this technique prevents the constructor
// parameters from changing over time.
		if(!$this->config) {
			$this->config = [];
		}
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
			if($this->bindableCache->isBindable($kvp)) {
				$kvp = $this->bindableCache->convertToKvp($kvp);
			}
		}

		foreach($kvp as $key => $value) {
			$this->bindKeyValue($key, $value, $context);
		}
	}

	public function bindTable(
		mixed $tableData,
		?Element $context = null,
		?string $bindKey = null
	):void {
		$this->tableBinder->bindTableData(
			$tableData,
			$context ?? $this->document,
			$bindKey
		);
	}

	/**
	 * @param iterable<int, mixed> $listData
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

	/** @param iterable<int, mixed> $listData */
	public function bindListCallback(
		iterable $listData,
		callable $callback,
		?Element $context = null,
		?string $templateName = null
	):int {
		if(!$context) {
			$context = $this->document;
		}

		return $this->listBinder->bindListData(
			$listData,
			$context,
			$templateName,
			$callback
		);
	}

	public function cleanDatasets():void {
		$xpathResult = $this->document->evaluate(
			"//*/@*[starts-with(name(), 'data-bind')] | //*/@*[starts-with(name(), 'data-template')] | //*/@*[starts-with(name(), 'data-table-key')]"
		);
		/** @var Attr $item */
		foreach($xpathResult as $item) {
			$item->ownerElement->removeAttribute($item->name);
		}
	}

	private function bind(
		?string $key,
		mixed $value,
		?Element $context = null
	):void {
		if(!$context) {
			$context = $this->document->documentElement;
		}

		if(is_callable($value) && !is_string($value)) {
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

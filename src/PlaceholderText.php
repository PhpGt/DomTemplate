<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Text;

class PlaceholderText {
	private string $bindKey;
	/** @var string[] */
	private array $filterList;
	private ?string $default;

	public function __construct(
		private Text $originalText
	) {
		$this->process();
	}

	public function getBindKey():string {
		return $this->bindKey;
	}

	/** @return string[] */
	public function getFilterList():array {
		return $this->filterList;
	}

	public function getDefault():string {
		return $this->default ?? "";
	}

	private function process():void {
		$data = trim($this->originalText->data, "{}");
		$this->bindKey = $this->parseBindKey($data);
		$this->filterList = $this->parseFilterList($data);
		$this->default = $this->parseDefault($data);
		$this->originalText->data = $this->default ?? $this->bindKey;
	}

	private function parseBindKey(string $data):string {
		$bindKey = strtok($data, "?");
		$bindKey = strtok($bindKey, ":");
		return trim($bindKey);
	}

	/** @return string[] */
	private function parseFilterList(string $data):array {
		$filterList = explode(":", $data);
		return array_map("trim", $filterList);
	}

	private function parseDefault(string $data):?string {
		$nullCoalescePos = strpos($data, "??");
		if($nullCoalescePos === false) {
			return null;
		}

		$default = substr($data, $nullCoalescePos + 2);
		return trim($default);
	}

	public function isWithinContext(Element $context):bool {
		return $context->contains($this->originalText);
	}

	public function setValue(mixed $value):void {
		$this->originalText->data = (string)$value;
	}
}

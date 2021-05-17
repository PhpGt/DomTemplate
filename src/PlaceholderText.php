<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Text;

class PlaceholderText {
	private string $bindKey;
	private ?string $default;

	public function __construct(
		private Text $originalText
	) {
		$this->process();
	}

	public function getBindKey():string {
		return $this->bindKey;
	}

	private function process():void {
		$data = trim($this->originalText->data, "{}");
		$this->bindKey = $this->parseBindKey($data);
		$this->default = $this->parseDefault($data);
	}

	private function parseBindKey(string $data):string {
		$bindKey = strtok($data, "?");
		$bindKey = strtok($bindKey, ":");
		return trim($bindKey);
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

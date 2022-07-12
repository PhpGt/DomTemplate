<?php /** @noinspection PhpPropertyOnlyWrittenInspection tracked in issue #290 */
namespace Gt\DomTemplate;

use Gt\Dom\Attr;
use Gt\Dom\Element;
use Gt\Dom\Text;

class PlaceholderText {
	private string $bindKey;
	private ?string $default;

	public function __construct(
		private readonly Text $originalText
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

	public function setValue(mixed $value):void {
		$stringValue = (string)$value;

		if(strlen($stringValue) === 0) {
			$stringValue = $this->default ?: "";
		}
		$this->originalText->data = $stringValue;

		$parent = $this->originalText->parentNode;
		if($parent instanceof Attr) {
			$this->originalText->normalize();
			$qualifiedName = $parent->name;
			$wholeText = $this->originalText->wholeText;
			$parent->ownerElement->setAttribute($qualifiedName, $wholeText);
		}
	}
}

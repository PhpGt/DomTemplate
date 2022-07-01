<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element;
use Gt\Dom\Node;
use Stringable;

class NodePathCalculator implements Stringable {
	public function __construct(
		private Node|Element $element
	) {
	}

	public function __toString():string {
		$path = "";
		/** @var Element $context */
		$context = $this->element;

		do {
			$contextPath = strtolower($context->tagName);

			if($context->id || $context->className) {
				$attrPath = "";
				if($id = $context->id) {
					$attrPath .= "@id='$id'";
				}

				foreach($context->classList as $class) {
					if(strlen($attrPath) !== 0) {
						$attrPath .= " and ";
					}

					$attrPath .= "contains(concat(' ',normalize-space(@class),' '),' $class ')";
				}

				$contextPath .= "[$attrPath]";
			}

			$path = "/" . $contextPath . $path;
			$context = $context->parentElement;
		}
		while($context && $context instanceof Element);

		return $path;
	}
}

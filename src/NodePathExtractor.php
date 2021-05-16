<?php
namespace Gt\DomTemplate;

use Gt\Dom\Node;
use Gt\Dom\Facade\NodeClass\DOMElementFacade;
use Stringable;

class NodePathExtractor implements Stringable {
	public function __construct(
		private Node $element
	) {
	}

	public function __toString():string {
		$refObj = new \ReflectionObject($this->element);
		$refProp = $refObj->getProperty("domNode");
		$refProp->setAccessible(true);
		/** @var DOMElementFacade $nativeDomNode */
		$nativeDomNode = $refProp->getValue($this->element);
		return $nativeDomNode->getNodePath();
	}
}

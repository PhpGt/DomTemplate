<?php
namespace Gt\DomTemplate;

use DOMElement;
use Gt\Dom\Document as BaseDocument;

class Document extends BaseDocument {
	public function __construct($document = null) {
		parent::__construct($document);
		$this->registerNodeClass(DOMElement::class, Node::class);
	}
}
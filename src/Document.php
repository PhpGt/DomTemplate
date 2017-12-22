<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document as BaseDocument;
use DOMNode;
use DOMElement;
use DOMDocumentFragment;


class Document extends BaseDocument {
	public function __construct($document = null) {
		parent::__construct($document);

		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);
	}
}
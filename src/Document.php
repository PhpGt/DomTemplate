<?php
namespace Gt\DomTemplate;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMDocumentFragment;
use Gt\Dom\Document as BaseDocument;

class Document extends BaseDocument {
	public function __construct($document = null) {
		parent::__construct($document);

		$this->registerNodeClass(DOMDocument::class, Document::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);
	}
}
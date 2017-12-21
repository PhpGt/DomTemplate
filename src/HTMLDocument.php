<?php
namespace Gt\DomTemplate;

use DOMElement;
use DOMNode;
use Gt\Dom\HTMLDocument as BaseHTMLDocument;

class HTMLDocument extends BaseHTMLDocument {
	public function __construct($document = "") {
		parent::__construct($document);
		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
	}
}
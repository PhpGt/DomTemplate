<?php
namespace Gt\DomTemplate;

use Gt\Dom\Document as BaseDocument;
use DOMElement;
use DOMDocumentFragment;

class Document extends BaseDocument {
	public function __construct($document = null) {
		parent::__construct($document);

		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);
	}
}
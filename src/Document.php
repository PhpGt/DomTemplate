<?php
namespace Gt\DomTemplate;

use DOMAttr;
use DOMCharacterData;
use DOMComment;
use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMDocumentFragment;
use DOMText;
use Gt\Dom\Document as BaseDocument;

/**
 * @property-read DocumentType $doctype;
 * @property-read Element $documentElement
 * @property-read Document $ownerDocument
 *
 * @method Attr createAttribute(string $name)
 * @method Comment createComment(string $data)
 * @method DocumentFragment createDocumentFragment()
 * @method Element createElement(string $name)
 * @method Element createTextNode(string $content)
 * @method ?Element getElementById(string $id)
 */
class Document extends BaseDocument {
	use ParentNode;

	public function __construct($document = null) {
		parent::__construct($document);

		$this->registerNodeClass(DOMNode::class, Node::class);
		$this->registerNodeClass(DOMElement::class, Element::class);
		$this->registerNodeClass(DOMAttr::class, Attr::class);
		$this->registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);
		$this->registerNodeClass(DOMDocumentType::class, DocumentType::class);
		$this->registerNodeClass(DOMCharacterData::class, CharacterData::class);
		$this->registerNodeClass(DOMText::class, Text::class);
		$this->registerNodeClass(DOMComment::class, Comment::class);
	}
}
<?php
namespace Gt\DomTemplate;

use DOMAttr;
use DOMNode;
use Gt\Dom\Element as BaseElement;

/**
 * @property-read Attr[] $attributes
 * @property string $className Gets and sets the value of the class attribute
 * @property-read TokenList $classList Returns a live TokenList collection of
 * the class attributes of the element
 * @property bool $checked Indicates whether the element is checked or not
 * @property bool $selected Indicates whether the element is selected or not
 * @property string $value Gets or sets the value of the element according to
 * its element type
 * @property string $id Gets or sets the value of the id attribute
 * @property string $innerHTML Gets or sets the HTML syntax describing the
 * element's descendants
 * @property string $outerHTML Gets or sets the HTML syntax describing the
 * element and its descendants. It can be set to replace the element with nodes
 * parsed from the given string
 * @property string $innerText
 * @property-read StringMap $dataset
 *
 * @method Attr setAttribute(string $name, string $value)
 * @method Attr setAttributeNode(DOMAttr $attr)
 * @method Attr getAttributeNode(string $name)
 *
 * Inherited from Node:
 *
 * @method Node|Element appendChild(DOMNode $newnode)
 * @method Node|Element cloneNode(bool $deep = false)
 * @method Node|Element insertBefore(DOMNode $newnode, DOMNode $refnode = null)
 * @method Node|Element removeChild(DOMNode $oldnode)
 * @method Node|Element replaceChild(DOMNode $newnode, DOMNode $oldnode)
 *
 * @property-read ?Node $parentNode
 * @property-read ?Node $firstChild
 * @property-read ?Node $lastChild
 * @property-read ?Node $previousSibling
 * @property-read ?Node $nextSibling
 */
class Element extends BaseElement {
	use NonDocumentTypeChildNode, ChildNode, ParentNode,
		TemplateParent, Bindable;
}
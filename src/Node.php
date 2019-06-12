<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Node as BaseNode;

/**
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
class Node extends BaseNode {
	use NonDocumentTypeChildNode, ChildNode, ParentNode,
		TemplateParent, Bindable;
}
<?php
namespace Gt\DomTemplate;

use DOMNode;
use Gt\Dom\Attr as BaseAttr;

/**
 * @property-read Element $ownerElement
 * @property-read Element $parentNode
 * @property-read Node|Element|null $firstChild
 * @property-read Node|Element|null $lastChild
 * @property-read Node|Element|null $previousSibling
 * @property-read Node|Element|null $nextSibling
 * @property-read HTMLDocument $ownerDocument
 *
 * @method Element appendChild(DOMNode $newnode)
 * @method Element cloneNode(bool $deep = false)
 * @method Element insertBefore(DOMNode $newnode, DOMNode $refnode = null)
 * @method Element removeChild(DOMNode $oldnode)
 * @method Element replaceChild(DOMNode $newnode, DOMNode $oldnode)
 */
class Attr extends BaseAttr {}
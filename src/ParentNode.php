<?php
namespace Gt\DomTemplate;

use DOMNode;

/**
 * @property-read HTMLCollection $children A live HTMLCollection containing all
 *  objects of type Element that are children of this ParentNode.
 * @property-read Node|Element|null $firstChild
 * @property-read Element|null $firstElementChild The Element that is the first
 *  child of this ParentNode.
 * @property-read Node|Element|null $lastChild
 * @property-read Element|null $lastElementChild The Element that is the last
 *  child of this ParentNode.
 * @property-read int $childElementCount The amount of children that the
 *  ParentNode has.
 *
 * @method Element getElementById(string $id)
 * @method Node|Element importNode(DOMNode $importedNode, bool $deep = false)
 * @method Node|Element insertBefore(DOMNode $newNode, DOMNode $refNode = false)
 * @method Node|Element removeChild(DOMNode $oldNode)
 * @method Node|Element replaceChild(DOMNode $newNode, DOMNode $oldNode)
 *
 * @method Element|null querySelector(string $selector)
 * @method HTMLCollection querySelectorAll(string $selector)
 * @method HTMLCollection css(string $selector, string $prefix = "descendant-or-self::")
 * @method HTMLCollection xPath(string $selector)
 * @method HTMLCollection getElementsByTagName(string $tag)
 */
trait ParentNode {}
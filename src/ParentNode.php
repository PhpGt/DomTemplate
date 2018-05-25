<?php
namespace Gt\DomTemplate;

/**
 * @property-read HTMLCollection $children
 * @property-read Element|null $firstElementChild
 * @property-read Element|null $lastElementChild
 *
 * @method Element|null querySelector(string $selector)
 * @method HTMLCollection querySelectorAll(string $selector)
 * @method HTMLCollection css(string $selectors, string $prefix)
 * @method HTMLCollection xPath(string $selector)
 * @method NodeList getElementsByTagName(string $selector)
 */
trait ParentNode {}
<?php
namespace Gt\DomTemplate;

/**
 * This interface does not define any functions, but is used to indicate that
 * the implementing class should expose "bind*" functions for the key-value data
 * it represents.
 *
 * For example: class Person implements BindObject {
 * // This function will be called for the "name" key when bound.
 * public function bindName():string {
 * 	return $this->forename . " " . $this->surname;
 * }
 */
interface BindObject {}
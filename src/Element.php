<?php
namespace Gt\DomTemplate;

use Gt\Dom\Element as BaseElement;

class Element extends BaseElement {
	use TemplateParent;
	use Bindable;
	use NonDocumentTypeChildNode;
	use ChildNode;
	use ParentNode;
}
<?php
namespace Gt\DomTemplate\Test\TestFactory;

use Gt\Dom\Facade\HTMLDocumentFactory;
use Gt\Dom\Facade\XMLDocumentFactory;
use Gt\Dom\HTMLDocument;
use Gt\Dom\XMLDocument;

class DocumentTestFactory {
	const HTML_NO_BIND_PROPERTY = <<<HTML
<!doctype html>
<output data-bind>Nothing is bound</output>
HTML;

	const HTML_SINGLE_ELEMENT = <<<HTML
<!doctype html>
<output data-bind:text>Nothing is bound</output>
HTML;

	const HTML_SYNONYMOUS_BIND_PROPERTIES = <<<HTML
<!doctype html>
<output id="o1" data-bind:text>Nothing is bound</output>
<output id="o2" data-bind:textContent>Nothing is bound</output>
<output id="o3" data-bind:text-content>Nothing is bound</output>
<output id="o4" data-bind:innerText>Nothing is bound</output>
<output id="o5" data-bind:inner-text>Nothing is bound</output>

<output id="o6" data-bind:html>Nothing is bound</output>
<output id="o7" data-bind:HTML>Nothing is bound</output>
<output id="o8" data-bind:innerHTML>Nothing is bound</output>
<output id="o9" data-bind:inner-html>Nothing is bound</output>
HTML;


	const HTML_MULTIPLE_ELEMENTS = <<<HTML
<!doctype html>
<output id="o1" data-bind:text>First default</output>
<output id="o2" data-bind:text>Second default</output>
<output id="o3" data-bind:text>Third default</output>
HTML;

	const HTML_MULTIPLE_NESTED_ELEMENTS = <<<HTML
<!doctype html>
<div id="container1">
	<output id="o1" data-bind:text>First default</output>
	<output id="o2" data-bind:text>Second default</output>
	<output id="o3" data-bind:text>Third default</output>
</div>
<div id="container2">
	<output id="o4" data-bind:text>Fourth default</output>
	<output id="o5" data-bind:text>Fifth default</output>
	<output id="o6" data-bind:text>Sixth default</output>
</div>
<div id="container3">
	<h1 data-bind:text="title">Default title</h1>
	<output id="o7" data-bind:text>Seventh default</output>
	<p>
		You have just bound the <span data-bind:text="title">default title</span> title!
	</p>
</div>
HTML;

	const HTML_USER_PROFILE = <<<HTML
<!doctype html>
<h1>User profile</h1>
<dl>
	<dt>Username</dt>
	<dd id="dd1" data-bind:text="username">username123</dd>
	<dt>Email address</dt>
	<dd id="dd2" data-bind:text="email">you@example.com</dd>
	<dt>Category</dt>
	<dd id="dd3" data-bind:text="category">N/A</dd>
</dl>

<h2>Audit trail</h2>
<div id="audit-trail">
	<p>The following activity has been recorded on your account:</p>
	
	<ul></ul>
</div>
HTML;

	const HTML_DIFFERENT_BIND_PROPERTIES = <<<HTML
<!doctype html>
<img id="img1" class="main" src="/default.png" alt="Not bound" 
	data-bind:src="photoURL" 
	data-bind:alt="altText" 
	data-bind:class="size" />

<img id="img2" class="secondary" src="/default.png" alt="Not bound"
	data-bind:class=":is-selected" />

<img id="img3" class="secondary" src="/default.png" alt="Not bound"
	data-bind:class=":isSelected selected-image" />

<p id="p1" data-params="funny friendly" data-bind:data-params=":isMagic magical">Is this paragraph magical?</p>

<form id="form1">
	<button id="btn1" data-bind:disabled="?isBtn1Disabled" />
	<button id="btn2" data-bind:disabled="?!isBtn2Enabled" />
</form>
HTML;


	public static function createHTML(string $html = ""):HTMLDocument {
		return HTMLDocumentFactory::create($html);
	}

	public static function createXML(string $xml):XMLDocument {
		return XMLDocumentFactory::create($xml);
	}
}

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

	const HTML_TABLES = <<<HTML
<!doctype html>
<table id="tbl1" data-bind:table="tableData"></table>

<table id="tbl2" data-bind:table="tableData">
	<thead>
		<tr>
			<th data-table-key="firstName">First name</th>
			<th data-table-key="lastName">Last name</th>
			<th data-table-key="email">Email address</th>
		</tr>	
	</thead>
	<tbody>
		<tr>
<!-- This row already exists in the HTML and should be kept when new data is bound -->
			<td>Greg</td>
			<td>Bowler</td>
			<td>greg@php.gt</td>
		</tr>
	</tbody>
</table>

<div id="multi-table-container">
	<section id="s1">
		<p>First table:</p>
		<table data-bind:table="tableData"></table>	
	</section>
	
	<section id="s2">
		<p>Second table (different data):</p>
		<table data-bind:table="tableData2"></table>	
	</section>
	
	<section id="s3">
		<p>Third table (same data):</p>
		<table data-bind:table="tableData"></table>	
	</section>
</div>
HTML;
	const HTML_NO_TABLE = <<<HTML
<!doctype html>
<div data-bind:table="tableData">
	<p>There's no table in here, mate.</p>
</div>
HTML;


	public static function createHTML(string $html = ""):HTMLDocument {
		return HTMLDocumentFactory::create($html);
	}

	public static function createXML(string $xml):XMLDocument {
		return XMLDocumentFactory::create($xml);
	}
}

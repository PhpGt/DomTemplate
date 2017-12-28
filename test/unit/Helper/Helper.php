<?php
namespace Gt\DomTemplate\Test\Helper;

class Helper {
	const HTML_NO_TEMPLATES = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has no templates</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		<p>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. A aliquam animi 
			deleniti distinctio dolore doloremque, eius et facilis iure maiores nihil 
			nisi, nostrum optio perferendis perspiciatis, rerum vitae voluptates.
		</p>
		<p class="bound-data-test">
			My name is <span data-bind:text="name">Example</span> 
			and I am <span data-bind:text="age">0</span> years old. 	
		</p>
	</section>
</main>
HTML;

	const HTML_NO_TEMPLATES_BIND_ATTR = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has no templates but does have bound attributes</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		<p>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. A aliquam animi 
			deleniti distinctio dolore doloremque, eius et facilis iure maiores nihil 
			nisi, nostrum optio perferendis perspiciatis, rerum vitae voluptates.
		</p>
		<p class="bound-data-test">
			My name is <span id="name" name="person_id" data-bind:text="@id">Example</span> 
			and I am <span name="age" data-bind:text="@name">0</span> years old. 	
		</p>
	</section>
</main>
HTML;


	const HTML_TEMPLATES = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has some templates</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		<ul>
			<li data-template="list-item">This is a list item</li>
		</ul>
		<p>
			Above this paragraph is an unordered list, with the LI elements templated.
		</p>
		<dl>
			<template id="title-definition">
				<dt data-bind:text="title">Title</dt>
				<dd data-bind:text="definition">Definition</dd>
			</template>
		</dl>
		<p>
			Above this paragraph is a definition list, with a template element wrapping the elements to extract.
		</p>
		<p>
			Below is an ordered list with the LI elements templated.		
		</p>
		<ol>
			<li data-template="ordered-list-item">This is an item in the second list</li>		
		</ol>
	</section>
</main>
HTML;

	const HTML_COMPONENTS = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has some components</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		<ul>
			<li data-template="list-item">This is a list item</li>
		</ul>
		<p>
			Above this paragraph is an unordered list, with the LI elements templated.
		</p>
		
		<title-definition-list></title-definition-list>
		
		<p>
			Above this paragraph is a custom component, which has a nested component within it.
		</p>
		<p>
			Below is another custom component.		
		</p>
		<ordered-list></ordered-list>
	</section>
</main>
HTML;

	const COMPONENT_TITLE_DEFINITION_LIST = <<<HTML
<dl>
	<title-definition data-template="title-definition-item"></title-definition>
</dl>
HTML;

	const COMPONENT_TITLE_DEFINITION = <<<HTML
<dt data-template-text="@title">Title</dt>
<dd data-template-text="@definition">Definition</dd>
HTML;

	const COMPONENT_TITLE_ORDERED_LIST = <<<HTML
<ol>
	<li data-template="ordered-list-item">This is an item in the second list</li>		
</ol>
HTML;

	const HTML_BIND_UNKNOWN_PROPERTY = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has no templates</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		<p>
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. A aliquam animi 
			deleniti distinctio dolore doloremque, eius et facilis iure maiores nihil 
			nisi, nostrum optio perferendis perspiciatis, rerum vitae voluptates.
		</p>
		<p class="bound-data-test">
			My name is <span data-bind:unknown="name">Example</span> 
			and I am <span data-bind:text="age">0</span> years old. 	
		</p>
	</section>
</main>
HTML;


	const HTML_TODO_LIST = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template>
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;


}
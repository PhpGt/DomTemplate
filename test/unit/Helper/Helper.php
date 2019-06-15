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

	const HTML_COMPONENTS_WITH_NAMED_TEMPLATE = <<<HTML
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
		
		<title-definition-list data-template="tdlist"></title-definition-list>
		
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

	const HTML_COMPONENT_WITH_CLASS = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has some components</title>
<main>
	<section>
		<h1>Hello, World!</h1>
		
		<title-definition-list class="source-class"></title-definition-list>
		
		<p>
			Above this paragraph is a custom component, which has a nested component within it.
		</p>
	</section>
</main>
HTML;

	const HTML_COMPONENT_WITH_CLASS_ON_PARENT = <<<HTML
<li class="existing-class">This is an item in the list</li>
HTML;

	const HTML_TEMPLATE_WITH_NESTED_COMPONENT = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has a template with a component inside</title>
<main>
	<section>
		<outer-nested-thing></outer-nested-thing>	
	</section>
</main>
HTML;

	const COMPONENT_OUTER_NESTED_THING = <<<HTML
<ul>
	<li data-template="inner-template-item">
		<inner-nested-thing></inner-nested-thing>			
	</li>		
</ul>
HTML;

	const COMPONENT_INNER_NESTED_THING = <<<HTML
<p>These two paragraphs are contained within a component, within a template!</p>
<p>The iteration number is: <span class="number">X</span></p>
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

	const COMPONENT_ORDERED_LIST = <<<HTML
<ol>
	<ordered-list-item></ordered-list-item>		
</ol>
HTML;
	const COMPONENT_ORDERED_LIST_ITEM = <<<HTML
<li>This is an item in the list</li>
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
			My name is <span class="test1" data-bind:unknown="name">Example</span> 
			and I am <span class="test2" data-bind:text="age">0</span> years old. 	
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

	const HTML_TODO_LIST_INLINE_NAMED_TEMPLATE = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template="todo-list-item">
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_INLINE_NAMED_TEMPLATE_DOUBLE = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template="todo-list-item">
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
	
	<ul id="todo-list-2">
		<li data-template>
			<p>Use the other template instead!</p>
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_OPTIONAL_ID = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template>
			<input name="id" data-bind:value="?id" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_OPTIONAL_ID_REFERENCED = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template>
			<input name="id" data-bind:value="?@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_BIND_CLASS = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template data-bind:class="complete" class="existing-class">
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_BIND_CLASS_COLON = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template data-bind:class="dateTimeCompleted:complete" class="existing-class">
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_TODO_LIST_BIND_CLASS_COLON_MULTIPLE = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>Todo list</title>
<main>
	<ul id="todo-list">
		<li data-template data-bind:class="dateTimeCompleted:complete dateTimeDeleted:deleted" class="existing-class">
			<input name="id" data-bind:value="@name" />
			<input name="title" data-bind:value="@name" />
			<button name="do" value="complete">Complete</button>		
		</li>
	</ul>
</main>
HTML;

	const HTML_PARENT_HAS_DATA_BIND_ATTR = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has a parent element with a data bind attribute</title>
<main>
	<div class="parent">
		<label>
			<span data-bind:text="outside-scope">This node is outside the scope</span>
	
			<ul>
				<li data-template="target-template">
					<span data-bind:text="target-key">This node is the target</a>
				</li>
			</ul>
		</label>
	</div>
</main>
HTML;

	const HTML_DOUBLE_BINDABLE_LIST = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has a two bindable lists and other bindable areas</title>
<main>
	<h1>Test document name: <span data-bind:text="name">name goes here</span></h1>
	
	<div class="area-1">
		<p>Here is a list of numbers from 1 to 10:</p>
		
		<ul>
			<li data-template>
				<span data-bind:text="number">N</span>
			</li>		
		</ul>
	</div>
	
	<div class="area-2">
		<p>Here is another list, starting from <span data-bind:text="start">X</span></p>
		
		<ul>
			<li data-template="dynamic-list-item">
				<span data-bind:text="number">N</span>
			</li>		
		</ul>
	</div>
</main>
HTML;

	const HTML_DOUBLE_NAMELESS_BIND_LIST = <<<HTML
<!doctype html>
<main>
	<h1>List of totalitarian superstates:</h1>
	<ul id="list-1">
		<li data-template data-bind:text="state-name"></li> 
	</ul>

	<h1>Ministries of Oceana:</h1>
	<ul id="list-2">
		<li data-template data-bind:text="ministry-name"></li>
	</ul>
</main>
HTML;


	const HTML_ATTRIBUTE_PLACEHOLDERS = <<<HTML
<!doctype html>
<meta charset="utf-8" />
<title>This document has some elements with attribute placeholders</title>
<main>
	<h1>This is a test!</h1>
	<p><a id="userType-{userType}" href="/user/{userId}">View your account</a></p>
	
	<p>You are logged in.</p>
	<p>This is your profile picture:</p>
	
	<img src="/img/profile/{userId}.jpg" alt="{username}'s profile picture" />
</main>
HTML;

}
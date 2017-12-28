# Bind dynamic data to reusable HTML components.

Built on top of [PHP.Gt/Dom][dom], this project provides simple view templating and dynamic data binding.

Directly manipulating the DOM in your code can lead to tightly coupling the logic and view. Binding data using custom elements and data attributes leads to highly readable, maintainable view files that are loosely coupled to the application logic.  

***

<a href="https://circleci.com/gh/PhpGt/DomTemplate" target="_blank">
	<img src="https://img.shields.io/circleci/project/PhpGt/DomTemplate/master.svg?style=flat-square" alt="Build status" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/DomTemplate" target="_blank">
	<img src="https://img.shields.io/scrutinizer/g/PhpGt/DomTemplate/master.svg?style=flat-square" alt="Code quality" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/DomTemplate" target="_blank">
	<img src="https://img.shields.io/scrutinizer/coverage/g/PhpGt/DomTemplate/master.svg?style=flat-square" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/DomTemplate" target="_blank">
	<img src="https://img.shields.io/packagist/v/PhpGt/DomTemplate.svg?style=flat-square" alt="Current version" />
</a>
<a href="http://www.php.gt/domtemplate" target="_blank">
	<img src="https://img.shields.io/badge/docs-www.php.gt/domtemplate-26a5e3.svg?style=flat-square" alt="PHP.G/DomTemplate documentation" />
</a>

## Example usage: Bind dynamic data to a template element

Consider a page with an unordered list (`<ul>`). Within the list there should be a list item (`<li>`) for every element of an array of data.

In this example, we will simply use an array to contain the data, but the data can come from a data source such as a database.

### Source HTML (`example.html`)

```html
<!doctype html>
<h1>Shopping list</h1>

<shopping-list></shopping-list>

<p>The use of a custom element is more useful on more complex pages, but is shown here as an example.</p>
```

### Custom element HTML (`_template/shopping-list.html`)

```html
<ul id="shopping-list">
	<li data-template data-bind:text="title">Item name</li>
</ul>
```

### PHP used to inject the list

```php
<?php
require "vendor/autoload.php";

$html = file_get_contents("example.html");
$document = new \Gt\DomTemplate\HTMLDocument($html, "./_template");
$document->prepareTemplates();

$data = [
	["id" => 1, "title" => "Tomatoes"],
	["id" => 2, "title" => "Noodles"],
	["id" => 3, "title" => "Cheese"],
	["id" => 4, "title" => "Broccoli"],
];

$outputTo = $document->getElementById("shopping-list");
$outputTo->bind($data);
```

### Output:

```html
<!doctype html>
<h1>Shopping list</h1>

<ul id="shopping-list">
	<li>Tomatoes</li>
	<li>Noodles</li>
	<li>Choose</li>
	<li>Broccoli</li>
</ul>
```

[dom]: https://www.php.gt/dom
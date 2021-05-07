# Bind dynamic data to reusable HTML components.

Built on top of [PHP.Gt/Dom][dom], this project provides dynamic data binding to DOM Documents, document templating and reusable HTML components.

Directly manipulating the DOM in your code can lead to tightly coupling the logic and view. Binding data using custom elements and data attributes leads to highly readable, maintainable view files that are loosely coupled to the application logic.  

***

<a href="https://github.com/PhpGt/DomTemplate/actions" target="_blank">
	<img src="https://badge.status.php.gt/domtemplate-build.svg" alt="Build status" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/DomTemplate" target="_blank">
	<img src="https://badge.status.php.gt/domtemplate-quality.svg" alt="Code quality" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/DomTemplate" target="_blank">
	<img src="https://badge.status.php.gt/domtemplate-coverage.svg" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/DomTemplate" target="_blank">
	<img src="https://badge.status.php.gt/domtemplate-version.svg" alt="Current version" />
</a>
<a href="http://www.php.gt/domtemplate" target="_blank">
	<img src="https://badge.status.php.gt/domtemplate-docs.svg" alt="PHP.G/DomTemplate documentation" />
</a>

## Example usage: Hello, you!

Consider a page with a form, with an input element to enter your name. When the form is submitted, the page should greet you by your name.

Without submitting the form, the HTML will be rendered untouched, showing the default message "Hello, you!".

### Source HTML (`name.html`)

```html
<!doctype html>
<h1>
	Hello, <span class="name-output">you</span>!
</h1>

<form>
	<input name="name" placeholder="Your name, please" required />
	<button>Submit</button>
</form>
```

### PHP used to inject your name (`name.php`)

```php
<?php
require "vendor/autoload.php";

$html = file_get_contents("name.html");
/** @var \Gt\Dom\HTMLDocument $document */
$document = \Gt\Dom\Facade\HTMLDocumentFactory::create($html);

if($name = $_GET["name"]) {
	$binder = new \Gt\DomTemplate\DocumentBinder($document);
	$binder->bindKeyValue("name-output", $name);
}

echo $document;
```


Features at a glance
--------------------

+ HTML components - organise and reuse DOM trees by storing separate components in their own HTML files, and including them using custom HTML tags.
+ Binding of dynamic data - bind key value pairs, associative arrays, lists of associative arrays and even nested lists.
+ Use standards compliant techniques for templates and components.
+ Easily style components using CSS by addressing their tag name (`shopping-list` in the above example).

[dom]: https://www.php.gt/dom
[example-groceries-html]: https://github.com/PhpGt/DomTemplate/blob/master/example/01-example-groceries.html

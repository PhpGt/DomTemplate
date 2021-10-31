<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindkeyvalue

$html = <<<HTML
<h1>User profile</h1>

<div>
	<h2 data-bind:text="username">Username</h2>
	<p>Full name: <span data-bind:text="fullName">Full Name</p>
	<p>Bio: <span data-bind:text="bio">Bio goes here</span></p>
</div>
HTML;

function example(DocumentBinder $binder):void {
	// In a real application, $data might be supplied from the database
	// and could contain model objects rather than associative arrays.
	$data = [
		"username" => "PhpNut",
		"fullName" => "Larry E. Masters",
		"bio" => "Christian - Dad - 4x Grandad - Co-Founder of @CakePHP - Developer - Open Source Advocate",
	];

	$binder->bindData($data);
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanBindAttributes();
echo $document;

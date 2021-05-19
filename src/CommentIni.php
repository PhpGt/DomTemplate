<?php
namespace Gt\DomTemplate;

use Gt\Dom\Comment;
use Gt\Dom\Document;
use Gt\Dom\NodeFilter;
use Throwable;

class CommentIni {
	/** @var array<string, array<string, string>|string>|null */
	private ?array $iniData;

	public function __construct(
		private Document $document
	) {
		$walker = $document->createTreeWalker(
			$document,
			NodeFilter::SHOW_COMMENT
		);

		$ini = null;

		while($commentNode = $walker->nextNode()) {
			/** @var Comment $commentNode */
			$data = trim($commentNode->data);

			try {
				$ini = parse_ini_string($data, true);
			}
			catch(Throwable) {
				$ini = null;
			}
			if(!$ini) {
				break;
			}

// At this point, the ini has successfully parsed.
			$context = $commentNode;
			while($context = $context->previousSibling) {
				if(trim($context->textContent) !== "") {
					throw new CommentIniInvalidDocumentLocationException("A Comment INI must only appear as the first node of the HTML.");
				}
			}
		}

		$this->iniData = $ini;
	}

	public function get(string $variable):?string {
		$parts = explode(".", $variable);

		$var = $this->iniData;
		foreach($parts as $part) {
			$var = $var[$part] ?? null;
		}

		return $var;
	}

	public function containsIniData():bool {
		return !empty($this->iniData);
	}
}

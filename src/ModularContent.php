<?php
namespace Gt\DomTemplate;

class ModularContent {
	public function __construct(
		private string $dirPath
	) {
		if(!is_dir($this->dirPath)) {
			throw new ModularContentDirectoryNotFoundException("The modular content path does not exist: $this->dirPath");
		}
	}

	public function getContent(string $name, string $extension = "html"):string {
		$filePath = $this->dirPath . "/" . $name . ".$extension";
		if(!is_file($filePath)) {
			throw new ModularContentFileNotFoundException("The modular content file does not exist: $filePath");
		}

		return file_get_contents($filePath);
	}
}

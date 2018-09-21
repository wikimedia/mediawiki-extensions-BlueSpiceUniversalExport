<?php

namespace BlueSpice\UniversalExport;

use BlueSpice\UniversalExport\IExportFileDescriptor;

class LegacyArrayDescriptor implements IExportFileDescriptor {

	private $data = [];

	public function __construct( $data ) {
		$this->data = $data;
	}

	public function getContents() {
		return $this->data['content'];
	}

	public function getFilename() {
		return $this->data['filename'];
	}

	public function getMimeType() {
		return $this->data['mime-type'];
	}

}
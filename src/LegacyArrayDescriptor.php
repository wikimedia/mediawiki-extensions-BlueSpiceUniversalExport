<?php

namespace BlueSpice\UniversalExport;

class LegacyArrayDescriptor implements IExportFileDescriptor {

	/**
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 *
	 * @return string
	 */
	public function getContents() {
		return $this->data['content'];
	}

	/**
	 *
	 * @return string
	 */
	public function getFilename() {
		return $this->data['filename'];
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		return $this->data['mime-type'];
	}

}

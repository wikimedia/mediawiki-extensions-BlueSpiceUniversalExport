<?php

namespace BlueSpice\UniversalExport;

interface IExportFileDescriptor {

	/**
	 * @return string
	 */
	public function getMimeType();

	/**
	 * @return string
	 */
	public function getFilename();

	/**
	 * E.g. results from file_get_contents
	 * @return string
	 */
	public function getContents();
}
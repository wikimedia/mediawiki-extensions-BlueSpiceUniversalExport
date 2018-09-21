<?php

namespace BlueSpice\UniversalExport;

interface IExportTarget {

	/**
	 * @param IExportFileDescriptor $descriptor
	 * @return \Status
	 */
	public function execute( $descriptor );
}
<?php

namespace BlueSpice\UniversalExport;

use MediaWiki\Status\Status;

interface IExportTarget {

	/**
	 * @param IExportFileDescriptor $descriptor
	 * @return Status
	 */
	public function execute( $descriptor );
}

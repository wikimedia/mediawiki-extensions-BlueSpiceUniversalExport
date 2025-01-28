<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use BlueSpice\UniversalExport\IExportFileDescriptor;
use MediaWiki\Status\Status;

class LocalFileSystem extends Base {

	/**
	 *
	 * @var IExportFileDescriptor
	 */
	protected $descriptor = null;

	/**
	 *
	 * @var Status
	 */
	private $status = null;

	/**
	 *
	 * @param IExportFileDescriptor $descriptor
	 * @return Status
	 */
	public function execute( $descriptor ) {
		$this->descriptor = $descriptor;

		$this->status = Status::newGood();

		$filename = $descriptor->getFilename();
		if ( isset( $this->exportParams['target-file-name'] ) ) {
			$filename = $this->exportParams['target-file-name'];
		}
		$targetFilePath = $this->exportParams[ 'target-file-path' ];

		$fullPath = $targetFilePath . '/' . $filename;
		$tmpFilepath = $fullPath;
		$result = file_put_contents( $tmpFilepath, $this->descriptor->getContents() );

		if ( $result === false ) {
			return Status::newFatal( 'Failed to save the file to ' . $fullPath );
		}

		return Status::newGood( $fullPath );
	}
}

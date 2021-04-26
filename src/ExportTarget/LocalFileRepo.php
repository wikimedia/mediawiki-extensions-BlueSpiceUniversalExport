<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use BlueSpice\UniversalExport\IExportFileDescriptor;
use Status;

class LocalFileRepo extends RequestBasedTarget {

	private $targetUrl = '';

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

		$targetFilename = $descriptor->getFilename();
		if ( !empty( $this->exportParams[ 'target-filename' ] ) ) {
			$targetFilename = $this->exportParams[ 'target-filename' ];
		}

		$tmpFilepath = wfTempDir() . '/' . $targetFilename;
		file_put_contents( $tmpFilepath, $this->descriptor->getContents() );

		$user = $this->context->getUser();
		$this->status =
			\BsFileSystemHelper::uploadLocalFileWithUser( $tmpFilepath, $user, true );

		$this->targetUrl = $this->status->getValue();

		$this->getContext()->getOutput()->redirect( $this->targetUrl );

		return $this->status;
	}
}

<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use BlueSpice\UniversalExport\IExportFileDescriptor;
use Status;

class Download extends Base {

	/**
	 *
	 * @param IExportFileDescriptor $descriptor
	 */
	public function execute( $descriptor ) {
		$this->context->getOutput()->disable();
		$resonse = $this->context->getRequest()->response();

		$resonse->header( 'Pragma: public' );
		$resonse->header( 'Expires: 0' );
		$resonse->header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		$resonse->header( 'Cache-Control: public' );
		$resonse->header( 'Content-Description: File Transfer' );
		$resonse->header( 'Content-Type: '.$descriptor->getMimeType() );
		$resonse->header( 'Content-Disposition: attachment; filename="'.$descriptor->getFilename().'"' );
		$resonse->header( 'Content-Transfer-Encoding: binary' );

		// TODO: This is old, bad code. Find a proper way to write to the
		// response body in context of a SpecialPage
		echo $descriptor->getContents();

		return Status::newGood();
	}

}
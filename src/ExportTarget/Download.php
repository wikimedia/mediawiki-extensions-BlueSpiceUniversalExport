<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use BlueSpice\UniversalExport\IExportFileDescriptor;
use MediaWiki\Status\Status;

class Download extends RequestBasedTarget {
	/**
	 *
	 * @param IExportFileDescriptor $descriptor
	 * @return Status
	 */
	public function execute( $descriptor ) {
		$this->getContext()->getOutput()->disable();
		$response = $this->getContext()->getRequest()->response();

		$response->header( 'Pragma: public' );
		$response->header( 'Expires: 0' );
		$response->header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		$response->header( 'Cache-Control: public' );
		$response->header( 'Content-Description: File Transfer' );
		$response->header( 'Content-Type: ' . $descriptor->getMimeType() );
		$response->header(
			"Content-Disposition: attachment; filename=\"{$descriptor->getFilename()}\""
		);
		$response->header( 'Content-Transfer-Encoding: binary' );
		$response->header( 'X-Robots-Tag: noindex' );

		// TODO: This is old, bad code. Find a proper way to write to the
		// response body in context of a SpecialPage
		echo $descriptor->getContents();

		return Status::newGood();
	}

}

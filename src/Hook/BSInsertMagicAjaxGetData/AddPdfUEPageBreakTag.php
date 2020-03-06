<?php

namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddPdfUEPageBreakTag extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:uepagebreak',
			'type' => 'tag',
			'name' => 'pdfpagebreak',
			'desc' => $this->msg( 'bs-universalexport-tag-pagebreak-text' )->text(),
			'code' => '<bs:uepagebreak />',
			'previewable' => false,
			'mwvecommand' => 'pdfPageBreakCommand',
			'helplink' => $this->getHelpLink()
		];

		return true;
	}

	/**
	 *
	 * @return string
	 */
	private function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceUniversalExport' )->getUrl();
	}

}

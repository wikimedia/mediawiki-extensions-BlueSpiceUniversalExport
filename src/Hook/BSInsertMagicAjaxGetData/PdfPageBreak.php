<?php

namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class PdfPageBreak extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:uepagebreak';
		$descriptor->type = 'tag';
		$descriptor->name = 'pdfpagebreak';
		$descriptor->desc = wfMessage( 'bs-universalexport-tag-pagebreak-text' )->escaped();
		$descriptor->code = '<bs:uepagebreak />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'pdfPageBreakCommand';
		$descriptor->helplink = $this->getServices()->getBSExtensionFactory()
			->getExtension( 'BlueSpiceUniversalExport' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}

<?php
namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class PdfNoExport extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:uenoexport';
		$descriptor->type = 'tag';
		$descriptor->name = 'pdfnoexport';
		$descriptor->desc = wfMessage( 'bs-universalexport-tag-noexport-desc' )->escaped();
		$descriptor->code = '<bs:uenoexport></bs:uenoexport>';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'pdfNoExportCommand';
		$descriptor->helplink = $this->getServices()->getBSExtensionFactory()
			->getExtension( 'BlueSpiceUniversalExport' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}

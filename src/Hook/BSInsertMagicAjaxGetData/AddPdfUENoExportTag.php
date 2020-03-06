<?php

namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddPdfUENoExportTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:uenoexport',
			'type' => 'tag',
			'name' => 'uenoexport',
			'desc' => $this->msg( 'bs-universalexport-tag-noexport-desc' )->text(),
			'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>',
			'examples' => [ [
				'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>'
			] ],
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

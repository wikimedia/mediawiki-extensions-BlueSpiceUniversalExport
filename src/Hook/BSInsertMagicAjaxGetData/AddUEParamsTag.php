<?php

namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddUEParamsTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:ueparams',
			'type' => 'tag',
			'name' => 'ueparams',
			'desc' => $this->msg( 'bs-universalexport-tag-params-desc' )->text(),
			'code' => '<bs:ueparams someParam="Some Value" />',
			'examples' => [ [
				'code' => '<bs:ueparams template="BlueSpice Landscape" />'
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

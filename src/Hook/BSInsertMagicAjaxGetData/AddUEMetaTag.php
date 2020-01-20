<?php

namespace BlueSpice\UniversalExport\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddUEMetaTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:uemeta',
			'type' => 'tag',
			'name' => 'uemeta',
			'desc' => $this->msg( 'bs-universalexport-tag-meta-des' )->text(),
			'code' => '<bs:uemeta someMeta="Some Value" />',
			'examples' => [ [
				'code' => '<bs:uemeta department="IT" security="high" />'
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
		return $this->getServices()->getBSExtensionFactory()
			->getExtension( 'BlueSpiceUniversalExport' )->getUrl();
	}

}

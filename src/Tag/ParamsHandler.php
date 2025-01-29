<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;

class ParamsHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-universalexport-params', 1 );
		$this->parser->getOutput()->setPageProperty(
			'bs-universalexport-params',
			FormatJson::encode( $this->processedArgs )
		);
		$attribs = array_merge(
			$this->processedArgs,
			[ 'class' => 'bs-universalexport-params' ]
		);

		return Html::element( 'div', $attribs );
	}
}

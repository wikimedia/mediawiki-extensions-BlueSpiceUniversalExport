<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use FormatJson;
use Html;

class ParamsHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-universalexport-params', 1 );
		$this->parser->getOutput()->setProperty(
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

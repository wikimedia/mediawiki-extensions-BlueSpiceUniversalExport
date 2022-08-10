<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use FormatJson;
use Html;

class MetaHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-universalexport-meta', 1 );
		$this->parser->getOutput()->setPageProperty(
			'bs-universalexport-meta',
			FormatJson::encode( $this->processedArgs )
		);
		$attribs = array_merge(
			$this->processedArgs,
			[ 'class' => 'bs-universalexport-meta' ]
		);

		return Html::element( 'div', $attribs );
	}
}

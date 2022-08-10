<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;

class HideTitleHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-universalexport-hidetitle', 1 );
		$this->parser->getOutput()->setPageProperty( 'bs-universalexport-hidetitle', true );

		return '';
	}
}

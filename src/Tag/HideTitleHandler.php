<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;

class HideTitleHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-universalexport-hidetitle', 1 );
		$this->parser->getOutput()->setProperty( 'bs-universalexport-hidetitle', true );

		return '';
	}
}

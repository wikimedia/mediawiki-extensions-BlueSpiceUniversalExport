<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use Html;
use Message;

class PageBreakHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-universalexport-pagebreak', 1 );

		return Html::rawElement(
			'div',
			[ 'class' => 'bs-universalexport-pagebreak' ],
			Message::newFromKey( 'bs-universalexport-tag-pagebreak-text' )->plain()
		);
	}
}

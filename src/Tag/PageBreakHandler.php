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

		// TODO RBV (08.02.11 11:34): Use CSS class for styling
		$style = "border-top: 2px dotted #999; background-color: #F5F5F5;"
			. "color: #BBB; font-style: italic; text-align: center;";

		return Html::rawElement(
			'div',
			[ 'style' => $style ],
			Message::newFromKey( 'bs-universalexport-tag-pagebreak-text' )->plain()
		);
	}
}

<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use Html;
use Message;

class NoExportHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setProperty( 'bs-tag-universalexport-exclude', 1 );
		$msg = Message::newFromKey( 'bs-universalexport-tag-exclude-text' )->plain();
		return Html::rawElement(
			'div',
			[ 'class' => 'bs-universalexport-exportexclude', 'title' => $msg ],
			$this->parser->recursiveTagParse( $this->processedInput )
		);
	}
}

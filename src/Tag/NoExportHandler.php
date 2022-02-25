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
		$this->processedInput = $this->parser->recursiveTagParseFully( $this->processedInput );
		$matches = [];

		preg_match( '/^<p>(.*?)\\n<\/p>$/', $this->processedInput, $matches );
		$inline = !empty( $matches );
		$elementName = $inline ? 'span' : 'div';

		return Html::rawElement(
			$elementName,
			[ 'class' => 'bs-universalexport-exportexclude', 'title' => $msg ],
			$inline ? $matches[1] : $this->processedInput
		);
	}
}

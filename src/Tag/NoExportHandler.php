<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\Handler;
use Html;
use MediaWiki\Message\Message;

class NoExportHandler extends Handler {

	/**
	 * @return string
	 */
	public function handle() {
		$this->parser->getOutput()->setPageProperty( 'bs-tag-universalexport-exclude', 1 );
		$msg = Message::newFromKey( 'bs-universalexport-tag-exclude-text' )->plain();
		$this->processedInput = $this->parser->recursiveTagParseFully(
			$this->processedInput,
			$this->frame
		);
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

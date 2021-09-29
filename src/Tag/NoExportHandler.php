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
		$elementName = $this->isBlocklevelInput() ? 'div' : 'span';
		$msg = Message::newFromKey( 'bs-universalexport-tag-exclude-text' )->plain();
		return Html::rawElement(
			$elementName,
			[ 'class' => 'bs-universalexport-exportexclude', 'title' => $msg ],
			$this->processedInput
		);
	}

	private function isBlocklevelInput() {
		// Wikitext parapraphs
		if ( strpos( $this->processedInput, "\n\n" ) !== false ) {
			return true;
		}

		// Wikitext unordered list
		if ( strpos( $this->processedInput, "\n*" ) !== false ) {
			return true;
		}

		// Wikitext ordered list
		if ( strpos( $this->processedInput, "\n#" ) !== false ) {
			return true;
		}

		// Wikitext definition list
		if ( strpos( $this->processedInput, "\n;" ) !== false ) {
			return true;
		}

		// Wikitext text indention
		if ( strpos( $this->processedInput, "\n:" ) !== false ) {
			return true;
		}

		// Wikitext table
		if ( strpos( $this->processedInput, "\n{|" ) !== false ) {
			return true;
		}

		// Wikitext preformatted text
		if ( strpos( $this->processedInput, "\n " ) !== false ) {
			return true;
		}

		// Wikitext heading
		if ( strpos( $this->processedInput, "\n=" ) !== false ) {
			return true;
		}

		// HTML div
		if ( preg_match( "#<div.*?>#", $this->processedInput ) === 1 ) {
			return true;
		}

		// HTML heading
		if ( preg_match( "#<h\d.*?>#", $this->processedInput ) === 1 ) {
			return true;
		}

		// More analysis required? Actually we'd probably need to test on the parsed content!

		return false;
	}
}

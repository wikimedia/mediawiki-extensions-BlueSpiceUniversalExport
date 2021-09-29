<?php

namespace BlueSpice\UniversalExport\Tag;

use BlueSpice\Tag\MarkerType;
use BlueSpice\Tag\MarkerType\None;
use BlueSpice\Tag\Tag;
use Parser;
use PPFrame;

class NoExport extends Tag {

	/**
	 *
	 * @return bool
	 */
	public function needsParsedInput() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function needsParseArgs() {
		return false;
	}

	/**
	 *
	 * @return MarkerType
	 */
	public function getMarkerType() {
		return new None();
	}

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return NoExportHandler
	 */
	public function getHandler( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame ) {
		return new NoExportHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	/**
	 *
	 * @return string[]
	 */
	public function getTagNames() {
		return [
			'nopdf',
			'universalexport:exclude',
			'bs:universalexport:exclude',
			'universalexport:noexport',
			'bs:universalexport:noexport',
			'bs:uenoexport'
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getResourceLoaderModuleStyles(): array {
		return [
			'ext.bluespice.universalExport.css'
		];
	}

	/**
	 *
	 * @return void
	 */
	public function getContainerElementName() {
		return '';
	}
}

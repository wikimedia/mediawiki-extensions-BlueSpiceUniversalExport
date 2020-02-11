<?php
/**
 * The TagLibrary of the UniversalExport Extension.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * UniversalExport TagLibrary class.
 * @package BlueSpiceUniversalExport
 */
class BsUniversalExportTagLibrary {
	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Registers
	 * TagExtensions within the Parser.
	 * @param Parser &$oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public static function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook(
			'universalexport:params',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		$oParser->setHook(
			'bs:universalexport:params',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		$oParser->setHook(
			'bs:ueparams',
			'BsUniversalExportTagLibrary::onParamsTag'
		);
		return true;
	}

	/**
	 *
	 * @param string $sContent
	 * @param array $aAttributes
	 * @param Parser $oParser
	 * @return string
	 */
	public static function onParamsTag( $sContent, $aAttributes, $oParser ) {
			$oParser->getOutput()->setProperty( 'bs-tag-universalexport-params', 1 );
			$oParser->getOutput()->setProperty(
			'bs-universalexport-params',
			json_encode( $aAttributes )
		);

		$aOut = [];
		$aOut[] = '<div class="bs-universalexport-params"';
		foreach ( $aAttributes as $sKey => $sValue ) {
			$aOut[] = ' ' . $sKey . '="' . $sValue . '"';
		}
		$aOut[] = '></div>';

		return implode( '', $aOut );
	}

}

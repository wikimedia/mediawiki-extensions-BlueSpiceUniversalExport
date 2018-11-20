<?php
/**
 * UniversalExport extension for BlueSpice
 *
 * Enables MediaWiki to export pages into different formats.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for UniversalExport extension
 * @package BlueSpiceUniversalExport
 */
class UniversalExport extends BsExtensionMW {

	/**
	 *  Initialization of UniversalExport extension
	 */
	protected function initExt() {
		//Hooks
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );
	}

	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Dispatches registration og TagExtensions to the TagLibrary.
	 * @param Parser $oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		return BsUniversalExportTagLibrary::onParserFirstCallInit( $oParser );
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		return BsUniversalExportTagLibrary::onBSUsageTrackerRegisterCollectors( $aCollectorsConfig );
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$extension = \BlueSpice\Services::getInstance()->getBSExtensionFactory()
			->getExtension( 'BlueSpiceUniversalExport' );
		$helplink = $extension->getUrl();

		$oResponse->result[] = array(
			'id' => 'bs:uemeta',
			'type' => 'tag',
			'name' => 'uemeta',
			'desc' => wfMessage( 'bs-universalexport-tag-meta-desc' )->plain(),
			'code' => '<bs:uemeta someMeta="Some Value" />',
			'examples' => array(
				array(
					'code' => '<bs:uemeta department="IT" security="high" />'
				)
			),
			'helplink' => $helplink
		);

		$oResponse->result[] = array(
			'id' => 'bs:ueparams',
			'type' => 'tag',
			'name' => 'ueparams',
			'desc' => wfMessage( 'bs-universalexport-tag-params-desc' )->plain(),
			'code' => '<bs:ueparams someParam="Some Value" />',
			'examples' => array(
				array(
					'code' => '<bs:ueparams template="BlueSpice Landscape" />'
				)
			),
			'helplink' => $helplink
		);

		$oResponse->result[] = array(
			'id' => 'bs:uepagebreak',
			'type' => 'tag',
			'name' => 'uepagebreak',
			'desc' => wfMessage( 'bs-universalexport-tag-pagebreak-desc' )->plain(),
			'code' => '<bs:uepagebreak />',
			'helplink' => $helplink
		);

		$oResponse->result[] = array(
			'id' => 'bs:uenoexport',
			'type' => 'tag',
			'name' => 'uenoexport',
			'desc' => wfMessage( 'bs-universalexport-tag-noexport-desc' )->plain(),
			'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>',
			'examples' => array(
				array(
					'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>'
				)
			),
			'helplink' => $helplink
		);

		return true;
	}
}

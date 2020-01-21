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
 * For further information visit https://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
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
		// Hooks
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
	}

	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Dispatches
	 * registration og TagExtensions to the TagLibrary.
	 * @param Parser &$oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		return BsUniversalExportTagLibrary::onParserFirstCallInit( $oParser );
	}

}

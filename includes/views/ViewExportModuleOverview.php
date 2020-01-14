<?php
/**
 * Renders the Overview of an ExportModule.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 *
 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-2.0-or-later
 * @filesource
 */

/**
 * This view renders the Overview of an ExportModule.
 * @package    BlueSpiceUniversalExport
 */
class ViewExportModuleOverview extends ViewBaseElement {

	/**
	 * Generates actually the output.
	 * @param mixed $params
	 * @return string The rendered HTML
	 */
	public function execute( $params = false ) {
		$aOut = [];

		$sId = !empty( $this->_mId ) ? ' id="' . $this->_mId . '"' : '';

		$aOut[] = '<div' . $sId . ' class="bs-universalexport-module">';
		$aOut[] = ' <h2 class="bs-universalexport-module-title">';
		$aOut[] = $this->mOptions['module-title'];
		$aOut[] = '</h2>';
		$aOut[] = ' <div class="bs-universalexport-module-description">';
		$aOut[] = $this->mOptions['module-description'];
		$aOut[] = '</div>';
		$aOut[] = ' <div class="bs-universalexport-module-body">';
		$aOut[] = '   <div class="bs-universalexport-module-bodycontent">';
		$aOut[] = $this->mOptions['module-bodycontent'];
		if ( $this->hasItems() ) {
			foreach ( $this->_mItems as $oItemView ) {
				$aOut[] = $oItemView->execute( $params );
			}
		}
		$aOut[] = '   </div>';
		$aOut[] = ' </div>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}
}

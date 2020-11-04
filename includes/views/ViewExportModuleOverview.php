<?php
/**
 * Renders the Overview of an ExportModule.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-2.0-or-later
 * @filesource
 */

use BlueSpice\UniversalExport\IExportModuleOverview;

/**
 * This view renders the Overview of an ExportModule.
 * @deprecated since version 3.3 - use own implementation of IExportModuleOverview
 * ViewBaseElement should not be used anymore!
 * @package    BlueSpiceUniversalExport
 */
class ViewExportModuleOverview extends ViewBaseElement implements IExportModuleOverview {

	/**
	 * Generates actually the output.
	 * @deprecated since version 3.3 - use own implementation of IExportModuleOverview
	 * ViewBaseElement should not be used anymore!
	 * @param mixed $params
	 * @return string The rendered HTML
	 */
	public function execute( $params = false ) {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
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

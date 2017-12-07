<?php
/**
 * The interface for an UniversalExport Module.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUniversalExport
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * UniversalExport Modue interface.
 * @package BlueSpiceUniversalExport
 */
interface BsUniversalExportModule {

	/*
	 * Creates a file, which can be returned in the HttpResponse
	 * @param SpecialUniversalExport $oCaller This object carries all needed information as public members
	 * @return array Associative array containing the file itself as well as the MIME-Type. I.e. array( 'mime-type' => 'text/html', 'content' => '<html>...' )
	 */
	public function createExportFile( &$oCaller );

	/**
	 * Creates a ViewExportModuleOverview to display on the SpecialUniversalExport page if no parameter is provided
	 * @return ViewExportModuleOverview
	 */
	public function getOverview();
}
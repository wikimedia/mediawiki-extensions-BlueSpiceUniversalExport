<?php

namespace BlueSpice\UniversalExport;

use MediaWiki\MediaWikiServices;
use SpecialUniversalExport;
use WebRequest;

interface IExportModule {
	/**
	 * Creates a file, which can be returned in the HttpResponse
	 * @param SpecialUniversalExport &$caller This object carries all needed
	 * information as public members
	 * @return array Associative array containing the file itself as well as the
	 * MIME-Type. I.e. array( 'mime-type' => 'text/html', 'content' => '<html>...' )
	 */
	public function createExportFile( &$caller );

	/**
	 * Creates a IExportModuleOverview to display on the SpecialUniversalExport
	 * page if no parameter is provided
	 * @return IExportModuleOverview
	 */
	public function getOverview();

	/**
	 * Get the link for exporting using this module
	 *
	 * @param WebRequest $request
	 * @param array|null $additional
	 * @return string
	 */
	public function getExportLink( WebRequest $request, array $additional = [] );

	/**
	 * Get the name of the module
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * @return MediaWikiServices
	 */
	public function getServices();

	/**
	 * Get permission required for export
	 *
	 * @return string|null if no permission is required
	 */
	public function getExportPermission();

	/**
	 * Handlers for special export sub-actions (subpages, recursive...)
	 *
	 * @return array
	 */
	public function getSubactionHandlers();

	/**
	 * Get the data for the action button
	 * [
	 * 		'title' => '',
	 * 		'text' => '',
	 * 		'iconClass' => ''
	 * ]
	 *
	 * @return array
	 */
	public function getActionButtonDetails();
}

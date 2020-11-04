<?php

namespace BlueSpice\UniversalExport;

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
}

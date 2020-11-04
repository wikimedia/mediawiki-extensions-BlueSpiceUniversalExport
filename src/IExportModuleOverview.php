<?php

namespace BlueSpice\UniversalExport;

interface IExportModuleOverview {

	/**
	 * @param mixed $params
	 * @return string
	 */
	public function execute( $params = false );
}

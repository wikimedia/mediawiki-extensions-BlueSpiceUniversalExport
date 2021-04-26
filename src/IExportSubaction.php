<?php

namespace BlueSpice\UniversalExport;

use WebRequest;

interface IExportSubaction {
	/**
	 * Whether this subaction is called
	 *
	 * @param ExportSpecification $specification
	 * @return bool
	 */
	public function applies( ExportSpecification $specification );

	/**
	 * Get permission required to execute this subaction
	 *
	 * @return bool
	 */
	public function getPermission();

	/**
	 * @param array &$template
	 * @param array &$contents
	 * @param ExportSpecification $specification
	 * @return bool
	 */
	public function apply( &$template, &$contents, $specification );

	/**
	 * Get the export module this is a subaction of
	 *
	 * @return IExportModule
	 */
	public function getMainModule();

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

	/**
	 * Get the link for exporting using this subaction
	 *
	 * @param WebRequest $request
	 * @param array|null $additional
	 * @return string
	 */
	public function getExportLink( WebRequest $request, array $additional = [] );
}

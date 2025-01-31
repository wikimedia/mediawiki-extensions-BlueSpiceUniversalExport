<?php

namespace BlueSpice\UniversalExport;

use MediaWiki\Context\IContextSource;

interface IExportDialogPlugin {

	/**
	 *
	 * @return array
	 */
	public function getRLModules(): array;

	/**
	 *
	 * @return array
	 */
	public function getJsConfigVars(): array;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function skip( IContextSource $context ): bool;
}

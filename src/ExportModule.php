<?php

namespace BlueSpice\UniversalExport;

use Config;
use MediaWiki\MediaWikiServices;

abstract class ExportModule implements IExportModule {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param Config $config
	 */
	protected function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @return IExportModule
	 */
	public static function factory( MediaWikiServices $services, Config $config ) {
		return new static( $config );
	}
}

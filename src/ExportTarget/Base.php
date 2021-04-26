<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use BlueSpice\UniversalExport\IExportTarget;

abstract class Base implements IExportTarget {

	/**
	 *
	 * @var array
	 */
	protected $exportParams = [];

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param array $exportParams
	 * @param \Config $config
	 * @return static
	 */
	public static function factory( $exportParams, $config ) {
		return new static( $exportParams, $config );
	}

	/**
	 *
	 * @param array $exportParams
	 * @param \Config $config
	 */
	public function __construct( $exportParams, $config ) {
		$this->exportParams = $exportParams;
		$this->config = $config;
	}

	/**
	 * @param array $descriptor
	 * @return \Status
	 */
	abstract public function execute( $descriptor );
}

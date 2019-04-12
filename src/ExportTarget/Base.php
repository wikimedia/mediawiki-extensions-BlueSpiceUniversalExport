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
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param array $exportParams
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @return static
	 */
	public static function factory( $exportParams, $context, $config ) {
		return new static( $exportParams, $context, $config );
	}

	/**
	 *
	 * @param array $exportParams
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( $exportParams, $context, $config ) {
		$this->exportParams = $exportParams;
		$this->context = $context;
		$this->config = $config;
	}

	/**
	 * @param array $descriptor
	 * @return \Status
	 */
	abstract public function execute( $descriptor );
}

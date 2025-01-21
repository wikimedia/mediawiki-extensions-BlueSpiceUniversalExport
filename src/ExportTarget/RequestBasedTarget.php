<?php

namespace BlueSpice\UniversalExport\ExportTarget;

use Config;
use IContextSource;
use MediaWiki\Context\RequestContext;

abstract class RequestBasedTarget extends Base {
	/** @var IContextSource */
	protected $context;

	/**
	 * @inheritDoc
	 */
	public static function factory( $exportParams, $config ) {
		return new static( $exportParams, $config, RequestContext::getMain() );
	}

	/**
	 * @param array $exportParams
	 * @param Config $config
	 * @param IContextSource $context
	 */
	public function __construct( $exportParams, $config, $context ) {
		parent::__construct( $exportParams, $config );
		$this->context = $context;
	}

	/**
	 * @return IContextSource
	 */
	protected function getContext() {
		return $this->context;
	}
}

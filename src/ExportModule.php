<?php

namespace BlueSpice\UniversalExport;

use Config;
use MediaWiki\MediaWikiServices;
use MWException;
use WebRequest;

abstract class ExportModule implements IExportModule {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/** @var MediaWikiServices */
	protected $services = null;

	/** @var string */
	protected $name = '';

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 */
	protected function __construct( $name, MediaWikiServices $services, Config $config ) {
		$this->name = $name;
		$this->config = $config;
		$this->services = $services;
	}

	/**
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @return IExportModule
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config ) {
		return new static( $name, $services, $config );
	}

	/**
	 * @param WebRequest $request
	 * @param array|null $additional Additional query params to append
	 * @return string
	 * @throws MWException
	 */
	public function getExportLink( WebRequest $request, array $additional = [] ) {
		$queryParams = $request->getValues();
		$title = isset( $additional['title'] ) ? $additional['title'] : '';
		if ( $title === '' && isset( $queryParams['title'] ) ) {
			$title = $queryParams['title'];
		}

		// TODO: To be replaced with ParamProcessor
		$pageNameForSpecial = \BsCore::sanitize( $title, '', \BsPARAMTYPE::STRING );
		$pageNameForSpecial = trim( $pageNameForSpecial, '_' );
		$special = $this->getServices()->getSpecialPageFactory()->getPage(
			'UniversalExport'
		);

		if ( isset( $queryParams['title'] ) ) {
			unset( $queryParams['title'] );
		}
		$queryParams['ue[module]'] = $this->getName();

		return $special->getPageTitle( $pageNameForSpecial )
			->getLinkURL( array_merge( $queryParams, $additional ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @inheritDoc
	 */
	public function getServices() {
		return $this->services;
	}
}

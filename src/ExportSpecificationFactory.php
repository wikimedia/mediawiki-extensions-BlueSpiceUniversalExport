<?php

namespace BlueSpice\UniversalExport;

use Config;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use PageProps;

class ExportSpecificationFactory {
	/** @var Config */
	private $config;
	/** @var PageProps */
	private $pageProps;

	/**
	 * @param Config $config
	 * @param PageProps $pageProps
	 */
	public function __construct( Config $config, PageProps $pageProps ) {
		$this->config = $config;
		$this->pageProps = $pageProps;
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param array|null $params
	 * @return ExportSpecification
	 */
	public function newSpecification( Title $title, User $user, $params = [] ) {
		return new ExportSpecification(
			$this->config, $this->pageProps, $title, $user, $params
		);
	}
}

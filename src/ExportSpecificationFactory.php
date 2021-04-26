<?php

namespace BlueSpice\UniversalExport;

use BlueSpice\UtilityFactory;
use Config;
use Title;
use User;

class ExportSpecificationFactory {
	/** @var Config  */
	private $config;
	/** @var UtilityFactory  */
	private $utilFactory;

	/**
	 * @param Config $config
	 * @param UtilityFactory $utilFactory
	 */
	public function __construct( Config $config, UtilityFactory $utilFactory ) {
		$this->config = $config;
		$this->utilFactory = $utilFactory;
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param array|null $params
	 * @return ExportSpecification
	 */
	public function newSpecification( Title $title, User $user, $params = [] ) {
		return new ExportSpecification(
			$this->config, $this->utilFactory, $title, $user, $params
		);
	}
}

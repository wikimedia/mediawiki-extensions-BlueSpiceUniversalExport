<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\SpecialAllPages;
use BlueSpice\Discovery\Component\SpecialMainPage;
use BlueSpice\Discovery\Component\SpecialRecentChanges;
use ConfigFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class MainLinksPanel implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @var ConfigFactory
	 */
	private $configFactory = null;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->configFactory = $configFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$links = [];
		$config = $this->configFactory->makeConfig( 'bsg' );
		if ( $config->get( 'DiscoveryMainLinksMainpage' ) ) {
			$links['special-mainpage'] = [
				'factory' => static function () {
					return new SpecialMainPage();
				},
				'position' => 1
			];
		}
		if ( $config->get( 'DiscoveryMainLinksAllPages' ) ) {
			$links['special-allpages'] = [
				'factory' => static function () {
					return new SpecialAllPages();
				},
				'position' => 30
			];
		}
		if ( $config->get( 'DiscoveryMainLinksRecentChanges' ) ) {
			$links['special-recentchanges'] = [
				'factory' => static function () {
					return new SpecialRecentChanges();
				},
				'position' => 100
			];
		}
		$registry->register(
			'MainLinksPanel',
			$links
		);
	}
}

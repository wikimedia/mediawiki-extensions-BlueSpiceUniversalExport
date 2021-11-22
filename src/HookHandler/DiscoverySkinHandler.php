<?php

namespace BlueSpice\UniversalExport\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\UniversalExport\ModuleFactory;

class DiscoverySkinHandler implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {
	/**
	 * @var ModuleFactory
	 */
	private $moduleFactory = null;

	/**
	 *
	 * @param ModuleFactory $moduleFactory
	 */
	public function __construct( ModuleFactory $moduleFactory ) {
		$this->moduleFactory = $moduleFactory;
	}

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		foreach ( $this->moduleFactory->getModules() as $name => $module ) {
			$reg = 't-' . $name;
			$registry->register( 'panel/export', $reg );
			if ( !$module->getSubactionHandlers() ) {
				continue;
			}
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				$key = 't-' . $name . '-' . $subaction;
				$registry->register( 'panel/export', $key );
			}
		}
	}

}

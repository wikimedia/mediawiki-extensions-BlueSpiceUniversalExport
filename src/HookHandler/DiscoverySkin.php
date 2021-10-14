<?php

namespace BlueSpice\UniversalExport\HookHandler;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;
use MediaWiki\MediaWikiServices;

class DiscoverySkin implements BlueSpiceDiscoveryTemplateDataProviderAfterInit {
	/**
	 *
	 * @return MediaWikiServicesa
	 */
	private function getServices() {
		return MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$moduleFactory = $this->getServices()->getService(
			'BSUniversalExportModuleFactory'
		);
		foreach ( $moduleFactory->getModules() as $name => $module ) {
			$reg = md5( $name );
			$registry->register( 'export', "ca-$reg" );
			$registry->unregister( 'toolbox', "ca-$reg" );
			if ( !$module->getSubactionHandlers() ) {
				continue;
			}
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				$key = md5( $name . '/' . $subaction );
				$registry->register( 'export', "ca-$key" );
				$registry->unregister( 'toolbox', "ca-$key" );
			}
		}
	}

}

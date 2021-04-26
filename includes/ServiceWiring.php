<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\UniversalExport\ExportSpecificationFactory;
use BlueSpice\UniversalExport\ModuleFactory;
use MediaWiki\MediaWikiServices;

return [

	'BSUniversalExportModuleFactory' => function ( MediaWikiServices $services ) {
		$moduleRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceUniversalExportModuleRegistry'
		);
		return new ModuleFactory(
			$moduleRegistry,
			$services,
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getHookContainer(),
			$services->getSpecialPageFactory()
		);
	},

	'BSUniversalExportSpecificationFactory' => function ( MediaWikiServices $services ) {
		return new ExportSpecificationFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getService( 'BSUtilityFactory' )
		);
	}
];

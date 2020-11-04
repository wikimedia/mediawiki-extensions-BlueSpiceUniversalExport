<?php

namespace BlueSpice\UniversalExport;

use BlueSpice\ExtensionAttributeBasedRegistry;
use Config;
use Hooks;
use MediaWiki\MediaWikiServices;
use SpecialPageFactory;

class ModuleFactory {
	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $moduleRegistry = null;

	/**
	 *
	 * @var MediaWikiServices
	 */
	protected $services = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var IExportModule[]
	 */
	protected $modules = [];

	/**
	 *
	 * @var IExportModule[]
	 */
	protected $legacyModules = null;

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $moduleRegistry
	 * @param MediaWikiServices $services
	 * @param Config $config
	 */
	public function __construct(
		ExtensionAttributeBasedRegistry $moduleRegistry,
		MediaWikiServices $services, Config $config
	) {
		$this->moduleRegistry = $moduleRegistry;
		$this->services = $services;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $name
	 * @return IExportModule|null
	 */
	public function newFromName( $name ) {
		if ( isset( $this->modules[$name] ) ) {
			$this->modules[$name];
		}
		$this->modules[$name] = null;
		$callable = $this->moduleRegistry->getValue( $name, null );
		if ( !$callable ) {
			if ( isset( $this->getLegacyModules()[$name] ) ) {
				$this->modules[$name] = $this->getLegacyModules()[$name];
				wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
			}
			return $this->modules[$name];
		}

		if ( !is_callable( $callable ) ) {
			return $this->modules[$name];
		}
		$this->modules[$name] = call_user_func_array( $callable, [
			$this->services,
			$this->config,
		] );
		return $this->modules[$name];
	}

	/**
	 *
	 * @return IExportModule[]
	 */
	public function getModules() {
		$moduleNames = array_merge(
			array_keys( $this->getLegacyModules() ),
			$this->moduleRegistry->getAllKeys()
		);

		$modules = [];
		foreach ( $moduleNames as $moduleName ) {
			$module = $this->newFromName( $moduleName );
			if ( !$module ) {
				continue;
			}
			$modules[$moduleName] = $module;
		}
		return $modules;
	}

	/**
	 *
	 * @return IExportModule[]
	 */
	private function getLegacyModules() {
		if ( $this->legacyModules !== null ) {
			return $this->legacyModules;
		}
		$this->legacyModules = [];
		$spPage = null;
		if ( SpecialPageFactory::exists( 'UniversalExport' ) ) {
			$spPage = SpecialPageFactory::getPage( 'UniversalExport' );
		}
		Hooks::run(
			'BSUniversalExportSpecialPageExecute',
			[
				// deprecated, not used anymore
				$spPage,
				// deprecated, not used anymore
				'',
				&$this->legacyModules
			],
			[ 'deprecatedVersion' => '3.2.2' ]
		);
		if ( count( $this->legacyModules ) > 0 ) {
			wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		}
		return $this->legacyModules;
	}

}

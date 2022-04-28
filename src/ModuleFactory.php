<?php

namespace BlueSpice\UniversalExport;

use BlueSpice\ExtensionAttributeBasedRegistry;
use Config;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPageFactory;
use WebRequest;

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

	/** @var WebRequest */
	protected $request;

	/**
	 *
	 * @var HookContainer
	 */
	protected $hookContainer = null;

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
	 * @var SpecialPageFactory
	 */
	protected $specialPageFactory = null;

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $moduleRegistry
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param HookContainer $hookContainer
	 * @param SpecialPageFactory $specialPageFactory
	 * @param WebRequest $request
	 */
	public function __construct( ExtensionAttributeBasedRegistry $moduleRegistry,
		MediaWikiServices $services, Config $config, HookContainer $hookContainer,
		SpecialPageFactory $specialPageFactory, WebRequest $request ) {
		$this->moduleRegistry = $moduleRegistry;
		$this->services = $services;
		$this->config = $config;
		$this->hookContainer = $hookContainer;
		$this->specialPageFactory = $specialPageFactory;
		$this->request = $request;
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
			$name,
			$this->services,
			$this->config,
			$this->request
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
		if ( $this->specialPageFactory->exists( 'UniversalExport' ) ) {
			$spPage = $this->specialPageFactory->getPage( 'UniversalExport' );
		}
		$this->hookContainer->run(
			'BSUniversalExportSpecialPageExecute',
			[
				// deprecated, not used anymore
				$spPage,
				// deprecated, not used anymore
				'',
				&$this->legacyModules
			],
			[ 'deprecatedVersion' => '3.3' ]
		);
		if ( count( $this->legacyModules ) > 0 ) {
			wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		}
		return $this->legacyModules;
	}

}

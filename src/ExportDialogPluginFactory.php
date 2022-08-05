<?php

namespace BlueSpice\UniversalExport;

use ExtensionRegistry;
use IContextSource;
use MediaWiki\Permissions\PermissionManager;
use Wikimedia\ObjectFactory\ObjectFactory;

class ExportDialogPluginFactory {

	/**
	 * @var ModuleFactory
	 */
	private $moduleFactory = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/**
	 * @var PermissionManager
	 */
	private $permissionManager = null;

	/**
	 * @var IContextSource
	 */
	private $context = null;

	/**
	 * @var array
	 */
	private $pluginRegistry = null;

	/**
	 *
	 * @param ModuleFactory $moduleFactory
	 * @param ObjectFactory $objectFactory
	 * @param PermissionManager $permissionManager
	 * @param IContextSource $context
	 */
	public function __construct( ModuleFactory $moduleFactory, ObjectFactory $objectFactory,
		PermissionManager $permissionManager, IContextSource $context ) {
		$this->moduleFactory = $moduleFactory;
		$this->objectFactory = $objectFactory;
		$this->permissionManager = $permissionManager;
		$this->context = $context;

		$extensionRegistry = ExtensionRegistry::getInstance();
		$this->pluginRegistry = $extensionRegistry->getAttribute(
			'BlueSpiceUniversalExportExportDialogPluginRegistry'
		);
	}

	/**
	 * @return array
	 */
	public function getPlugins(): array {
		$plugins = [];

		foreach ( $this->moduleFactory->getModules() as $name => $module ) {
			$requiredPermission = $module->getExportPermission();
			if ( $requiredPermission !== null ) {
				$userCanExport = $this->userCanExport(
					$requiredPermission,
					$this->context
				);
				if ( !$userCanExport ) {
					continue;
				}
			}

			if ( !isset( $this->pluginRegistry[$name] ) ) {
				continue;
			}

			$specs = $this->pluginRegistry[$name];
			$plugin = $this->objectFactory->createObject( $specs );
			if ( $plugin instanceof IExportDialogPlugin === false ) {
				continue;
			}

			if ( $plugin->skip( $this->context ) ) {
				continue;
			}

			$plugins[] = $plugin;
		}

		return $plugins;
	}

	/**
	 * @param string $permission
	 * @param IContextSource $context
	 * @return bool
	 */
	private function userCanExport( $permission, IContextSource $context ) {
		return $this->permissionManager->userCan(
			$permission, $context->getUser(), $context->getTitle()
		);
	}
}

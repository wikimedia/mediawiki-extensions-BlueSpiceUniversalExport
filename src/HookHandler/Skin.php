<?php

namespace BlueSpice\UniversalExport\HookHandler;

use BlueSpice\UniversalExport\IExportModule;
use BlueSpice\UniversalExport\IExportSubaction;
use IContextSource;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\MediaWikiServices;
use SkinTemplate;

class Skin implements SkinTemplateNavigation__UniversalHook {
	/**
	 *
	 * @return MediaWikiServices
	 */
	private function getServices() {
		return MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 * @return void
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $sktemplate->getSkinName() === 'bluespicecalumma' ) {
			// BlueSpiceCalumma has its own integration see:
			// BlueSpice\UniversalExport\Hook\ChameleonSkinTemplateOutputPageBeforeExec\\AddActions
			return;
		}
		$moduleFactory = $this->getServices()->getService(
			'BSUniversalExportModuleFactory'
		);
		foreach ( $moduleFactory->getModules() as $name => $module ) {
			$context = $sktemplate->getContext();
			if ( !$this->userCanExport( $module->getExportPermission(), $context ) ) {
				continue;
			}
			$description = $this->getActionDescription( $module, $sktemplate );
			if ( $description === null ) {
				continue;
			}
			$links[ 'actions' ][md5( $name )] = $description;
			/**
			 * @var string $name
			 * @var IExportSubaction $handler
			 */
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				if ( !$this->userCanExport( $handler->getPermission(), $context ) ) {
					continue;
				}
				$description = $this->getActionDescription(
					$module,
					$sktemplate,
					$subaction,
					$handler
				);
				if ( !$description ) {
					continue;
				}
				$key = md5( $name . '/' . $subaction );
				$links[ 'actions' ][$key] = $description;
			}
		}
	}

	/**
	 * @param IExportModule $module
	 * @param SkinTemplate $sktemplate
	 * @param string|null $subaction
	 * @param IExportSubaction|null $handler
	 * @return array The ContentAction Array
	 */
	private function getActionDescription( IExportModule $module, SkinTemplate $sktemplate,
		$subaction = null, $handler = null ) {
		$authority = $handler !== null ? $handler : $module;

		$actionButtonDetails = $authority->getActionButtonDetails();
		if ( $actionButtonDetails === null ) {
			return null;
		}

		return [
			'id' => $module->getName() . ( $subaction ? "-$subaction" : '' ),
			'href' => $authority->getExportLink( $sktemplate->getRequest() ),
			'title' => $actionButtonDetails['title'] ?? '',
			'text' => $actionButtonDetails['text'] ?? '',
			'class' => 'bs-ue-export-link',
			'iconClass' => $actionButtonDetails['iconClass'] ?? '' . ' bs-ue-export-link'
		];
	}

	/**
	 * @param string $permission
	 * @param IContextSource $context
	 * @return bool
	 */
	private function userCanExport( $permission, IContextSource $context ) {
		$pm = $this->getServices()->getPermissionManager();
		return $pm->userCan(
			$permission, $context->getUser(), $context->getTitle()
		);
	}

}

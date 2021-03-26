<?php

namespace BlueSpice\UniversalExport\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\UniversalExport\IExportModule;
use BlueSpice\UniversalExport\IExportSubaction;
use BlueSpice\UniversalExport\ModuleFactory;
use MediaWiki\MediaWikiServices;

class AddActions extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function skipProcessing() {
		return $this->skin->getTitle()->isSpecialPage();
	}

	protected function doProcess() {
		/** @var ModuleFactory $moduleFactory */
		$moduleFactory = $this->getServices()->getService(
			'BSUniversalExportModuleFactory'
		);

		$actions = [];
		foreach ( $moduleFactory->getModules() as $name => $module ) {
			if ( !$this->userCanExport( $module->getExportPermission() ) ) {
				continue;
			}
			$actions[] = $this->getActionDescription( $module );
			/**
			 * @var string $name
			 * @var IExportSubaction $handler
			 */
			foreach ( $module->getSubactionHandlers() as $subaction => $handler ) {
				if ( !$this->userCanExport( $handler->getPermission() ) ) {
					continue;
				}
				$key = md5( $name . '/' . $subaction );
				$actions[$key] = $this->getActionDescription( $module, $subaction, $handler );
			}
		}

		$this->mergeSkinDataArray(
				SkinData::EXPORT_MENU,
				array_keys( $actions )
		);

		return true;
	}

	/**
	 * @param IExportModule $module
	 * @param string|null $subaction
	 * @param IExportSubaction|null $handler
	 * @return array The ContentAction Array
	 */
	private function getActionDescription( IExportModule $module, $subaction = null, $handler = null ) {
		$authority = $handler !== null ? $handler : $module;

		return [
			'id' => $module->getName() . ( $subaction ? "-$subaction" : '' ),
			'href' => $authority->getExportLink( $this->skin->getRequest() ),
			'title' => $authority->getActionButtonDetails()['title'] ?? '',
			'text' => $authority->getActionButtonDetails()['text'] ?? '',
			'class' => 'bs-ue-export-link',
			'iconClass' => $authority->getActionButtonDetails()['iconClass'] ?? '' . ' bs-ue-export-link'
		];
	}

	/**
	 * @param string $permission
	 * @return bool
	 */
	private function userCanExport( $permission ) {
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		return $pm->userCan(
			$permission, $this->getContext()->getUser(), $this->getContext()->getTitle()
		);
	}
}

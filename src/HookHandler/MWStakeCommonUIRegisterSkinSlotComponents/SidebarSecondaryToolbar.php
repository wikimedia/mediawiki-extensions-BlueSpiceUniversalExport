<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\UniversalExport\ExportDialogButtonComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class SidebarSecondaryToolbar implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'ToolbarPanel',
			[
				'export' => [
					'factory' => static function () {
						return new ExportDialogButtonComponent();
					}
				]
			]
		);
	}
}

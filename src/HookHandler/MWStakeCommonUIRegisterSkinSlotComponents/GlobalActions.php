<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\SpecialAllTemplates;
use BlueSpice\Discovery\Component\SpecialSpecialPages;
use BlueSpice\Discovery\Component\SpecialUpload;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class GlobalActions implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-specialpages' => [
					'factory' => static function () {
						return new SpecialSpecialPages();
					}
				],
				'special-alltemplates' => [
					'factory' => static function () {
						return new SpecialAllTemplates();
					}
				],
				'special-upload' => [
					'factory' => static function () {
						return new SpecialUpload();
					}
				]
			]
		);
	}
}

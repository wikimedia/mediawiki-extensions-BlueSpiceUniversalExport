<?php

namespace BlueSpice\Discovery\HookHandler\MWStakeCommonUIRegisterSkinSlotComponents;

use BlueSpice\Discovery\Component\DefaultSearchForm;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class NavbarPrimarySearchForm implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'NavbarPrimarySearchForm',
			[
				'a-default-search' => [
					'factory' => static function () {
						return new DefaultSearchForm();
					}
				]
			]
		);
	}
}

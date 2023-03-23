<?php

namespace BlueSpice\UniversalExport\HookHandler;

use BlueSpice\UniversalExport\ExportDialogPluginFactory;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use Message;
use Skin;

class SkinHandler implements SidebarBeforeOutputHook {

	/**
	 * @var array
	 */
	private $plugins = null;

	/**
	 *
	 * @param ExportDialogPluginFactory $pluginFactory
	 */
	public function __construct( ExportDialogPluginFactory $pluginFactory ) {
		$this->plugins = $pluginFactory->getPlugins();
	}

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return void
	 */
	public function onBeforePageDisplay( $out, $skin ) {
		$out->addModules( 'ext.bluespice.universalExport.exportDialog.pluginRegistry' );
		$out->addModules( 'ext.bluespice.universalExport.exportDialog' );

		$rlModules = [ 'ext.bluespice.universalExport.exportDialog.pluginRegistry' ];
		$jsConfigVars = [];
		foreach ( $this->plugins as $plugin ) {
			$pluginRlModules = $plugin->getRLModules();
			if ( !empty( $pluginRlModules ) ) {
				$rlModules = array_merge( $rlModules, $pluginRlModules );
			}

			$pluginJsConfigVars = $plugin->getJsConfigVars();
			if ( !empty( $pluginRlModules ) ) {
				$jsConfigVars = array_merge( $jsConfigVars, $pluginJsConfigVars );
			}
		}

		$out->addJsConfigVars( 'bsgUEExportDialogPluginRLModules', $rlModules );

		foreach ( $jsConfigVars as $name => $value ) {
			$out->addJsConfigVars( $name, $value );
		}
	}

	/**
	 *
	 * @param Skin $skin
	 * @param array &$sidebar
	 * @return void
	 */
	public function onSidebarBeforeOutput( $skin, &$sidebar ): void {
		/**
		 * Unfortunately the `VectorTemplateTest::testGetMenuProps` from `Skin:Vector` will break
		 * in `REL1_35`, as it does not properly clear out all hook handlers.
		 * See https://github.com/wikimedia/Vector/blob/1b03bafb1267f350ee2b0018da53c31ee0674f92/tests/phpunit/integration/VectorTemplateTest.php#L107-L108
		 * In later versions this test does not exist anymore and we can remove the bail out again.
		 * We do not perform any own UnitTests on this class, so bailing out here should be fine.
		 */
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}

		if ( !empty( $this->plugins ) ) {
			$sidebar['TOOLBOX']['ue-export-dialog'] = [
				'id' => 'bs-ue-export-dialog-open',
				'href' => '',
				'title' => Message::newFromKey( 'bs-ue-export-dialog-button-title' )->text(),
				'text' => Message::newFromKey( 'bs-ue-export-dialog-button-text' )->text(),
				'class' => 'bs-ue-export-link',
			];
		}
	}
}

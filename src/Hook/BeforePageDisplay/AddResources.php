<?php

namespace BlueSpice\UniversalExport\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.universalExport.css' );

		return true;
	}

}

<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEParamsTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:params'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-params'
			]
		];
	}

}

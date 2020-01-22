<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEExcludeTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:exclude'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-exclude'
			]
		];
	}

}

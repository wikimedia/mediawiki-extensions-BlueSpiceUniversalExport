<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEMetaTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:meta'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-meta'
			]
		];
	}

}

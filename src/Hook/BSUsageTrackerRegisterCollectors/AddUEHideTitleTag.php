<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEHideTitleTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:hidetitle'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-hidetitle'
			]
		];
	}

}

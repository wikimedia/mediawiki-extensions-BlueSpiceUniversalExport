<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEPageBreakTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:pagebreak'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-pagebreak'
			]
		];
	}

}

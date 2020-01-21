<?php

namespace BlueSpice\UniversalExport\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddUEExcludeArticleTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:universalexport:excludearticle'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-universalexport-excludearticle'
			]
		];
	}

}

<?php
namespace Craft;

class MaxCDNController extends BaseController
{
	/**
	 * Generate index page
	 *
	 * @return mixed
	 */
	public function actionIndex()
	{
		return $this->renderTemplate('maxcdn', [
			'files' => craft()->maxCDN->getPopularFiles(),
		]);
	}

	/**
	 * Generate zone page
	 *
	 * @return mixed
	 */
	public function actionZones()
	{
		$zoneData = craft()->maxCDN->getZones();
		$zones = array();

		if (is_array($zoneData)) {
			foreach ($zoneData as $zone) {
				$stats = craft()->maxCDN->getZoneStats($zone->id);

				$zones[$zone->id] = array(
					'name' => $zone->name,
					'hits' => $stats->hit,
					'cacheHits' => $stats->cache_hit,
					'nonCacheHits' => $stats->noncache_hit,
					'size' => $stats->size,
				);
			}
		}

		return $this->renderTemplate('maxcdn/zones', [
			'zones' => $zones,
		]);
	}

	/**
	 * Generate cache page
	 *
	 * @return mixed
	 */
	public function actionCache()
	{
		$zones = craft()->maxCDN->getZones();

		foreach ($zones as $zone) {
			$options[] = [
				'label' => $zone->name,
				'value' => $zone->id,
			];
		}

		return $this->renderTemplate('maxcdn/cache', [
			'options' => $options,
		]);
	}

	/**
	 * Purge cache
	 *
	 * @return mixed
	 */
	public function actionPurgeCache()
	{
		 $this->requirePostRequest();

		 $zoneId = craft()->request->getPost('zone_id');

		 craft()->maxCDN->purgeFiles($zoneId);

		 craft()->userSession->setNotice(Craft::t('Cache cleared.'));

		 return $this->actionIndex();
	}
}
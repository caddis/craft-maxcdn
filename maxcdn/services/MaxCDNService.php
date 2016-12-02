<?php
namespace Craft;

require CRAFT_PLUGINS_PATH . '/maxcdn/vendor/autoload.php';

class MaxCDNService extends BaseApplicationComponent
{
	protected $api;

	public function __construct()
	{
		$settings = craft()->plugins->getPlugin('maxcdn')->getSettings();

		$this->api = new \NetDNA(
			$settings->alias,
			$settings->consumerKey,
			$settings->consumerSecret
		);
	}

	/**
	 * Get a list of zones. This DOES NOT include stats, just the ID,
	 * name, and other relevant metadata about the zone.
	 *
	 * @method getZones
	 *
	 * @return array
	 */
	public function getZones()
	{
		return $this->callApi('/zones.json', 'zones');
	}

	/**
	 * Get the stats for a provided Zone. Note that an ID will generally
	 * be something like '12345'. Don't confuse the indexes on the
	 * zones array with the zone's actual ID.
	 *
	 * @param int $id
	 * @return object
	 */
	public function getZoneStats($id)
	{
		$response = $this->callApi('/reports/' . $id . '/stats.json', 'stats');

		// TODO: Will likely break here with multiple zones.
		// Patch when you have multiple zones to test.

		$response->hit = number_format($response->hit);

		$response->cache_hit = number_format($response->cache_hit);

		$response->noncache_hit = number_format($response->noncache_hit);

		$response->size = $this->convertSize($response->size, 'GB');

		return $response;
	}

	/**
	 * Get the files in a zone, sorted by hits
	 *
	 * @return array|bool
	 */
	public function getPopularFiles()
	{
		$files = $this->callApi('/reports/popularfiles.json', 'popularfiles');

		if (! $files) {
			return false;
		}

		foreach($files as &$file) {
			$file->hit = number_format($file->hit);
			$file->size = $this->convertSize($file->size, 'GB');
		}

		return $files;
	}

	/**
	 * Delete the zone by its provided ID.
	 *
	 * @param  int $zoneId
	 * @return void
	 */
	public function purgeFiles($zoneId)
	{
		$this->callApi('/zones/pull.json/' . $zoneId . '/cache', null, 'delete');
	}

	/**
	 * Helper method to convert sizes. Taken from Maxee.
	 *
	 * @param int $size The file's size in bytes
	 * @param string $unit
	 *
	 * @return string
	 */
	public function convertSize($size, $unit = '')
	{
		if ((! $unit and $size >= 1<<30) or $unit == 'GB') {
			return number_format($size / (1<<30), 2).'GB';
		}

		if ((! $unit and $size >= 1<<20) or $unit == 'MB') {
			return number_format($size / (1<<20), 2).'MB';
		}

		if ((! $unit and $size >= 1<<10) or $unit == 'KB') {
			return number_format($size / (1<<10), 2) . 'KB';
		}

		return number_format($size) . ' bytes';
	}

	/**
	 * Call api
	 *
	 * @param string $endpoint
	 * @param string $reportType
	 * @param string $callType
	 * @return array|bool
	 */
	private function callApi($endpoint, $reportType, $callType = '')
	{
		if ($callType) {
			switch ($callType) {
				case 'delete':
					$response = $this->api->delete($endpoint);
					return true;
					break;
			}
		}

		$response = json_decode($this->api->get($endpoint));

		if ($response->code === 404) {
			return false;
		}

		return $response->data->$reportType;
	}
}
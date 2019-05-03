<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class AutoresponderService
 * @package Getresponse\WordPress
 */
class AutoresponderService {

	const CACHE_CYCLE_DAYS_KEY = 'cycle_days';
	const CACHE_TIME = 300;

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct($api) {
		$this->api = $api;
	}

	/**
	 * @return array
	 * @throws ApiException
	 */
	public function get_autoresponders() {

		$cycleDays = gr_cache_get(self::CACHE_CYCLE_DAYS_KEY);

		if (false == $cycleDays) {
			$autoresponders = $this->get_autoresponders_from_api();
			$cycleDays = array();

			foreach ($autoresponders as $responder) {
				if ($responder['status'] !== 'enabled') {
					continue;
				}
				if ($responder['triggerSettings']['dayOfCycle'] == null) {
					continue;
				}

				$cycleDays[$responder['triggerSettings']['subscribedCampaign']['campaignId']][$responder['autoresponderId']] = array(
					'id' => $responder['autoresponderId'],
					'day' => $responder['triggerSettings']['dayOfCycle'],
					'name' => $responder['name'],
					'status' => $responder['status']
				);
			}

			gr_cache_set(self::CACHE_CYCLE_DAYS_KEY, $cycleDays, self::CACHE_TIME);
		}

		return $cycleDays;
	}

	/**
	 * @param string $id
	 *
	 * @return int
	 * @throws ApiException
	 */
	public function get_autoresponder_cycle_day($id) {

		$data = gr_get_option('autoresponders');

		if (isset($data[$id])) {
			return $data[$id];
		}

		$autoresponders = $this->get_autoresponders_from_api();

		if (!empty($autoresponders)) {
			foreach ($autoresponders as $responder) {
				if ($responder['autoresponderId'] == $id) {

					$data[$id] = $responder['triggerSettings']['dayOfCycle'];
					gr_update_option('autoresponders', $data);

					return $responder['triggerSettings']['dayOfCycle'];
				}
			}
		}

		return null;
	}

	/**
	 * @return array
	 * @throws ApiException
	 */
	private function get_autoresponders_from_api()
    {
		$page = 1;
		$perPage = 100;
		$autoresponders = array();

		do {
			$result = $this->api->get_autoresponders(array('page' => $page, 'perPage' => $perPage));
			$autoresponders = array_merge($result, $autoresponders);
			$page++;
		} while (count($result) == $perPage);

		return $autoresponders;
	}
}

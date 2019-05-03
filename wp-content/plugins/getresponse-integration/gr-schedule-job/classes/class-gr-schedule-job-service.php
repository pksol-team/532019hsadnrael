<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ScheduleJobService
 * @package Getresponse\WordPress
 */
class ScheduleJobService {

	const SCHEDULE_STATUS = 'schedule_status';

	/** @var ScheduleJobRepository  */
	private $repository;

	/** @var Configuration */
	private $configuration;

	/**
	 * @param ScheduleJobRepository $repository
	 * @param Configuration $configuration
	 */
	public function __construct($repository, $configuration) {
		$this->repository = $repository;
		$this->configuration = $configuration;
	}

	/**
	 * @param string $action
	 * @param ScheduleJobInterface $job
	 */
	public function add_schedule_job( $action, $job ) {
		$this->repository->add( $job->get_contact_id(), $action, json_encode( $job->for_schedule_job() ) );

	}

	/**
	 * @param int $id
	 */
	public function remove_job( $id ) {
		$this->repository->remove_job( $id );
	}

	/**
	 * @return bool
	 */
	public function is_schedule_enabled() {
		return (bool) gr_get_option( self::SCHEDULE_STATUS );
	}

	/**
	 * @param string $status
	 */
	public function update_schedule_status( $status ) {
		gr_update_option( self::SCHEDULE_STATUS, (int) $status );
	}

	public function handle_jobs() {

		if ( false === gr()->is_connected_to_getresponse() ) {
			return;
		}

		if ($this->configuration->is_cron_job_locked()) {
			return;
		}

		$api = ApiFactory::create_api();

		if ( empty( $api ) ) {
			return;
		}

		$exporter = WooCommerceExporterFactory::create($api);
		$cart_service  = new CartService( $api );
		$order_service = new OrderService( $api );
		$jobs_builder  = new ScheduleJobsBuilder();
		$woocommerce = new WoocommerceService($api);

		$results = $this->repository->get_schedules();

		if ( 0 === count( $results ) ) {
			return;
		}

		$this->configuration->lock_cron_job();

		$carts = $orders = $export_customers = array();

		foreach ( $results as $row ) {

			$payload = json_decode( $row->payload, true );

			switch ( $row->type ) {

				case ScheduleJob::UPDATE_CART:

					try {
						$products = $cart_service->build_variants_from_products(
							$woocommerce->get_ecommerce_store(),
							$payload['products']
						);

					} catch ( ApiException $e ) {
						continue;
					} catch ( EcommerceException $e ) {
						continue;
					}

					$carts[] = new CartJob(
						ScheduleJob::UPDATE_CART,
						CartFactory::create_from_params(
							$payload['store_id'],
							$payload['customer_id'],
							$payload['total_price'],
							$payload['external_id'],
							$payload['total_tax_price'],
							$products,
							$payload['url'],
							$payload['currency']
						)
					);

					break;

				case ScheduleJob::REMOVE_CART:
					$carts[] = new CartJob(
						ScheduleJob::REMOVE_CART,
						CartFactory::create_from_params(
							$payload['store_id'],
							$payload['customer_id'],
							null,
							$payload['external_id'],
							null,
							null,
							null,
							null
						)
					);

					break;

				case ScheduleJob::CREATE_ORDER:

					$orders[] = new OrderJob(
						ScheduleJob::CREATE_ORDER,
						OrderFactory::create_from_params(
							$payload['store_id'],
							$payload['cart_id'],
							$payload['contact_id'],
							$payload['order_id']
						)
					);

					break;

				case ScheduleJob::EXPORT_CUSTOMER:

					$export_customers[] = new ExportCustomerJob(
						ScheduleJob::EXPORT_CUSTOMER,
						ExportCustomerFactory::create_from_params(
							$payload['campaign_id'],
							$payload['contact_id'],
							$payload['custom_fields'],
							$payload['autoresponder_id'],
							$payload['store_id']
						)
					);

					break;

			}

			$this->remove_job( $row->id );
		}

		$carts  = $jobs_builder->process_cart_jobs( $carts );

		try {
			/** @var CartJob $job */
			foreach ( $carts as $job ) {

				switch ( $job->get_action() ) {
					case ScheduleJob::UPDATE_CART:
						$cart_service->upsert_cart( $job->get_cart() );
						break;

					case ScheduleJob::REMOVE_CART:
						$cart_service->remove_cart( $job->get_cart() );
						break;
				}
			}

			/** @var OrderJob $order */
			foreach ( $orders as $order ) {
				$order_service->upsert_order( $order->get_order() );
			}

			/** @var ExportCustomerJob $export */
			foreach ( $export_customers as $export ) {
				$export_customer = $export->get_export_customer();
				$exporter->export_customer(
                    $export_customer,
					new \WC_Customer($export_customer->get_contact_id()),
					wc_get_orders(array(
						'meta_key' => '_customer_user',
						'meta_value' => $export_customer->get_contact_id(),
					))
				);
			}
		} catch ( \Exception $e ) {}

		$this->configuration->unlock_cron_job();
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class WoocommerceService
 * @package Getresponse\WordPress
 */
class WoocommerceService {

	const CART_HASH = 'gr_cart_hash';

	/**
	 * Billing fields.
	 * @var array
	 */
	static $billing_fields = array(
		'firstname' => array( 'value' => 'billing_first_name', 'name' => 'firstname', 'default' => 'yes' ),
		'lastname'  => array( 'value' => 'billing_last_name', 'name' => 'lastname', 'default' => 'yes' ),
		'email'     => array( 'value' => 'billing_email', 'name' => 'email', 'default' => 'yes' ),
		'address'   => array( 'value' => 'billing_address_1', 'name' => 'address', 'default' => 'no' ),
		'city'      => array( 'value' => 'billing_city', 'name' => 'city', 'default' => 'no' ),
		'state'     => array( 'value' => 'billing_state', 'name' => 'state', 'default' => 'no' ),
		'telephone' => array( 'value' => 'billing_phone', 'name' => 'telephone', 'default' => 'no' ),
		'country'   => array( 'value' => 'billing_country', 'name' => 'country', 'default' => 'no' ),
		'company'   => array( 'value' => 'billing_company', 'name' => 'company', 'default' => 'no' ),
		'postcode'  => array( 'value' => 'billing_postcode', 'name' => 'postcode', 'default' => 'no' )
	);

	/** @var Api */
	private $api;

	/**
	 * @param Api $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * @param \WC_Order $order
	 * @param string $user_email
	 * @throws EcommerceException
	 * @throws ApiException
	 */
	public function update_order( $order, $user_email ) {

		if (!$this->is_ecommerce_enabled()) {
			return;
		}

		$session = new Session();
		$customer_service = new CustomerService($this->api);
		$schedule_service = new ScheduleJobService(
			new ScheduleJobRepository(),
			new Configuration()
		);

		$customer = $customer_service->get_customer( $user_email, gr_get_option( 'woocommerce_ecommerce_campaign' ));

		if ( empty( $customer ) ) {
			return;
		}

		$cart_id = null;

		if ( ! is_admin() ) {
			$cart_id = $session->get( 'cart' );
			$session->set( 'cart', - 1 );
		}

		$order_service = new OrderService( $this->api );

		if ( $schedule_service->is_schedule_enabled() ) {

			$schedule_job_service = new ScheduleJobService(
				new ScheduleJobRepository(),
				new Configuration()
			);
			$schedule_job_service->add_schedule_job(
				ScheduleJob::CREATE_ORDER,
				OrderFactory::create_from_params(
					$this->get_ecommerce_store(),
					$cart_id,
					$customer['contactId'],
					$order->get_id()
				)
			);

		} else {
			$order_service->upsert_order(
				OrderFactory::create_from_params(
					$this->get_ecommerce_store(),
					$cart_id,
					$customer['contactId'],
					$order->get_id()
				)
			);
		}
	}

	/**
	 * @param \WC_Cart $cart
	 * @param string $user_email
	 *
	 * @throws \Exception
	 * @throws ApiException
	 * @throws EcommerceException
	 */
	public function update_cart( $cart, $user_email ) {

		if (!$this->is_ecommerce_enabled()) {
			return;
		}

		$cart_service     = new CartService( $this->api );
		$customer_service = new CustomerService( $this->api );
		$session          = new Session();
		$schedule_service = new ScheduleJobService(
			new ScheduleJobRepository(),
			new Configuration()
		);

		$customer = $customer_service->get_customer( $user_email, gr_get_option( 'woocommerce_ecommerce_campaign' ) );

		if ( empty( $customer ) ) {
			return;
		}

		$cart_id = $session->get( 'cart' );

		// order has been created - generate new cart
		if ( $cart_id === - 1 ) {
			$cart_id = $cart_service->generate_cart_id();
			$session->set( 'cart', $cart_id );

			return;
		}

		$cart_data = $cart->get_cart();
		$cart_hash = $session->get( self::CART_HASH );

		// generate current cart hash
		$cc_hash = CartHash::generate_hash_from_cart( $cart_data );

		if ( empty( $cart_id ) ) {
			$cart_id = $cart_service->generate_cart_id();
			$session->set( 'cart', $cart_id );
		} else if ( $cc_hash !== $cart_hash && empty( $cart_data ) ) {
			$this->remove_cart( $cart );
			$session->set( self::CART_HASH, $cc_hash );

			return;
		} else if ( $cc_hash === $cart_hash ) {
			return;
		}

		$session->set( self::CART_HASH, $cc_hash );

		if (0 === (int) $cart->get_total( false )) {
			return;
		}

		if ( $schedule_service->is_schedule_enabled() ) {

			$schedule_service->add_schedule_job(
				ScheduleJob::UPDATE_CART,
				RawCartFactory::create_from_params(
					$this->get_ecommerce_store(),
					$customer['contactId'],
					$cart->get_cart_contents_total(),
					$cart_id,
					$cart->get_total( false ),
					$cart->get_cart_contents(),
					wc_get_cart_url(),
					get_woocommerce_currency()
				)
			);
		} else {

			$products = $cart_service->build_variants_from_products(
				$this->get_ecommerce_store(),
				$cart->get_cart_contents()
			);

			$cart_service->upsert_cart(
				CartFactory::create_from_params(
					$this->get_ecommerce_store(),
					$customer['contactId'],
					$cart->get_cart_contents_total(),
					$cart_id,
					$cart->get_total( false ),
					$products,
					wc_get_cart_url(),
					get_woocommerce_currency()
				)
			);
		}
	}

	/**
	 * @param \WC_Cart $cart
	 *
	 * @throws ApiException
	 */
	public function remove_cart( $cart ) {

		if (!$this->is_ecommerce_enabled()) {
			return;
		}

		$service = new CartService( $this->api );
		$session = new Session();
		$cart_id = $session->get( 'cart' );
		$user = wp_get_current_user();

		$schedule_service = new ScheduleJobService(
			new ScheduleJobRepository(),
			new Configuration()
		);
		$customer_service = new CustomerService( $this->api );

		$customer = $customer_service->get_customer( $user->user_email, gr_get_option( 'woocommerce_ecommerce_campaign' ) );

		if ( $schedule_service->is_schedule_enabled() ) {
			$schedule_service->add_schedule_job(
				ScheduleJob::REMOVE_CART,
				RawCartFactory::create_from_params(
					$this->get_ecommerce_store(),
					$customer['contactId'],
					$cart->get_total( false ),
					$cart_id,
					0,
					array(),
					wc_get_cart_url(),
					get_woocommerce_currency()
				)
			);
		} else {
			$service->remove_cart(
				CartFactory::create_from_params(
					$this->get_ecommerce_store(),
					$customer['contactId'],
					$cart->get_total( false ),
					$cart_id,
					0,
					array(),
					wc_get_cart_url(),
					get_woocommerce_currency()
				)
			);
		}

		$session->set( 'cart', null );
	}

	/**
	 * @param int $campaign_id
	 * @param string $autoresponder_id
	 * @param array $customs
	 * @param string $store_id
	 * @param bool $use_schedule
	 * @throws \Exception
	 */
	public function export_customers($campaign_id, $autoresponder_id, $customs, $store_id, $use_schedule)
    {
		$query = new \WP_User_Query(array('fields' => 'ID'));
		$customers = $query->get_results();

		if ( empty( $customers ) ) {
			return;
		}

        $exporter = WooCommerceExporterFactory::create(ApiFactory::create_api());
		$exporter->export_customers($customers, $campaign_id, $autoresponder_id, $customs, $store_id, $use_schedule);
	}

	public function is_ecommerce_enabled()
    {
		return (bool) gr_get_option( 'woocommerce_ecommerce' );
	}

	public function get_ecommerce_store()
    {
		return gr_get_option( 'woocommerce_ecommerce_store' );
	}

	public function disconnectEcommerce()
    {
        gr_update_option('woocommerce_ecommerce', 0);
        gr_delete_option('woocommerce_ecommerce_store');
    }
}

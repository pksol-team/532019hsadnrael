<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class WooCommerceExporter
 * @package Getresponse\WordPress
 */
class WooCommerceExporter {

	/** @var CustomerService */
	private $customerService;
	/** @var CartService */
	private $cartService;
    /** @var OrderService */
	private $orderService;
	/** @var ProductService */
	private $productService;
    /** @var OrdersMap */
	private $ordersMap;
    /** @var ProductsMap */
	private $productsMap;
	/** @var ScheduleJobService */
	private $scheduleJobService;

    /**
     * @param CustomerService $customerService
     * @param CartService $cartService
     * @param OrderService $orderService
     * @param ProductService $productService
     * @param OrdersMap $ordersMap
     * @param ProductService $productsMap
     * @param ScheduleJobService $scheduleJobService
     */
	public function __construct($customerService, $cartService, $orderService, $productService, $ordersMap, $productsMap, $scheduleJobService)
    {
	    $this->customerService = $customerService;
	    $this->cartService = $cartService;
	    $this->orderService = $orderService;
	    $this->productService = $productService;
	    $this->ordersMap = $ordersMap;
	    $this->productsMap = $productsMap;
	    $this->scheduleJobService = $scheduleJobService;
	}

	/**
	 * @param $customerIds
	 * @param $campaign_id
	 * @param $autoresponder_id
	 * @param $customs
	 * @param $store_id
	 * @param $use_schedule
	 *
	 * @throws \Exception
	 */
	public function export_customers(
		$customerIds,
		$campaign_id,
		$autoresponder_id,
		$customs,
		$store_id,
		$use_schedule
	) {

		foreach ($customerIds as $customer_id) {

			try {
				$export_customer = ExportCustomerFactory::create_from_params(
					$campaign_id,
					$customer_id,
					$customs,
					$autoresponder_id,
					$store_id
				);

				if ($use_schedule) {
					$this->scheduleJobService->add_schedule_job(
						ScheduleJob::EXPORT_CUSTOMER,
						$export_customer
					);
				} else {

					$this->export_customer(
					    $export_customer,
                        new \WC_Customer($customer_id),
                        wc_get_orders(array(
                            'meta_key' => '_customer_user',
                            'meta_value' => $customer_id,
                        ))
                    );
				}
			} catch ( ApiException $e ) {
			}
		}
	}

    /**
     * @param ExportCustomer $exportCustomerCommand
     * @param \WC_Customer $customer
     * @param \WC_Order[] $orders
     * @return bool|void
     */
    public function export_customer($exportCustomerCommand, $customer, $orders)
    {
        $customs = array();
        $data = $customer->get_data();

        foreach ($exportCustomerCommand->get_custom_fields() as $woo_custom_name => $gr_custom_name) {
            if (!empty($data['billing'][$woo_custom_name])) {
                $customs[$gr_custom_name] = $data['billing'][$woo_custom_name];
            }
        }

        $grCustomerId = $this->customerService->createOrGetContact(
            $exportCustomerCommand->get_campaign_id(),
            $customer->get_first_name() . ' ' . $customer->get_last_name(),
            $customer->get_email(),
            $exportCustomerCommand->get_autoresponder_id(),
            $customs
        );

        /**
         * first condition means that contact was added and is waiting in queue
         * second condition is obvious
         * third condition is obvious
         */
        if (null === $grCustomerId || empty($orders) || empty($exportCustomerCommand->get_store_id())) {
            return;
        }

        foreach ($orders as $order) {
            try {
                $this->export_order($order, $exportCustomerCommand->get_store_id(), $grCustomerId);
            } catch (ApiException $e) {
            } catch (EcommerceException $e) {}
        }
	}

	/**
	 * @param \WC_Order $order
	 * @param string $store_id
	 * @param string $contact_id
	 * @throws EcommerceException
	 * @throws ApiException
	 */
	public function export_order($order, $store_id, $contact_id)
    {
        $this->orderService->upsert_order(
            OrderFactory::create_from_params(
                $store_id,
                null,
                $contact_id,
                $order->get_id(),
                true
            )
        );
	}
}

<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class EcommerceException
 * @package Getresponse\WordPress
 */
class EcommerceException extends \Exception
{

    /**
     * @param string $orderId
     * @return EcommerceException
     */
    public static function createForIncorrectOrder($orderId)
    {
        return new self('Cannot create order for order id:' . $orderId);
    }

}

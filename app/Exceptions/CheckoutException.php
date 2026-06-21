<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an order cannot be completed for a reason the customer should see
 * (empty cart, item went out of stock, coupon no longer valid). The message is
 * safe to show directly to the user.
 */
class CheckoutException extends Exception
{
}

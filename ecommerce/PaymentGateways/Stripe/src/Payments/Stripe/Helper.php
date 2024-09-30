<?php

namespace Ollyo\PaymentHub\Payments\Stripe;

use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Brick\Money\CurrencyConverter;
use Ollyo\PaymentHub\Exceptions\InvalidDataException;
use Brick\Money\ExchangeRateProvider\ConfigurableProvider;


final class Helper {
    
    /**
     * Array containing the minimum charge amounts for various currencies.
     * @since 1.0.0
     */
    public static array $minimumCharges = [
        'USD' => 0.50,
        'AED' => 2.00,
        'AUD' => 0.50,
        'BGN' => 1.00,
        'BRL' => 0.50,
        'CAD' => 0.50,
        'CHF' => 0.50,
        'CZK' => 15.00,
        'DKK' => 2.50,
        'EUR' => 0.50,
        'GBP' => 0.30,
        'HKD' => 4.00,
        'HUF' => 175.00,
        'INR' => 0.50,
        'JPY' => 50.00,
        'MXN' => 10.00,
        'MYR' => 2.00,
        'NOK' => 3.00,
        'NZD' => 0.50,
        'PLN' => 2.00,
        'RON' => 2.00,
        'SEK' => 3.00,
        'SGD' => 0.50,
        'THB' => 10.00,
    ];


    /**
     * Converts a given amount from one currency to another based on the provided exchange rate.
     *
     * @param float  $amount             The amount to convert.
     * @param string $currency           The target currency to convert the amount to.
     * @param object $balanceTransaction The balance transaction object containing currency and exchange rate information.
     *
     * @return float The converted amount in the target currency.
     * @since 1.0.0
     */
    public static function convertAmountByCurrency($amount, $currency, $balanceTransaction)
	{
		$settlementCurrency 	= strtoupper($balanceTransaction->currency);
		$exchangeRate           = 1 / $balanceTransaction->exchange_rate;
		$exchangeRateProvider 	= new ConfigurableProvider();
		$exchangeRateProvider->setExchangeRate( $settlementCurrency, $currency,$exchangeRate); 

		$money 				= Money::of($amount, $settlementCurrency);
		$converter 			= new CurrencyConverter($exchangeRateProvider);
		$convertedAmount 	= $converter->convert($money, $currency,null, RoundingMode::HALF_UP);

		return $convertedAmount->getAmount()->toFloat();
	}


    /**
     * Calculates the difference between the total price and the minimum allowed charge.
     *
     * @param  object $data         An object containing the necessary data.
     * @return float                The difference between the total amount and the minimum charge, or 0 if the total 
     *                              is sufficient.
     * @throws InvalidDataException If the minimum charge for the provided currency code is not available.
     * @since  1.0.0
     */
    public static function calculateMinimumChargeDifference($data): float 
    {
        if (!isset(static::$minimumCharges[$data->currency->code])) {
            throw new InvalidDataException('Currency Exchange Is Unavailable.');
        }

        $minCharge = static::$minimumCharges[$data->currency->code];

        $subtotal       = $data->subtotal ?? 0;
        $tax            = $data->tax ?? 0;
        $shippingCharge = $data->shipping_charge ?? 0;
        $couponDiscount = $data->coupon_discount ?? 0;

        $totalAmount = ($subtotal + $tax + $shippingCharge) - $couponDiscount;

        if ($minCharge > $totalAmount) {
            return $minCharge - $totalAmount;
        }

        return 0;
    }
}
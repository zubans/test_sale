<?php

namespace App\Services;

use App\DTO\PaymentRequest;
use App\Helpers\PriceHelper;
use App\Repository\CountriesRepository;
use App\Repository\CouponsRepository;
use App\Services\PricesService\CalculatePriceService;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentService
{
    private PaypalPaymentProcessor $paypalProcessor;
    private StripePaymentProcessor $stripeProcessor;

    public function __construct(
        PaypalPaymentProcessor $paypalProcessor,
        StripePaymentProcessor $stripeProcessor
    )
    {
        $this->paypalProcessor = $paypalProcessor;
        $this->stripeProcessor = $stripeProcessor;
    }

    /**
     * @throws \Exception
     */
    public function processPayment(
        PaymentRequest      $paymentRequest,
        CouponsRepository   $couponRepository
    )
    {

        $price = CalculatePriceService::init($couponRepository)->calculatePrice($paymentRequest)
        return match ($paymentRequest->paymentProcessor) {
            'paypal' => $this->paypalProcessor->pay(PriceHelper::majorToMinor($paymentRequest->product)),
            'stripe' => $this->stripeProcessor->processPayment($paymentRequest),
            default => throw new \Exception('Unsupported payment processor.'),
        };
    }
}
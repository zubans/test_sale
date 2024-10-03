<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PaymentRequest
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public int $product;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(max=15)
     */
    public string $taxNumber;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    public string $couponCode;

    /**
     * @Assert\NotBlank
     * @Assert\Choice({"paypal", "stripe"})
     */
    public string $paymentProcessor;

    public function __construct(array $data)
    {
        $this->product = $data['product'] ?? null;
        $this->taxNumber = $data['taxNumber'] ?? null;
        $this->couponCode = $data['couponCode'] ?? null;
        $this->paymentProcessor = $data['paymentProcessor'] ?? null;
    }
}
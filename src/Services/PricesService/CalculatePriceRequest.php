<?php

namespace App\Services\PricesService;

use App\Validator\Constraints\ValidCountryTaxNumber;

class CalculatePriceRequest
{
    #[Assert\NotBlank]
    private int $productId;

    private string $couponCode;

    #[Assert\NotBlank]
    #[ValidCountryTaxNumber]
    #[Assert\Length(min: 10, max: 15)]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2}[A-Z0-9]{3,20}$/',
        message: 'Tax number must start with 2 letters followed by letters or digits.'
    )]
    private string $taxNumber;

    public function __construct(readonly array $request, readonly array $countries) {
        $this->productId = $request['product'];
        $this->couponCode = $request['couponCode'];
        $this->taxNumber = $request['taxNumber'];
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getTaxNumber(): string
    {
        return substr($this->taxNumber, 0, 2);
    }
}
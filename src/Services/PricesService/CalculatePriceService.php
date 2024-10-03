<?php

namespace App\Services\PricesService;

use App\Entity\Coupons;
use App\Exceptions\DiscountException;
use App\Exceptions\PriceException;
use App\Repository\CouponsRepository;

class CalculatePriceService
{
    private static ?CalculatePriceService $instance = null;

    public static function init(CouponsRepository $couponsRepository): CalculatePriceService
    {
        if (self::$instance === null) {
            self::$instance = new self($couponsRepository);
        }

        return self::$instance;
    }

    public function __construct(private readonly CouponsRepository $couponsRepository) {}

    /**
     * @param int $price
     * @param int $tax
     * @param string|null $discount
     * @return int
     * @throws PriceException|DiscountException
     */
    public function calculatePrice(int $price, int $tax, ?string $discount = ''): int
    {
        $price = $price + $this->getTax($price, $tax) - $this->getDiscount($price, $discount);

        if ($price <= 0) {
            throw new PriceException('incorrect price');
        }

        return $price;
    }

    /**
     * @param int $price
     * @param int $tax
     * @return int
     */
    private function getTax(int $price, int $tax): int
    {
        return $price * ($tax / 100);
    }

    /**
     * @param $price
     * @param string|null $discount
     * @return int
     * @throws DiscountException
     */
    private function getDiscount($price, ?string $discount = ''): int
    {
        if (empty($discount)) {
            return 0;
        }

        /** @var Coupons $coupon */
        $coupon = $this->couponsRepository->findOneBy(['name' => $discount]);

        if (!$coupon) {
            throw new DiscountException('incorrect discount');
        }

        $discount = 0;
        if ($coupon->getType() === Coupons::PERCENTAGE) {
            $discount = $price * $coupon->getValue() / 100;
        } elseif ($coupon->getType() === Coupons::FIXED) {
            $discount = $coupon->getValue();
        }

        return $discount;
    }
}
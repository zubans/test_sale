<?php

namespace App\Services;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class RequestService
{
    public function __construct(private readonly array $request)
    {
        $this->validate();
    }

    public function getProduct(): int
    {
        return $this->request['product'];
    }

    public function getTaxNumber(): string
    {
        return $this->request['taxNumber'];
    }

    public function getCouponCode(): ?string
    {
        return $this->request['couponCode'] ?? '';
    }

    private function validate(): void
    {
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection([
            'product' => new Assert\type('integer'),
            'taxNumber' => [
                new Assert\type('string'),
                new Assert\Callback(static function (string $value, ExecutionContextInterface $ctx) {
                    $countryCode = substr($value, 0, 2);

                    $ctx->buildViolation('Not valid country code.')
                        ->addViolation();
                }),
                ],
            'couponCode' => new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Count(['min' => 3]),
            ]),
        ]);

        $violations = $validator->validate($this->request, []);

    }
}
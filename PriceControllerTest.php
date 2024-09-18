<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Coupons;
use App\Entity\Products;
use App\Exceptions\DiscountException;
use App\Exceptions\PriceException;
use App\Repository\CountriesRepository;
use App\Repository\CouponsRepository;
use App\Repository\ProductsRepository;
use App\Services\PricesService\CalculatePriceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceControllerTest extends TestCase
{
    private $productsRepository;
    private $countriesRepository;

    private $couponsRepository;
    private $validator;
    private $em;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productsRepository = $this->createMock(ProductsRepository::class);
        $this->countriesRepository = $this->createMock(CountriesRepository::class);
        $this->couponRepository = $this->createMock(CouponsRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->controller = new PriceController();
    }

    /**
     * @dataProvider successDataProvider
     * @param $inputData
     * @param $expectedOutput
     * @throws Exception
     * @throws DiscountException
     * @throws PriceException
     */
    public function testCalculatePriceSuccess($inputData, $expectedOutput)
    {
        $product = new Products();
        $product->setName('Тестовый продукт');
        $product->setPrice(1000);

        $tax = new Countries();
        $tax->setTax(19);

        $coupon = new Coupons();
        $coupon->setValue(123)->setType(Coupons::FIXED);

        $this->productsRepository->method('find')->willReturn($product);
        $this->countriesRepository->method('findOneBy')->willReturn($tax);
        $this->couponRepository->method('findOneBy')->willReturn($coupon);

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->createMock(CalculatePriceService::class)->method('calculatePrice')->willReturn(1156);

        $request = new Request([], [], [], [], [], [], json_encode($inputData));
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->index(
            $request,
            $this->productsRepository,
            $this->countriesRepository,
            $this->couponRepository,
            $this->validator,
            $this->em
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedOutput), $response->getContent());
    }

    public static function successDataProvider(): array
    {
        return [
            [
                [
                    "product" => 1,
                    "taxNumber" => "DE123456789",
                    "couponCode" => "D15"
                ],
                [
                    'name' => 'Тестовый продукт',
                    'price' => "10.67"
                ]
            ],
            // Вы можете добавить больше тестовых случаев здесь
        ];
    }
}

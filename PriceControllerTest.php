<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Products;
use App\Repository\CountriesRepository;
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
    private $validator;
    private $em;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productsRepository = $this->createMock(ProductsRepository::class);
        $this->countriesRepository = $this->createMock(CountriesRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->controller = new PriceController(
            $this->productsRepository,
            $this->countriesRepository,
            $this->validator,
            $this->em
        );
    }

    /**
     * @dataProvider successDataProvider
     * @throws Exception
     */
    public function testCalculatePriceSuccess($inputData, $expectedOutput)
    {
        // Создаем продукт
        $product = new Products();
//        $product->setId(1);
        $product->setName('Тестовый продукт');
        $product->setPrice(1000); // Установим базовую цену

        // Создаем налог
        $tax = new Countries();
        $tax->setTax(0.19); // 19% НДС для теста

        // Мокаем вызовы репозиториев
        $this->productsRepository->method('find')->willReturn($product);
        $this->countriesRepository->method('findOneBy')->willReturn($tax);

        // Мокаем валидатор
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        // Мокаем сервис расчета цены
        $calculatePriceService = $this->createMock(CalculatePriceService::class);
        $calculatePriceService->method('calculatePrice')->willReturn(1156); // Ожидаемый результат с налогом

        $this->em->method('getRepository')->willReturn($this->countriesRepository);
        $this->em->method('findOneBy')->willReturn($calculatePriceService);

        // Создаем запрос соответствующий вашему API
        $request = new Request([], [], [], [], [], [], json_encode($inputData));
        $request->headers->set('Content-Type', 'application/json');

        // Вызываем метод контроллера
        $response = $this->controller->index($request, $this->productsRepository, $this->countriesRepository, $this->validator, $this->em);

        // Проверяем результат
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
                    'price' => 1156 // Ожидаемая цена с учетом налога
                ]
            ],
            // Вы можете добавить больше тестовых случаев здесь
        ];
    }
}

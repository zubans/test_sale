<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Products;
use App\Exceptions\DiscountException;
use App\Exceptions\PriceException;
use App\Helpers\PriceHelper;
use App\Repository\CountriesRepository;
use App\Repository\CouponsRepository;
use App\Repository\ProductsRepository;
use App\Services\PricesService\CalculatePriceRequest;
use App\Services\PricesService\CalculatePriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PriceController extends AbstractController
{
    /**
     * @throws PriceException
     * @throws DiscountException
     */
    #[Route('/calculate-price', name: 'app_price', methods: ['POST'])]
    public function index(
        Request $request,
        ProductsRepository $productsRepository,
        CountriesRepository $cR,
        CouponsRepository $couponsRepository,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
    ): JsonResponse {
        $countries = $cR->getCountryCodes();

        $calculatePriceRequest = new CalculatePriceRequest($request->toArray(), $countries);
        $errors = $validator->validate($calculatePriceRequest);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => (string)$errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        /** @var Products $product */
        $product = $productsRepository->find($calculatePriceRequest->getProductId());

        $tax = $cR->findOneBy(['code' => $calculatePriceRequest->getTaxNumber()]);

        try {
            $price = CalculatePriceService::init($couponsRepository)->calculatePrice(
                $product->getPrice(),
                $tax->getTax(),
                $calculatePriceRequest->getCouponCode(),
            );

            return new JsonResponse([
                'name' => $product->getName(),
                'price' => PriceHelper::minorToMajor($price),
            ]);
        } catch (PriceException|DiscountException $e) {
            return $this->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

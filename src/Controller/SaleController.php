<?php

namespace App\Controller;

use App\DTO\PaymentRequest;
use App\Entity\Products;
use App\Repository\CountriesRepository;
use App\Repository\ProductsRepository;
use App\Services\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaleController extends AbstractController
{
    private PaymentService $paymentService;
    private ValidatorInterface $validator;

    public function __construct(PaymentService $paymentService, ValidatorInterface $validator)
    {
        $this->paymentService = $paymentService;
        $this->validator = $validator;
    }

    /**
     * @Route("/pay", name="process_payment", methods={"POST"})
     */
    public function processPayment(
        Request $request,
        ProductsRepository $productsRepository,
        CountriesRepository $countriesRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $paymentRequest = new PaymentRequest($data);

        /** @var Products $product */
        $product = $productsRepository->find($paymentRequest->product);

        $tax = $countriesRepository->findOneBy(['code' => $paymentRequest->taxNumber]);

        $errors = $this->validator->validate($paymentRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            throw new BadRequestHttpException(implode(", ", $errorMessages));
        }

        $result = $this->paymentService->processPayment($paymentRequest);

        return new JsonResponse($result);
    }
}

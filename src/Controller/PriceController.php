<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class PriceController extends AbstractController
{
    #[Route('/calculate-price', name: 'app_price')]
    public function index(Request $request, ProductsRepository $productsRepository): JsonResponse
    {
        $product = $productsRepository->find($request->toArray()['product']);
        dd($product);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PriceController.php',
        ]);
    }
}

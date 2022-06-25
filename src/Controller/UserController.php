<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/user", name="app_user")
     * @param UserInterface $user
     * @return JsonResponse
     */
    public function index(UserInterface $user): JsonResponse
    {
        return $this->json([
            'status' => 1,
            'data' => [
                'id' => $user->getId(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    /**
     * @Route("/api/user/orders", name="user_orders")
     * @return JsonResponse
     */
    public function orders(UserInterface $user): JsonResponse
    {
        $orders = $user->getOrders();
        $ordersData = [];
        foreach ($orders as $order) {

            $addressData = [
                'id' => $order->getAddress()->getId(),
                'name' => $order->getAddress()->getName(),
                'district' => $order->getAddress()->getDistrict(),
                'city' => $order->getAddress()->getCity(),
                'country' => $order->getAddress()->getCountry(),
                'detail' => $order->getAddress()->getDetail(),
            ];

            $productData = [
                'id' => $order->getProduct()->getId(),
                'name' => $order->getProduct()->getName(),
                'barcode' => $order->getProduct()->getBarcode(),
            ];

            $data = [
                "order_id" => $order->getId(),
                "product" => $productData,
                "quantity" => $order->getQuantity(),
                "shipping_date" => $order->getShippingDate(),
                "address" => $addressData,
            ];

            $ordersData[] = $data;
        }
        return $this->json([
            'status' => 1,
            'orders' => $ordersData
        ]);
    }
}

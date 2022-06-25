<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $validator;
    private $doctrine;

    public function __construct(OrderRepository  $orderRepository, ValidatorInterface $validator, ManagerRegistry $doctrine)
    {
        $this->orderRepository = $orderRepository;
        $this->validator = $validator;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/api/order/create", name="order_create")
     * @param Request $request
     * @param UserInterface $user
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(Request $request, UserInterface $user): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $entityManager = $this->getDoctrine()->getManager();

        if( isset($requestData['address_id']) ){
            $addressId = $requestData['address']['id'];
            $addressRepository = $this->getDoctrine()->getRepository(Address::class);
            $address = $addressRepository->find([$addressId]);
        } else {
            if(isset($requestData['address']) && is_array($requestData['address'])) {
                $address = new Address();
                $address->setUser($user);
                $address->setName($requestData['address']['name']);
                $address->setDistrict($requestData['address']['district']);
                $address->setCity($requestData['address']['city']);
                $address->setCountry($requestData['address']['country']);
                $address->setDetail($requestData['address']['detail']);

                $entityManager->persist($address);
                $entityManager->flush();

                $addressId = $address->getId();
            } else {
                return $this->json([
                    'status' => 0,
                    'message' => 'Adres bilgileri eksik olduğundan sipariş oluşturulamadı!',
                ]);
            }
        }

        if( !isset($requestData['product_id']) || empty($requestData['product_id'] ) ) {
            return $this->json([
                'status' => 0,
                'message' => 'Ürün id olmadığından sipariş oluşturulamadı!',
            ]);
        } else {
            $product = $this->getDoctrine()->getRepository(Product::class)->find((int) $requestData['product_id']);
        }

        $shippingDate = date('Y-m-d h:i:s', strtotime('+ 2 days', strtotime(date('Y-m-d h:i:s'))));

        $order = new Order();
        $order->setUser($user);
        $order->setProduct($product);
        $order->setAddress($address);
        $order->setQuantity($requestData['quantity']);
        $order->setStatus(1);
        $order->setShippingDate(new \DateTime($shippingDate));
        $order->setCreatedAt(new \DateTime("now"));

        $entityManager->persist($order);
        if($order->getId()) {
            $productQuantity = $product->getQuantity();
            $product->setQuantity($productQuantity - $requestData['quantity']);
        } else {

        }

        $responseData = [
            'order_code' => $order->getId(),
            'product_id' => $order->getProductId(),
            'quantity' => $order->getQuantity(),
            'shipping_date' => $order->getShippingDate(),
            'address' => $address
        ];

        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $responseData,
        ]);
    }

    /**
     * @Route("/api/order/update/{id}", name="order_update")
     * @param int $id
     * @param Request $request
     * @param UserInterface $user
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(int $id, Request $request, UserInterface $user) : JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $entityManager = $this->getDoctrine()->getManager();
        $orderRepository = $entityManager->getRepository(Order::class);
        $order = $orderRepository->find($id);

        if(!$order) {
            return $this->json([
                'status' => 0,
                'message' => 'Sipariş bulunamadı!',
            ]);
        }

        if($order->getShippingDate() < date('Y-m-d H:i:s')) {
            return $this->json([
                'status' => 0,
                'message' => 'Ürün kargolanma süresi geçtiği için bu sipariş güncellenemez!',
            ]);
        }

        if( isset($requestData['address_id']) ){
            $addressId = $requestData['address']['id'];
            $addressRepository = $this->getDoctrine()->getRepository(Address::class);
            $address = $addressRepository->findOneBy($addressId);
        } else {
            $address = new Address();
            $address->setUserId($user->getId());
            $address->setName($requestData['address']['name']);
            $address->setDistrict($requestData['address']['district']);
            $address->setCity($requestData['address']['city']);
            $address->setCountry($requestData['address']['country']);
            $address->setDetail($requestData['address']['detail']);

            $entityManager->persist($address);
            $entityManager->flush();

            $addressId = $address->getId();
        }

        $shippingDate = date('Y-m-d h:i:s', strtotime('+ 2 days', strtotime(date('Y-m-d h:i:s'))));

        $order->setAddress($address);
        $order->setQuantity($requestData['quantity']);
        $order->setUpdatedAt(new \DateTime("now"));

        $entityManager->persist($order);
        $entityManager->flush();

        //Ürün stoğundan azaltma işlemi

        $responseData = [
            'order_code' => $order->getId(),
            'product_id' => $order->getProductId(),
            'quantity' => $order->getQuantity(),
            'shipping_date' => $order->getShippingDate(),
            'address' => $address
        ];

        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $responseData,
        ]);
    }

    /**
     * @Route("/api/order/show/{id}", name="order_show")
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $order->getId(),
        ]);
    }

    /**
     * @Route("/api/order/list/", name="order_list")
     * @param UserInterface $user
     * @return JsonResponse
     */
    public function list(UserInterface $user): JsonResponse
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->getAll($user);

        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $orders,
        ]);
    }


}

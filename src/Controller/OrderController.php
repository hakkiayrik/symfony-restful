<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Service\ValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $validationService;

    public function __construct(OrderRepository  $orderRepository, ValidationService $validationService)
    {
        $this->orderRepository = $orderRepository;
        $this->validationService = $validationService;
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

        $validate = $this->validationService->createDataValidation($requestData);

        if(!$validate['status']) {
            return $this->json($validate);
        }

        $addressRepository = $this->getDoctrine()->getRepository(Address::class);

        if( isset($requestData['address_id']) ){
            $addressId = $requestData['address_id'];
            $address = $addressRepository->find($addressId);
        } else {
            $address = new Address();
            $addressRepository->add($address, $requestData['address'], $user);
        }

        $shippingDate = date('Y-m-d h:i:s', strtotime('+ 2 days', strtotime(date('Y-m-d h:i:s'))));

        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $product = $productRepository->find($requestData['product_id']);

        $order = new Order();
        $order->setUser($user);
        $order->setProduct($product);
        $order->setAddress($address);
        $order->setQuantity($requestData['quantity']);
        $order->setStatus(1);
        $order->setShippingDate(new \DateTime($shippingDate));
        $order->setCreatedAt(new \DateTime("now"));

        $entityManager->persist($order);
        $entityManager->flush();

        if($order->getId()) {
            $productQuantity = $product->getQuantity();
            $product->setQuantity($productQuantity - $requestData['quantity']);
            $entityManager->persist($product);
            $entityManager->flush();
        } else {
            return $this->json([
                'status' => 0,
                'message' => 'Sipariş Oluşturulamadı!',
            ]);
        }

        $addressData = [
            'id' => $order->getAddress()->getId(),
            'name' => $order->getAddress()->getName(),
            'district' => $order->getAddress()->getDistrict(),
            'city' => $order->getAddress()->getCity(),
            'country' => $order->getAddress()->getCountry(),
            'detail' => $order->getAddress()->getDetail(),
        ];
        $responseData = [
            'order_code' => $order->getId(),
            'product_id' => $order->getProduct()->getId(),
            'quantity' => $order->getQuantity(),
            'shipping_date' => $order->getShippingDate(),
            'address' => $addressData
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

        //ShippingDate kontrolü
        /*$obj = new \DateTime();
        if(date('Y-m-d', $order->getShippingDate()) < date('Y-m-d')) {
            return $this->json([
                'status' => 0,
                'message' => 'Ürün kargolanma süresi geçtiği için bu sipariş güncellenemez!',
            ]);
        }*/

        $validate = $this->validationService->updateDataValidation($requestData);

        if(!$validate['status']) {
            return $this->json($validate);
        }

        $product = $this->getDoctrine()->getRepository(Product::class)->find($order->getProduct()->getId());

        $addressRepository = $this->getDoctrine()->getRepository(Address::class);

        if( isset($requestData['address_id']) ){
            $addressId = $requestData['address_id'];
            $address = $addressRepository->find($addressId);;
        } else {
            $address = new Address();
            $addressRepository->add($address, $requestData['address'], $user);
        }

        $productQuantity = $product->getQuantity();
        $orderOldQuantity = $order->getQuantity();

        $order->setAddress($address);
        $order->setQuantity($requestData['quantity']);
        $order->setUpdatedAt(new \DateTime("now"));

        $entityManager->persist($order);
        $entityManager->flush();

        //Adet düzenleme yapıldığında ürün adedi tekrar güncelliyoruz
        if($orderOldQuantity > $requestData['quantity']) {
            //Eğer düzenlenen adet miktarı sipariş deki tutardan küçükse aradaki farkı tekrardan ürüne iade ediyoruz
            $product->setQuantity($productQuantity + ($orderOldQuantity - (int)$requestData['quantity']));
        } else if($requestData['quantity'] > $orderOldQuantity) {
            //Eğer düzenlenen adet miktarı sipariş deki tutardan büyükse aradaki farkı ürün miktarından çıkartıyoruz
            $product->setQuantity($productQuantity - ((int)$requestData['quantity'] - $orderOldQuantity));
        }
        $entityManager->persist($product);
        $entityManager->flush();


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
        $responseData = [
            'order_code' => $order->getId(),
            'product_id' => $productData,
            'quantity' => $order->getQuantity(),
            'shipping_date' => $order->getShippingDate(),
            'address' => $addressData
        ];

        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $responseData,
        ]);
    }

    /**
     * @Route("/api/order/show/{id}", name="order_show")
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if(is_null($id)) {
            return $this->json([
                'status' => 0,
                'message' => 'Sipariş id değeri eksik!',
            ]);
        }

        $order = $this->orderRepository->find($id);
        if(is_null($order) || !is_object($order)) {
            return $this->json([
                'status' => 0,
                'message' => 'Sipariş bulunamadı!',
            ]);
        }

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

        $orderData = [
            "order_id" => $order->getId(),
            "product" => $productData,
            "quantity" => $order->getQuantity(),
            "shipping_date" => $order->getShippingDate(),
            "address" => $addressData,
        ];

        return $this->json([
            'status' => 1,
            'message' => 'İşlem başarılı',
            'data' => $orderData,
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

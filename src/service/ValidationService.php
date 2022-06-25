<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ValidationService {

    private $productRepository;

    function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function createDataValidation($requestData)
    {
        if(!isset($requestData['address_id'])) {
            if (!isset($requestData['address']) && !is_array($requestData['address'])) {
                return array(
                    'status' => 0,
                    'message' => 'Adres bilgisi bulunamadı!'
                );
            } else {
                if (
                    (!isset($requestData['address']['name']) || empty($requestData['address']['name'])) ||
                    (!isset($requestData['address']['district']) || empty($requestData['address']['district'])) ||
                    (!isset($requestData['address']['city']) || empty($requestData['address']['city'])) ||
                    (!isset($requestData['address']['country']) || empty($requestData['address']['country']))
                ) {
                    return array(
                        'status' => 0,
                        'message' => 'Adres bilgileri eksik!'
                    );
                }
            }
        }

        if( !isset($requestData['quantity']) || empty($requestData['quantity'] ) ) {
            return array(
                'status' => 0,
                'message' => 'Adet bilgisi bulunamadı!'
            );
        }

        if( !isset($requestData['product_id']) || empty($requestData['product_id'] ) ) {
            return array(
                'status' => 0,
                'message' => 'Ürün bilgisi bulunamadı!'
            );
        } else {
            $product = $this->productRepository->find($requestData['product_id']);
            if( is_null($product) ) {
                return array(
                    'status' => 0,
                    'message' => 'Ürün bulunamadı!'
                );
            } else if(!$this->productRepository->checkQuantity($product, $requestData['quantity'])) { // Sipariş verilmek istenen tutar ile ürün stoğunu karşılaştırıyoruz.
                return array(
                    'status' => 0,
                    'message' => 'Ürün stoğu yeterli değil!'
                );
            }
        }

        return array(
            'status' => 1,
        );
    }

    public function updateDataValidation($requestData)
    {

        if(!isset($requestData['address_id'])) {
            if(!isset($requestData['address']) && !is_array($requestData['address'])) {
                return array(
                    'status' => 0,
                    'message' => 'Adres bilgisi bulunamadı!'
                );
            } else {
                if(
                    (!isset($requestData['address']['name']) ||  empty($requestData['address']['name'])) ||
                    (!isset($requestData['address']['district']) ||  empty($requestData['address']['district'])) ||
                    (!isset($requestData['address']['city']) ||  empty($requestData['address']['city'])) ||
                    (!isset($requestData['address']['country']) ||  empty($requestData['address']['country']))
                ) {
                    return array(
                        'status' => 0,
                        'message' => 'Adres bilgileri eksik!'
                    );
                }
            }
        }


        if( !isset($requestData['quantity']) || empty($requestData['quantity'] ) ) {
            return array(
                'status' => 0,
                'message' => 'Adet bilgisi bulunamadı!'
            );
        }

        return array(
            'status' => 1,
        );
    }

}
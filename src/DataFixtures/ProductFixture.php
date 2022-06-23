<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 3; $i++) {
            $product = new Product();
            $product->setName($faker->sentence(6, true));
            $product->setDescription($faker->text(300));
            $product->setPrice($faker->randomFloat(NULL, 2000,50000));
            $product->setQuantity($faker->numberBetween(50,150));
            $manager->persist($product);
        }

        $manager->flush();
    }
}

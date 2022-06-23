<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AddressFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        //First User
        $address = new Address();
        $address->setName('Ev Adresi');
        $address->setUserId(1);
        $address->setDistrict($faker->state);
        $address->setCity($faker->city);
        $address->setCountry($faker->country);
        $address->getDetail($faker->address);
        $manager->persist($address);

        //Second User
        $address = new Address();
        $address->setName('Ev Adresi');
        $address->setUserId(2);
        $address->setDistrict($faker->state);
        $address->setCity($faker->city);
        $address->setCountry($faker->country);
        $address->getDetail($faker->address);
        $manager->persist($address);

        //Third User
        $address = new Address();
        $address->setName('İş Adresi');
        $address->setUserId(2);
        $address->setDistrict($faker->state);
        $address->setCity($faker->city);
        $address->setCountry($faker->country);
        $address->getDetail($faker->address);
        $manager->persist($address);

        $manager->flush();
    }
}

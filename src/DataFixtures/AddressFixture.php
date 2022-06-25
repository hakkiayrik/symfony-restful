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
        /*$faker = Factory::create();

        for ($i = 0; $i < 3; $i++) {
            $user = $this->getReference(UserFixture::USER_REFERENCE . '-' . $i);
            $address = new Address();
            $address->setName('Ev Adresi');
            $address->setUser($user);
            $address->setDistrict($faker->state);
            $address->setCity($faker->city);
            $address->setCountry($faker->country);
            $address->getDetail($faker->address);
            $manager->persist($address);
        }

        $manager->flush();*/
    }
}

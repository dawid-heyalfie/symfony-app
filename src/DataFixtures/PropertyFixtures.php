<?php

namespace App\DataFixtures;

use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Form\DataTransformer\SlugTransformer;

class PropertyFixtures extends Fixture
{
    private SlugTransformer $slugTransformer;

    public function __construct(SlugTransformer $slugTransformer)
    {
        $this->slugTransformer = $slugTransformer;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create a single user to own all properties
        $user = new User();
        $user->setEmail('owner@example.com')
            ->setPassword(password_hash('password', PASSWORD_BCRYPT))
            ->setRoles(['ROLE_USER', 'ROLE_PROPERTY_MANAGER']);

        $manager->persist($user);
        $manager->flush();

        $propertiesData = [
            [
                'title' => 'Luxury Apartment in Downtown',
                'description' => 'A beautiful and spacious luxury apartment located in the heart of the city.',
                'price' => '350000.00',
            ],
            [
                'title' => 'Cozy Country House',
                'description' => 'A charming country house with a large garden and scenic views.',
                'price' => '250000.00',
            ],
            [
                'title' => 'Modern Condo with Sea View',
                'description' => 'A sleek modern condo with stunning views of the sea and modern amenities.',
                'price' => '500000.00',
            ],
            [
                'title' => 'Affordable Studio Apartment',
                'description' => 'A compact and affordable studio apartment, ideal for singles or students.',
                'price' => '90000.00',
            ],
        ];

        foreach ($propertiesData as $data) {
            $property = new Property();
            $property->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setPrice($data['price'])
                ->setSlug($this->slugTransformer->reverseTransform($data['title']))
                ->setOwner($user);

            $manager->persist($property);
        }

        // Randomized properties
        for ($i = 0; $i < 20; $i++) {
            $title = $faker->realText(30);
            $property = new Property();
            $property->setTitle($title)
                ->setDescription($faker->realText(200))
                ->setPrice($faker->randomFloat(2, 50000, 1000000))
                ->setSlug($this->slugTransformer->reverseTransform($title))
                ->setOwner($user);

            $manager->persist($property);
        }

        $manager->flush();
    }
}

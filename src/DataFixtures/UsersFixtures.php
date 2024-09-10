<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker\Factory;

class UsersFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordEncoder;
    private SluggerInterface $slugger;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, SluggerInterface $slugger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        // Create an admin user
        $admin = new Users();
        $admin->setEmail('mohamedamin.chalbi@esen.tn');
        $admin->setLastname('chalbi');
        $admin->setFirstname('mohamed amine');
        $admin->setAdress('8 rue 9 avril megrine riadh');
        $admin->setZipcode('2014');
        $admin->setCity('Megrine riadh');
        $admin->setPassword($this->passwordEncoder->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Create regular users using Faker
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= 5; $i++) {
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAdress($faker->streetAddress);
            $user->setZipcode($faker->postcode);
            $user->setCity($faker->city);
            $user->setPassword($this->passwordEncoder->hashPassword($user, 'secret'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}

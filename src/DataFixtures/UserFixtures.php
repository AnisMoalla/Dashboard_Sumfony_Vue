<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Ramsey\Uuid\Uuid;
class UserFixtures extends Fixture
{

    private $passwordEncoder;

      public function __construct(UserPasswordEncoderInterface $passwordEncoder)
      {
          $this->passwordEncoder = $passwordEncoder;
      }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user= new User();
        $user->setEmail("anis.1@gmail.com");
        $user->setPassword("0000");
        $user->setUuid(Uuid::uuid4());


        $manager->persist($user);
        $manager->flush();
    }
}

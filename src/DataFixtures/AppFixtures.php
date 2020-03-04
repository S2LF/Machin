<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user=new User();
        $user->setEmail('admin@gmail.com');
        $user->setPseudo('AdminGod');
        $user->setRoles(['ROLE_ADMIN']);

        $password = $this->encoder->encodePassword($user, 'Admin1234');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}

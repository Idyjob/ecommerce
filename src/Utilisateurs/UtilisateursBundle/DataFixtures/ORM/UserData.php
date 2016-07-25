<?php
namespace UtilisateursBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Utilisateurs\UtilisateursBundle\Entity\Utilisateur;

class  UserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface,OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
      // the 'security.password_encoder' service requires Symfony 2.6 or higher
       $encoder = $this->container->get('security.encoder_factory');

        $user1 = new Utilisateur();
        $user1->setUsername('amadou');
        $user1->setEmail('amadou.85@hotmail.fr');
        $user1->setEnabled(1);

        $user1->setPassword($encoder->getEncoder($user1)->encodePassword('amadou',$user1->getSalt()));
        $user1->addRole('ROLE_ADMIN');
        $user1->addRole('ROLE_SECOND');


        $user2 = new Utilisateur();
        $user2->setUsername('saliou');
        $user2->setEmail('saliou58@live.fr');
        $user2->setEnabled(1);
        $user2->setPassword($encoder->getEncoder($user1)->encodePassword('amadou',$user1->getSalt()));


        $user2->addRole('ROLE_SECOND');

        $manager->persist($user1);
        $manager->persist($user2);
        $manager->flush();
        $this->addReference('user1',$user1);
        $this->addReference('user2',$user2);
    }


    public function getOrder(){

      return 8;
    }
}

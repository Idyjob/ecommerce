<?php

namespace PagesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pages\PagesBundle\Entity\Pages;

class PagesData extends AbstractFixture implements OrderedFixtureInterface
{
  public function load(ObjectManager $manager)
  {
       $pages1 = new Pages();
       $pages1->setTitre('CGV');
       $pages1->setContenu('<div class="row">
                   <h4>Item Brand and Category</h4>
                   <h5>AB29837 Item Model</h5>
                   <p>
                   Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                   Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
       </div>');

       $pages2 = new Pages();
       $pages2->setTitre('Mentions légales');
       $pages2->setContenu('<div class="row">
                   <h4>Item Brand and Category</h4>
                   <h5>AB29837 Item Model</h5>
                   <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                   Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
       </div>');

       $pages3 = new Pages();
       $pages3->setTitre('Lois');
       $pages3->setContenu('<div class="row">
                   <h4>Item Brand and Category</h4>
                   <h5>AB29837 Item Model</h5>
                   <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                    Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
       </div>');







      $manager->persist($pages1);
      $manager->persist($pages2);
      $manager->persist($pages3);

      $manager->flush();
  }

  public function getOrder(){

    return 1;
  }
}

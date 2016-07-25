<?php
namespace Ecommerce\EcommerceBundle\Listener;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Ecommerce\EcommerceBundle\Entity\Media;


class UploadListener{
      protected $manager;

      public function __construct(EntityManager $manager)
      {
          $this->manager = $manager;
      }

      public function onUpload(PostPersistEvent $event)
      {
          $file = $event->getFile();

          $object = new Media();
          $object->setFilename($file->getPathName());

          $this->manager->persist($object);
          $this->manager->flush();
      }










}

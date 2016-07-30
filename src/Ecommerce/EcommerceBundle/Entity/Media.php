<?php

namespace Ecommerce\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Media
 *
 * @ORM\Table("media")
 * @ORM\Entity(repositoryClass="Ecommerce\EcommerceBundle\Repository\MediaRepository")
 * @ORM\HasLifecycleCallbacks
 */

class Media
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;




    /**
     * @ORM\Column(name="name", length=255)
     * @Assert\NotBlank
     */

      private $name;

      /**
       * @ORM\Column(name="path", length=255, nullable=true)
       */

      public $path;
      /**
       * @ORM\Column(name="updated_at", type="datetime", nullable=true)
       */
      private $updatedAt;

      /**
       * @ORM\Column(name="media_type", length=255, nullable=true)
       */
      private $mediatype;

      /**
       * @ORM\Column(name="media_size", length=255, nullable=true)
       */
      private $mediasize;

      /**
        * @Assert\File(
        *     maxSize = "1000M",
        *     maxSizeMessage = "Fichier trop large",
        *     notFoundMessage = "Image not Found.",
        *     mimeTypes = {"image/jpeg", "image/png","image/jpg", "image/gif","video/ogg", "video/mp4", "video/mp3"},
        *     mimeTypesMessage = "ce type de fichier n'est pas autorisé"
        * )
    */

      public $file;

      public function getUploadRootDir(){


        if($this->mediatype == "mp4" || $this->mediatype == "mp3"){
            return __dir__."/../../../../web/uploads/videos";
        }else{
              return __dir__."/../../../../web/uploads/images";
        }

      }
      public function getAbsolutePath(){

        return null === $this->path ? null: $this->getUploadRootDir()."/".$this->path;
      }
      public function getAssetPath(){
        if($this->mediatype == "mp4" || $this->mediatype == "mp3"){
            return "uploads/videos/".$this->path;
        }else{
            return "uploads/images/".$this->path;
        }


      }
      /**
      * @ORM\PrePersist()
      *@ORM\PostLoad()
      */

      public function postLoad(){
        $this->updatedAt = new \DateTime();
      }
      /**
      * @ORM\PrePersist()
      *@ORM\PreUpdate()
      */
      public function preUpload(){
        $this->tempFile = $this->getAbsolutePath();
        $this->oldFile = $this->getPath();
        $this->updatedAt = new \DateTime();
        if((null != $this->file)){
          $this->path = sha1(uniqid(mt_rand(),true)).".".$this->file->guessExtension();
          $this->mediatype = $this->file->guessExtension();
          $this->mediasize = $this->file->getClientSize()." bytes";
        }
      }

      /**
      * @ORM\PostPersist()
      *@ORM\PostUpdate()
      */
      public function upload(){
        if(null != $this->file){
          $this->file->move($this->getUploadRootDir(), $this->path);
          unset($this->file);
          if(null != $this->oldFile) unlink($this->tempFile);
        }
      }

      /**
      *@ORM\PreRemove()
      */

      public function preRemoveUpload(){
        $this->tempFile =$this->getAbsolutePath();

      }

      /**
      *@ORM\PostRemove()
      */

      public function postRemoveUpload(){

        if(file_exists($this->tempFile)) unlink($this->tempFile);
      }

    public function getId()
    {
        return $this->id;
    }

   public function getName(){

     return $this->name;
   }

   public function getPath(){
      return $this->path;
   }
   public function  __toString(){

     return $this->name;
   }

  //  public function getFile(){
  //    return $this->getAssetPath();
  //  }

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Media
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set mediatype
     *
     * @param string $mediatype
     * @return Media
     */
    public function setMediatype($mediatype)
    {
        $this->mediatype = $mediatype;

        return $this;
    }

    /**
     * Get mediatype
     *
     * @return string
     */
    public function getMediatype()
    {
        return $this->mediatype;
    }

    /**
     * Set mediasize
     *
     * @param string $mediasize
     * @return Media
     */
    public function setMediasize($mediasize)
    {
        $this->mediasize = $mediasize;

        return $this;
    }

    /**
     * Get mediasize
     *
     * @return string
     */
    public function getMediasize()
    {
        return $this->mediasize;
    }
}

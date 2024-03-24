<?php

namespace AWD\ImageSaver;

use AWD\ImageSaver\Errors\InvalidEntity;

class Entity
{
    public readonly mixed $entity;

    private ?string $id = null;
    private ?string $image = null;

    private ?string $getId = null;
    private ?string $getImage = null;
    private ?string $setImage = null;

    /**
     * @throws InvalidEntity
     */
    public function __construct(mixed $entity, string $id, string $image)
    {
        $this->entity= $entity;

        $mod_id = str_replace(array('_', '-'), '', $id);
        $mod_image = str_replace(array('_', '-'), '', $image);


        foreach(get_object_vars($entity) as $var)
        {
            if(strcasecmp($id, $var) == 0 || strcasecmp($mod_id, $var) == 0)
            {
                $this->id = $var;
            }
            else if(strcasecmp($image, $var) == 0 || strcasecmp($mod_image, $var) == 0)
            {
                $this->image = $var;
            }
        }

        if($this->id && $this->image)
        {
            return;
        }
        foreach (get_class_methods($entity) as $method)
        {
            if((stripos($method, $id) !== false ||
                    stripos($method, $mod_id) !== false)
                && (stripos($method, "get") !== false))
            {
                $this->getId = $method;
            }
            else if((stripos($method, $image) !== false ||
                    stripos($method, $mod_image) !== false)
                && (stripos($method, "get") !== false))
            {
                $this->getImage = $method;
            }
            else if((stripos($method, $image) !== false ||
                    stripos($method, $mod_image) !== false)
                && (stripos($method, "set") !== false ||
                    stripos($method, "update") !== false ||
                    stripos($method, "change") !== false))
            {
                $this->setImage = $method;
            }
        }

        if(!$this->getId || !$this->getImage || !$this->setImage)
            throw new InvalidEntity("The entity given does not match the parameters in the image_saver.yaml file. Ensure the id and image parameters are set correctly.");
    }

    public function getClassName() : string
    {
        return get_class($this->entity);
    }

    public function getId() : string
    {
        if($this->id)
        {
            $id = $this->id;
            return $this->entity->$id;
        }else
        {
            $getId = $this->getId;
            return $this->entity->$getId();
        }
    }

    public function getImage() : string
    {
        if($this->image)
        {
            $image = $this->image;
            return $this->entity->$image;
        }else
        {
            $getImage = $this->getImage;
            return $this->entity->$getImage();
        }
    }

    public function setImage(string $path): void
    {
        if($this->image)
        {
            $image = $this->image;
            $this->entity->$image = $path;
        }else
        {
            $setImage = $this->setImage;
            $this->entity->$setImage($path);
        }
    }
}
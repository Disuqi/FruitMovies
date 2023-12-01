<?php

namespace AWD\ImageSaver;

use AWD\ImageSaver\Errors\InvalidEntity;
use AWD\ImageSaver\Errors\UnsupportedEntity;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;

class ImageSaver
{
    private const BASE_DIR_POSTFIX = "Data/";
    private const IMAGES_DIRECTORY = "images/";
    private array $entitiesData;
    private ?EntityManagerInterface $entityManager;


    public function __construct(mixed $entities, bool $handleEntityManager, EntityManagerInterface $entityManager)
    {
        $this->entitiesData = $entities;
        if($handleEntityManager)
        {
            $this->entityManager = $entityManager;
        }
    }

    /**
     * @throws UnsupportedEntity
     * @throws InvalidEntity
     * @throws ReflectionException
     */
    public function saveImage(mixed $entity, mixed $data): void
    {
        $entityData = $this->getEntityData($entity);
        $imageSaverEntity = new Entity($entity, $entityData["id"], $entityData["image"]);

        $dirPath = $this->getEntityImageDirPathFromImageSaverEntity($imageSaverEntity);
        $ext = $data->guessExtension();
        $filename = $entityData["image"] . '.' . $ext;

        $data->move($dirPath, $filename);
        $imageSaverEntity->setImage($dirPath . $filename);

        if($this->entityManager)
        {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }
    }

    /**
     * @throws UnsupportedEntity
     * @throws ReflectionException
     */
    public function getBaseDir(mixed $entity) : string
    {
        $entityData = $this->getEntityData($entity);
        $className = $this->getEntityClassName($entity);
        if($entityData['base_dir'] !== null)
        {
            $baseDir = $entityData['base_dir'];
            if(!str_ends_with($baseDir, '/'))
            {
                $baseDir .= '/';
            }
        }
        else
        {
            $baseDir = $className . self::BASE_DIR_POSTFIX;
        }
        return $baseDir;
    }

    /**
     * @throws UnsupportedEntity
     * @throws InvalidEntity
     * @throws ReflectionException
     */
    public function getEntityImageDirPath(mixed $entity) : string
    {
        $entityData = $this->getEntityData($entity);
        $imageSaverEntity = new Entity($entity, $entityData["id"], $entityData["image"]);
        return $this->getBaseDir($imageSaverEntity->getClassName()) . $imageSaverEntity->getId() . "/" .self::IMAGES_DIRECTORY;
    }

    /**
     * @throws UnsupportedEntity
     * @throws ReflectionException
     */
    private function getEntityImageDirPathFromImageSaverEntity(Entity $imageSaverEntity) : string
    {
        return $this->getBaseDir($imageSaverEntity->getClassName()) . $imageSaverEntity->getId() . "/" .self::IMAGES_DIRECTORY;
    }

    /**
     * @throws UnsupportedEntity
     * @throws ReflectionException
     */
    private function getEntityData(mixed $entity) : array
    {
        $className = $this->getEntityClassName($entity);
        if(!array_key_exists($className, $this->entitiesData))
        {
            throw new UnsupportedEntity("This Entity is unsupported. Add it to the image_saver.yaml config file, to be able to use it.");
        }
        return $this->entitiesData[$className];
    }

    /**
     * @throws ReflectionException
     */
    private function getEntityClassName(mixed $entity) : string
    {
        return (new ReflectionClass($entity))->getShortName();
    }
}
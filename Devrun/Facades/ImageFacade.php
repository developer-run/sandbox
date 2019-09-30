<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ImageFacade.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Facades;

use Devrun\Utils\FileTrait;
use Nette\Http\FileUpload;
use Nette\SmartObject;

/**
 * Class ImageFacade
 *
 * @package Devrun\Facades
 * @todo refactor this class name to ImageStorage  (DevRun namespace)
 */
class ImageFacade
{
    const IMAGE_PREFIX = 'original';

    use SmartObject;
    use FileTrait;

    /** @var ImageStorage */
    protected $imageStorage;

    private $wwwDir;


    /**
     * ImageFacade constructor.
     *
     * @param ImageStorage $imageStorage
     */
    public function __construct(ImageStorage $imageStorage)
    {
        $imageStorage->setOriginalPrefix(self::IMAGE_PREFIX);
        $this->imageStorage = $imageStorage;
    }


    /**
     * DI setter
     *
     * @param $dir
     */
    public function setStorageDir($dir)
    {
        $this->imageStorage->setImagesDir($dir);
    }

    /**
     * DI setter
     *
     * @param mixed $wwwDir
     */
    public function setWwwDir($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }

    /**
     * @return mixed
     */
    public function getWwwDir()
    {
        return $this->wwwDir;
    }


    /**
     * @param FileUpload $image
     * @param null       $namespace
     *
     * @return \Brabijan\Images\Image
     */
    public function uploadImage(FileUpload $image, $namespace = NULL)
    {
        return $this->getImageStorage()->setNamespace($namespace)->upload($image);
    }


    /**
     * @param string $content
     * @param string $filename
     * @param null   $namespace
     *
     * @return \Brabijan\Images\Image
     */
    public function save($content, $filename, $namespace = NULL)
    {
        return $this->getImageStorage()->setNamespace($namespace)->save($content, $filename);
    }


    /**
     * @return ImageStorage
     */
    public function getImageStorage()
    {
        return $this->imageStorage;
    }


    /**
     * remove all images in namespace
     *
     * @param $namespace
     */
    public function removeNamespace($namespace)
    {
        if (is_dir($dir = "$this->wwwDir/{$this->getImageStorage()->getImagesDir()}/$namespace")) {
            $this->rmdir($dir, true);
        }

    }


}
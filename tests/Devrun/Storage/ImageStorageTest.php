<?php

namespace Devrun\Storage;

use Devrun\Tests\BaseTestCase;
use Devrun\Tests\FileTestTrait;
use Nette\Utils\Strings;
use Nette\Utils\UnknownImageFileException;
use Ublaboo\ImageStorage\ImageResizeException;

class ImageStorageTest extends BaseTestCase
{

    use FileTestTrait;

    public static $migrations = false;


    /** @var \Devrun\Storage\ImageStorage @inject */
    public $imageStorage;


    public function testCheckService()
    {
        $this->assertInstanceOf('Devrun\Storage\ImageStorage', $this->imageStorage, 'Devrun\Storage\ImageStorage not installed');

        $wwwDir = $this->imageStorage->getWwwDir();

        $this->assertDirectoryExists($wwwDir);
        $this->assertDirectoryIsReadable($wwwDir);
        $this->assertDirectoryIsWritable($wwwDir);
    }


    public function testAddUnknownImage()
    {
        $identifier = '/images/test/unknown|nonExist.jpg';

        try {
            $image = $this->imageStorage->fromIdentifier($identifier);
            $this->assertInstanceOf('Ublaboo\ImageStorage\Image', $image);

            $newImage = $image->createLink(); // relative path

            $newImage = $image->getPath(); // absolute path

            $this->assertFileExists($newImage);
            $this->assertTrue(Strings::endsWith($image->identifier, 'no-image.png'));

        } catch (UnknownImageFileException $e) {
            die($e->getMessage());

        } catch (ImageResizeException $e) {
            die($e->getMessage());
        }
    }


    public function testAddExistImage()
    {
//        $dir = $this->getContainer()->getParameters()['imageDir'];
//        $testFile = "$dir/testImage.jpg";

        $image = $this->createTestImage();

        $storageImg = $this->imageStorage->saveContent($image, 'testImage.jpg', 'images/testImage');
        $identifier = $storageImg->identifier;

        try {

            /*
             * get original image
             */
            $image = $this->imageStorage->fromIdentifier([$identifier]);
            $this->assertInstanceOf('Ublaboo\ImageStorage\Image', $image);

            $newImage = $image->createLink(); // relative path
            $newImage = $image->getPath(); // absolute path

            $this->assertFileExists($newImage);
            $this->assertFalse(Strings::endsWith($image->identifier, 'no-image.png'));


            /*
             * get modify image
             */
            $image = $this->imageStorage->fromIdentifier([$identifier, '100x100']);
            $this->assertInstanceOf('Ublaboo\ImageStorage\Image', $image);
            $this->assertFileExists($image->getPath());


            /*
             * delete image
             */
            $this->imageStorage->delete($identifier);
            $this->assertFileNotExists($image->getPath());


        } catch (UnknownImageFileException $e) {
            throw new $e;

        } catch (ImageResizeException $e) {
            throw new $e;
        }


    }


}

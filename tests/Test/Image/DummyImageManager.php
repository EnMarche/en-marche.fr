<?php

namespace Tests\App\Test\Image;

use App\Entity\ImageOwnerInterface;
use App\Image\ImageManagerInterface;

class DummyImageManager implements ImageManagerInterface
{
    public function saveImage(ImageOwnerInterface $object): void
    {
        $object->setImageName($object->getImage());
    }

    public function removeImage(ImageOwnerInterface $object): void
    {
        $object->setImageName(null);
    }
}

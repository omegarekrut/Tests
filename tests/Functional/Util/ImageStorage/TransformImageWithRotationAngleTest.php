<?php

namespace Tests\Functional\Util\ImageStorage;

use App\Util\ImageStorage\Collection\ImageCollection;
use App\Util\ImageStorage\Collection\ImageWithRotationAngleCollection;
use App\Util\ImageStorage\Image;
use App\Util\ImageStorage\ImageWithRotationAngle;
use App\Util\ImageStorage\TransformImageWithRotationAngle;
use Tests\Functional\TestCase;

class TransformImageWithRotationAngleTest extends TestCase
{
    public TransformImageWithRotationAngle $transformImageWithRotationAngle;

    public function setUp(): void
    {
        parent::setUp();

        $this->transformImageWithRotationAngle = $this->getContainer()->get(TransformImageWithRotationAngle::class);
    }

    public function testTransformImageWithImageCollectionShouldReturnImageCollection(): void
    {
        $imageCollection = new ImageCollection([new Image('test image')]);

        $imageAfterTransform = $this->transformImageWithRotationAngle->checkAndTransformCollectionType($imageCollection);

        $this->assertEquals($imageCollection, $imageAfterTransform);
        $this->assertEquals($imageCollection->first()->getFilename(), $imageAfterTransform->first()->getFilename());
    }

    public function testTransformImageWithImageWithRotationAngleCollectionShouldReturnImageCollection(): void
    {
        $imageWithRotationAngleCollection = new ImageWithRotationAngleCollection(
            [new ImageWithRotationAngle('test image', 0)]
        );

        $imageAfterTransform = $this->transformImageWithRotationAngle->checkAndTransformCollectionType($imageWithRotationAngleCollection);

        $this->assertNotEquals($imageWithRotationAngleCollection, $imageAfterTransform);
        $this->assertInstanceOf(ImageCollection::class, $imageAfterTransform);
        $this->assertEquals($imageWithRotationAngleCollection->first()->getFilename(), $imageAfterTransform->first()->getFilename());
    }
}

<?php

namespace App\Tests\Repository;

use App\Entity\Image;
use App\Repository\AwsImageRepository;
use AsyncAws\SimpleS3\SimpleS3Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AwsImageRepositoryTest extends TestCase
{
    const TEST_USERNAME = 'testUserName';
    const TEST_UPLOADED_IMAGENAME = 'testImageName.jpg';
    const TEST_JPG = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'googlyeyedmerlin.jpg';

    /**
     * @var array
     */
    private $testUserListImages = [];

    protected function setUp(): void
    {
        $s3Client = $this->getSimpleS3Client();

        //todo just empty & delete test bucket

        //create test bucket
        $s3Client->createBucket(['Bucket' => $_SERVER['AWS_S3_BUCKET']]);

        //delete uploaded image
        $s3Client->deleteObject([
            'Bucket' => $_SERVER['AWS_S3_BUCKET'],
            'Key' => self::TEST_USERNAME . '/' . self::TEST_UPLOADED_IMAGENAME,
        ]);

        //create test images for user list
        $this->testUserListImages = [];
        for ($i = 0; $i < 5; $i++) {
            $filename = 'test' . $i . '.jpg';
            $jpg = new UploadedFile(
                self::TEST_JPG,
                $filename,
                'image/jpeg',
                null
            );
            $s3Client->upload($_SERVER['AWS_S3_BUCKET'], self::TEST_USERNAME . '/' . $filename, $jpg->getPathname());
            $s3Client->upload($_SERVER['AWS_S3_BUCKET'], self::TEST_USERNAME . '2/' . $filename, $jpg->getPathname());
            $this->testUserListImages[] = $filename;
        }
    }

    /**
     * @covers \App\Repository\AwsImageRepository::load
     */
    public function testLoadReturnsImage()
    {
        $this->assertInstanceOf(
            Image::class,
            $this->getAwsImageRepository()->load('testUserName', 'testImageName')
        );
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testCreateUsernameInvalidThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_USERNAME_INVALID);
        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('&&&&&&&', 'testImageName'));
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testCreateImageNameInvalidThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_NAME_INVALID);
        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('testUserName', '&&&&&&&'));
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testNoImageFileThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_UPLOAD_NOT_FOUND);
        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('testUserName', 'testImageName'));
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testImageFileTooSmallThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_TOO_SMALL);

        $png = new UploadedFile(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tiny.jpg',
            'tiny.jpg',
            'image/jpeg',
            null
        );

        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('testUserName', 'testImageName'), $png);
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testImageFileTooBigThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_TOO_BIG);

        $png = new UploadedFile(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'ginormous.jpg',
            'ginormous.jpg',
            'image/jpeg',
            null
        );

        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('testUserName', 'testImageName'), $png);
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testImageFileWrongTypeThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_TYPE_NOT_ALLOWED);

        $png = new UploadedFile(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'haircut.png',
            'haircut.png',
            'image/png',
            null
        );

        $awsImageRepository = $this->getAwsImageRepository();
        $awsImageRepository->create($awsImageRepository->load('testUserName', 'testImageName'), $png);
    }

    /**
     * @covers \App\Repository\AwsImageRepository::create
     */
    public function testCreateImageIsSuccessful()
    {
        $jpg = new UploadedFile(
            self::TEST_JPG,
            self::TEST_UPLOADED_IMAGENAME,
            'image/jpeg',
            null
        );

        $awsImageRepository = $this->getAwsImageRepository();

        $awsImageRepository->create(
            $awsImageRepository->load(self::TEST_USERNAME, self::TEST_UPLOADED_IMAGENAME),
            $jpg
        );

        $this->assertTrue(
            $this->getSimpleS3Client()->has(
                $_SERVER['AWS_S3_BUCKET'],
                self::TEST_USERNAME . '/' . self::TEST_UPLOADED_IMAGENAME
            )
        );
    }

    /**
     * @covers \App\Repository\AwsImageRepository::getUrl
     */
    public function testGetUrlForNonexistentImageThrowsException()
    {
        $this->expectExceptionCode(AwsImageRepository::ERROR_IMAGE_NOT_FOUND);

        $awsImageRepository = $this->getAwsImageRepository();

        $awsImageRepository->getUrl(
            $awsImageRepository->load(self::TEST_USERNAME, 'borkbork.jpg'),
        );
    }

    /**
     * @covers \App\Repository\AwsImageRepository::getUrl
     */
    public function testGetUrlIsSuccessful()
    {
        $awsImageRepository = $this->getAwsImageRepository();

        $url = $awsImageRepository->getUrl(
            $awsImageRepository->load(self::TEST_USERNAME, 'test0.jpg'),
        );
        $this->assertIsString($url);
        //todo is it a valid url
    }

    /**
     * @covers \App\Repository\AwsImageRepository::loadImageListByUserName
     */
    public function testGetListByUserIsEmptyForUnknownUser()
    {
        $this->assertEquals([], $this->getAwsImageRepository()->loadImageListByUserName('joe'));
    }

    /**
     * @covers \App\Repository\AwsImageRepository::loadImageListByUserName
     */
    public function testGetListByUserIsSuccessful()
    {
        $list = $this->getAwsImageRepository()->loadImageListByUserName(self::TEST_USERNAME);
        $this->assertEquals($this->testUserListImages, $list);
    }

    /**
     * @return AwsImageRepository
     */
    private function getAwsImageRepository(): AwsImageRepository
    {
        return new AwsImageRepository($this->getSimpleS3Client());
    }

    /**
     * @return SimpleS3Client
     */
    private function getSimpleS3Client(): SimpleS3Client
    {
        return new SimpleS3Client([
            'endpoint' => $_SERVER['AWS_S3_ENDPOINT'],
            'pathStyleEndpoint' => true
        ]);
    }
}
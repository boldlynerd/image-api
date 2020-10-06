<?php

namespace App\Repository;

use App\Entity\Image;
use AsyncAws\S3\Input\GetObjectRequest;
use AsyncAws\SimpleS3\SimpleS3Client;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AwsImageRepository implements ImageRepositoryInterface
{
    const ERROR_IMAGE_UPLOAD_NOT_FOUND = 1;
    const ERROR_IMAGE_NAME_INVALID = 2;
    const ERROR_USERNAME_INVALID = 3;
    const ERROR_IMAGE_NOT_FOUND = 4;
    const ERROR_IMAGE_TYPE_NOT_ALLOWED = 5;
    const ERROR_IMAGE_ALREADY_EXISTS = 6;
    const ERROR_IMAGE_TOO_BIG = 7;
    const ERROR_IMAGE_TOO_SMALL = 8;
    const BUCKET_NOT_FOUND = 9;

    /**
     * @var SimpleS3Client
     */
    private SimpleS3Client $s3Client;
    private string $bucket;
    private int $imageSizeBytesMin = 400000;
    private int $imageSizeBytesMax = 1000000;

    public function __construct(SimpleS3Client $s3Client)
    {
        $this->s3Client = $s3Client;
        $this->bucket = $_SERVER['AWS_S3_BUCKET'];
        if (!empty($_SERVER['IMAGE_SIZE_BYTES_MIN']) && (int)$_SERVER['IMAGE_SIZE_BYTES_MIN'] > 0) {
            $this->imageSizeBytesMin = (int)$_SERVER['IMAGE_SIZE_BYTES_MIN'];
        }
        if (!empty($_SERVER['IMAGE_SIZE_BYTES_MAX']) && (int)$_SERVER['IMAGE_SIZE_BYTES_MAX'] > 0) {
            $this->imageSizeBytesMax = (int)$_SERVER['IMAGE_SIZE_BYTES_MAX'];
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function create(Image $image, UploadedFile $file = null)
    {
        try {
            $this->areRequiredArgumentsPresent($image, $file);
            $this->areFileTypeAndImageValid($file);

            if ($this->s3Client->has($this->bucket, $this->getAwsImageKey($image))) {
                throw new Exception('Image exists', self::ERROR_IMAGE_ALREADY_EXISTS);
            }
        } catch (Exception $e) {
            throw new Exception('Creation failed: ' . $e->getMessage(), $e->getCode());
        }

        $this->createBucketIfNeeded();

        // Bug: the upload call saves a file with 0 bytes to the localstack S3 bucket
        // even their example code fails
        //$this->s3Client->upload('my-image-bucket', 'photos/cat_2.txt', 'I like this cat');
        //
        // if I use the AWS CLI to upload a file it works
        // conclusion: not my code at fault, but extremely frustrating
        //
        // possible reasons: windows??? ugh

        $this->s3Client->upload($this->bucket, $this->getAwsImageKey($image), $file->getPathname());
    }

    /**
     * @inheritDoc
     */
    public function getUrl(Image $image)
    {
        if (!$this->s3Client->has($this->bucket, $this->getAwsImageKey($image))) {
            throw new Exception('Image not found', self::ERROR_IMAGE_NOT_FOUND);
        }

        //not presigned
        //return $this->s3Client->getUrl($this->bucket, $this->getAwsImageKey($image));

        $objectRequest = new GetObjectRequest([
            'Bucket' => $this->bucket,
            'Key' => $this->getAwsImageKey($image),
        ]);

        return $this->s3Client->presign($objectRequest, new \DateTimeImmutable('+30 min'));

    }

    /**
     * @param string $userName
     *
     * @return array
     */
    public function loadImageListByUserName(string $userName)
    {
        $userName = $this->cleanStringForAws($userName);

        $matchingImages = $this->s3Client->listObjectsV2(
            ['Bucket' => $this->bucket, 'Prefix' => $userName . '/']
        );

        $imageNames = [];
        foreach ($matchingImages as $image) {
            $imageNames[] = str_replace($userName . '/', '', $image->getKey());
        }

        return $imageNames;
    }

    /**
     * @inheritDoc
     */
    public function update(Image $image, UploadedFile $file)
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(Image $image)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function read(Image $image)
    {
        // TODO: Implement read() method.
    }

    /**
     * @param string $userName
     * @param string $imageName
     * @return Image
     */
    public function load(string $userName, string $imageName)
    {
        $image = new Image();
        return $image
            ->setName($this->cleanStringForAws($imageName))
            ->setUserName($this->cleanStringForAws($userName));
    }

    /**
     * @param Image $image
     * @return string
     */
    private function getAwsImageKey(Image $image): string
    {
        return $image->getUserName() . '/' . $image->getName();
    }

    /**
     * @param string $string
     * @return string
     *
     * https://docs.aws.amazon.com/AmazonS3/latest/dev/UsingMetadata.html
     * todo: allow characters which need special handling
     */
    private function cleanStringForAws(string $string)
    {
        return preg_replace('~[^0-9a-z!\-_\.\*\'()]~i', '', $string);
    }

    /**
     * @param Image $image
     * @param UploadedFile|null $file
     * @throws Exception
     */
    private function areRequiredArgumentsPresent(Image $image, ?UploadedFile $file): void
    {
        if (empty($image->getUserName())) {
            throw new Exception('Username invalid', self::ERROR_USERNAME_INVALID);
        }
        if (empty($image->getName())) {
            throw new Exception('Image name invalid', self::ERROR_IMAGE_NAME_INVALID);
        }
        if (empty($file)) {
            throw new Exception('Image missing', self::ERROR_IMAGE_UPLOAD_NOT_FOUND);
        }
    }

    /**
     * @param UploadedFile|null $file
     * @throws Exception
     *
     * todo human readable image sizes in error messages
     */
    private function areFileTypeAndImageValid(?UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), Image::ALLOWED_MIME_TYPES)) {
            throw new Exception(
                'Image must be one of following types: '
                . implode(',', Image::ALLOWED_MIME_TYPES),
                self::ERROR_IMAGE_TYPE_NOT_ALLOWED
            );
        }

        if ($file->getSize() < $this->imageSizeBytesMin) {
            throw new Exception(
                'Image must be at least ' . $this->imageSizeBytesMin . ' bytes',
                self::ERROR_IMAGE_TOO_SMALL
            );
        }

        if ($file->getSize() > $this->imageSizeBytesMax) {
            throw new Exception(
                'Image must be less than ' . $this->imageSizeBytesMax . ' bytes',
                self::ERROR_IMAGE_TOO_BIG
            );
        }
    }

    /**
     * I would like to use this, but it doesn't work and I have to go do work for my job
     *
     * @throws Exception
     */
    private function checkBucketExists()
    {
        $waiter = $this->s3Client->bucketExists(['Bucket' => $this->bucket]);

        while (true) {
            if ($waiter->wait(0)) {
                break;
            }

            sleep(1);
        }

        if (!$waiter->isSuccess()) {
            throw new Exception(
                'Bucket not found',
                self::BUCKET_NOT_FOUND
            );
        }
    }

    private function createBucketIfNeeded()
    {
        $this->s3Client->createBucket(['Bucket' => $this->bucket]);
    }
}
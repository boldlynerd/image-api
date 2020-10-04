<?php

namespace App\Repository;

use App\Entity\Image;
use AsyncAws\S3\Input\GetObjectRequest;
use AsyncAws\SimpleS3\SimpleS3Client;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AwsImageRepository implements ImageRepositoryInterface
{
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
        if ((int)$_SERVER['IMAGE_SIZE_BYTES_MIN'] > 0) {
            $this->imageSizeBytesMin = (int)$_SERVER['IMAGE_SIZE_BYTES_MIN'];
        }
        if ((int)$_SERVER['IMAGE_SIZE_BYTES_MAX'] > 0) {
            $this->imageSizeBytesMax = (int)$_SERVER['IMAGE_SIZE_BYTES_MAX'];
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function create(Image $image, UploadedFile $file = null)
    {
        //basic checks
        if (empty($image->getUserName())) {
            throw new Exception('Creation failed: Username invalid', 2);
        }
        if (empty($image->getName())) {
            throw new Exception('Creation failed: Image name invalid', 3);
        }
        if (empty($file)) {
            throw new Exception('Creation failed: Image missing', 6);
        }

        //file validity checks
        //todo use file->getMimeType() which is safe but requires symfony/mime which needs to be installed
        //todo human readable image sizes in error messages
        if (!in_array($file->getClientMimeType(), Image::ALLOWED_MIME_TYPES)) {
            throw new Exception(
                'Creation failed: Image must be one of following types: '
                . implode(',', Image::ALLOWED_MIME_TYPES),
                7
            );
        }

        if ($file->getSize() < $this->imageSizeBytesMin) {
            throw new Exception(
                'Creation failed: Image must be at least ' . $this->imageSizeBytesMin . ' bytes',
                8
            );
        }

        if ($file->getSize() > $this->imageSizeBytesMax) {
            throw new Exception(
                'Creation failed: Image must be less than ' . $this->imageSizeBytesMax . ' bytes',
                9
            );
        }

        //s3 checks
        if ($this->s3Client->has($this->bucket, $this->getAwsImageKey($image))) {
            throw new Exception('Creation failed: Image exists', 1);
        }

        // the upload call saves a file with 0 bytes to the localstack S3 bucket
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
            throw new Exception('Image not found', 4);
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
            ['Bucket' => $this->bucket, 'Prefix' => $userName]
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
    public function update(Image $image, string $base64Image)
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
     * todo: allow special handling characters
     */
    private function cleanStringForAws(string $string)
    {
        return preg_replace('~[^0-9a-z!\-_\.\*\'()]~i', '', $string);
    }
}
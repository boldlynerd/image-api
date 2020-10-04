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

    public function __construct(SimpleS3Client $s3Client)
    {
        $this->s3Client = $s3Client;
        $this->bucket = $_SERVER['AWS_S3_BUCKET'];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function create(Image $image, UploadedFile $file)
    {
        if (empty($image->getUserName())) {
            throw new Exception('Creation failed: Username invalid', 2);
        }
        if (empty($image->getName())) {
            throw new Exception('Creation failed: Image name invalid', 3);
        }
        if ($this->s3Client->has($this->bucket, $this->getAwsImageKey($image))) {
            throw new Exception('Creation failed: Image exists', 1);
        }

        //todo file validity testing here

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
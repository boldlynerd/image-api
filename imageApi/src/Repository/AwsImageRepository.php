<?php

namespace App\Repository;

use App\Entity\Image;
use AsyncAws\SimpleS3\SimpleS3Client;

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
     */
    public function create(Image $image, string $base64Image)
    {
        //todo: if image already exists, throw exception

        $fileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $image->getName();
        file_put_contents($fileName, base64_decode($base64Image));
        $resource = fopen($fileName, 'r');

        $this->s3Client->upload($this->bucket, $this->getAwsImageKey($image), $resource);

        //todo clean up temp dir
    }

    /**
     * @inheritDoc
     */
    public function getUrl(Image $image)
    {
        return $this->s3Client->getUrl($this->bucket, $this->getAwsImageKey($image));
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
     *
     */
    public function loadImageListByUserName(string $userName)
    {
        $matchingObjects = $this->s3Client->listObjectsV2(['Bucket' => $this->bucket, 'Prefix' => $userName]);
        dump($matchingObjects->getContents());
        die();
        foreach ($matchingObjects->getContents() as $content) {
        }
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
            ->setName($imageName)
            ->setUserName($userName);
    }

    /**
     * @param Image $image
     * @return string
     */
    private function getAwsImageKey(Image $image): string
    {
        return $image->getUserName() . '/' . $image->getName();
    }
}
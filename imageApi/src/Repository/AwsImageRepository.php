<?php

namespace App\Repository;

use App\Entity\Image;
use AsyncAws\SimpleS3\SimpleS3Client;
use Exception;

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
    public function create(Image $image, string $base64Image)
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
        //todo check if object exists
        return $this->s3Client->getUrl($this->bucket, $this->getAwsImageKey($image));

        /*
         * todo test if time allows

            $input = new GetObjectRequest([
                'Bucket' => 'my-bucket',
                'Key' => 'test',
            ]);

            // Sign on the fly
            $content = $s3->getObject($input);

         $url = $s3->presign($input, new \DateTimeImmutable('+60 min'));
         */
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
     * @return array
     */
    public function loadImageListByUserName(string $userName)
    {
        $matchingImages = $this->s3Client->listObjectsV2(['Bucket' => $this->bucket, 'Prefix' => $userName]);

        $imageNames = [];
        foreach ($matchingImages as $image) {
            $imageNames[] = str_replace($userName . '/', '', $image->getKey());
        }
        return $imageNames;
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
            ->setName($this->urlify($imageName))
            ->setUserName($this->urlify($userName));
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
    private function urlify(string $string)
    {
        return preg_replace('~[^0-9a-z!\-_\.\*\'()]~i', '', $string);
    }
}
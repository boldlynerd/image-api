<?php

namespace App\Repository;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageRepositoryInterface
{
    /**
     * @param Image $image
     * @param UploadedFile $file
     * @return Image
     */
    public function create(Image $image, UploadedFile $file);

    /**
     * @param Image $image
     * @return Image
     */
    public function read(Image $image);

    /**
     * @param Image $image
     * @return string
     */
    public function getUrl(Image $image);

    /**
     * @param Image $image
     * @param UploadedFile $file
     * @return Image
     */
    public function update(Image $image, UploadedFile $file);

    /**
     * @param Image $image
     * @return bool
     */
    public function delete(Image $image);
}
<?php

namespace App\Repository;

use App\Entity\Image;

interface ImageRepositoryInterface
{
    /**
     * @param Image $image
     * @param string $base64Image
     * @return Image
     */
    public function create(Image $image, string $base64Image);

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
     * @param string $base64Image
     * @return Image
     */
    public function update(Image $image, string $base64Image);

    /**
     * @param Image $image
     * @return bool
     */
    public function delete(Image $image);
}
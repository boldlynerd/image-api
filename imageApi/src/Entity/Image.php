<?php

namespace App\Entity;

class Image
{
    private string $name;

    private string $type; //todo possibly enum

    private int $bytes;

    private int $width;

    private int $height;

    private string $userName; // possibly later $user with User object

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Image
     */
    public function setName(string $name): Image
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Image
     */
    public function setType(string $type): Image
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getBytes(): int
    {
        return $this->bytes;
    }

    /**
     * @param int $bytes
     * @return Image
     */
    public function setBytes(int $bytes): Image
    {
        $this->bytes = $bytes;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Image
     */
    public function setWidth(int $width): Image
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Image
     */
    public function setHeight(int $height): Image
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return Image
     */
    public function setUserName(string $userName): Image
    {
        $this->userName = $userName;
        return $this;
    }
}
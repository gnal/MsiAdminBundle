<?php

namespace Msi\Bundle\AdminBundle\Util;

class ImageManipulator
{
    protected $file;
    protected $image;
    protected $w;
    protected $h;
    protected $qualityPng = 1;
    protected $qualityJpg = 100;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(\SplFileInfo $file)
    {
        $this->file = $file;
        $this->validate()->create();

        return $this;
    }

    public function resizeProp($size)
    {
        if ($this->w > $this->h) {
            $width = $size;
            $height = $size * $this->h / $this->w;
        } else {
            $height = $size;
            $width = $size * $this->w / $this->h;
        }

        $image = imagecreatetruecolor($width, $height);

        imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->w, $this->h);

        $this->image = $image;

        return $this;
    }

    public function resize($width, $height)
    {
        $srcRatio = $this->w / $this->h;
        $dstRatio = $width / $height;

        // Resizing
        if ($srcRatio > $dstRatio) {
            $ratio = $height / $this->h;
            $h = $this->h * $ratio;
            $w = $this->w * $ratio;
        } else {
            $ratio = $width / $this->w;
            $h = $this->h * $ratio;
            $w = $this->w * $ratio;
        }

        // Cropping
        $x = ($w - $width) / -2;
        $y = ($h - $height) / -2;

        $image = imagecreatetruecolor($width, $height);

        imagecopyresampled($image, $this->image, $x, $y, 0, 0, $w, $h, $this->w, $this->h);

        $this->image = $image;

        return $this;
    }

    // 0-100
    public function setQuality($a)
    {
        $this->qualityJpg = $a;
        $this->qualityPng = round(abs((9 * $a) / 100 - 9));
    }

    public function save($pathname = null)
    {

        switch ($this->file->getExtension()) {
            case 'png':
                return imagepng($this->image, $pathname ?: $this->file, $this->qualityPng);
                break;
            case 'gif':
                return imagegif($this->image, $pathname ?: $this->file);
                break;
            default:
                return imagejpeg($this->image, $pathname ?: $this->file, $this->qualityJpg);
        }
    }

    protected function validate()
    {
        if (!$this->file->isFile())
            throw new \Exception($this->file.' is not a file.');

        return $this;
    }

    protected function create()
    {
        switch ($this->file->getExtension()) {
            case 'jpg';
                $this->image = imagecreatefromjpeg($this->file);
                break;
            case 'jpeg';
                $this->image = imagecreatefromjpeg($this->file);
                break;
            case 'png';
                $this->image = imagecreatefrompng($this->file);
                break;
            case 'gif';
                $this->image = imagecreatefromgif($this->file);
                break;
            default:
                throw new \Exception($this->file.' is not a valid image file.');
        }

        $this->w = imagesx($this->image);
        $this->h = imagesy($this->image);

        return $this;
    }
}

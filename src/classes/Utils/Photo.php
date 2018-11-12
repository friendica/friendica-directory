<?php

namespace Friendica\Directory\Utils;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Photo
{
	/**
	 * @var resource|false
	 */
	private $image;
	/**
	 * @var int
	 */
	private $width;
	/**
	 * @var int
	 */
	private $height;

	public function __construct($data)
	{
		$this->image = @imagecreatefromstring($data);
		if ($this->image !== FALSE) {
			$this->width = imagesx($this->image);
			$this->height = imagesy($this->image);
		}
	}

	public function __destruct()
	{
		if ($this->image) {
			imagedestroy($this->image);
		}
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function getImage()
	{
		return $this->image;
	}

	public function scaleImage($max)
	{
		$width = $this->width;
		$height = $this->height;

		$dest_width = $dest_height = 0;

		if ((!$width) || (!$height)) {
			return FALSE;
		}

		if ($width > $max && $height > $max) {
			if ($width > $height) {
				$dest_width = $max;
				$dest_height = intval(($height * $max) / $width);
			} else {
				$dest_width = intval(($width * $max) / $height);
				$dest_height = $max;
			}
		} else {
			if ($width > $max) {
				$dest_width = $max;
				$dest_height = intval(($height * $max) / $width);
			} else {
				if ($height > $max) {
					$dest_width = intval(($width * $max) / $height);
					$dest_height = $max;
				} else {
					$dest_width = $width;
					$dest_height = $height;
				}
			}
		}

		$dest = imagecreatetruecolor($dest_width, $dest_height);
		if ($this->image) {
			imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dest_width, $dest_height, $width, $height);
			imagedestroy($this->image);
		}

		$this->image = $dest;
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function scaleImageUp($min)
	{
		$width = $this->width;
		$height = $this->height;

		$dest_width = $dest_height = 0;

		if ((!$width) || (!$height)) {
			return FALSE;
		}

		if ($width < $min && $height < $min) {
			if ($width > $height) {
				$dest_width = $min;
				$dest_height = intval(($height * $min) / $width);
			} else {
				$dest_width = intval(($width * $min) / $height);
				$dest_height = $min;
			}
		} else {
			if ($width < $min) {
				$dest_width = $min;
				$dest_height = intval(($height * $min) / $width);
			} else {
				if ($height < $min) {
					$dest_width = intval(($width * $min) / $height);
					$dest_height = $min;
				} else {
					$dest_width = $width;
					$dest_height = $height;
				}
			}
		}

		$dest = imagecreatetruecolor($dest_width, $dest_height);
		if ($this->image) {
			imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dest_width, $dest_height, $width, $height);
			imagedestroy($this->image);
		}

		$this->image = $dest;
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function scaleImageSquare($dim)
	{
		$dest = imagecreatetruecolor($dim, $dim);
		if ($this->image) {
			imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $dim, $dim, $this->width, $this->height);
			imagedestroy($this->image);
		}

		$this->image = $dest;
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function cropImage($max, $x, $y, $w, $h)
	{
		$dest = imagecreatetruecolor($max, $max);
		if ($this->image) {
			imagecopyresampled($dest, $this->image, 0, 0, $x, $y, $max, $max, $w, $h);
			imagedestroy($this->image);
		}

		$this->image = $dest;
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	public function saveImage($path)
	{
		imagejpeg($this->image, $path, 100);
	}

	public function imageString()
	{
		ob_start();
		imagejpeg($this->image, NULL, 100);
		$return = ob_get_clean();
		return $return;
	}
}


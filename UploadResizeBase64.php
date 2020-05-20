<?php

namespace App;

class UploadResizeBase64
{
    public $targetWidth = 0;
    public $targetHeight = 0;
    public $ext = "jpg";
	public $width = 0;
    public $height = 0;
    public $mode = "cover";

    public function save($base64_string, $path)
    {
		$im = base64_decode($base64_string);

        $file = fopen($path, "wb");
        $newImage = $this->resize($im, $this->width, $this->height);

        fwrite($file, $newImage);
        fclose($file);
        // return $filename;
    }

    public function resize($file, $w, $h)
    {
        $this->originalImage = imagecreatefromstring($file);
        if (!$this->originalImage) {
            return false;
        }
        $this->originalWidth = imagesx($this->originalImage);
        $this->originalHeight= imagesy($this->originalImage);


        switch ($this->mode){
			case "contain":
				$this->contain();
			break;
			case "cover":
				$this->cover();
			break;
			case "byWidth":
				$this->byWidth();
			break;
			case "byHeight":
				$this->byHeight();
			break;
			case "autoHeight":
				$this->autoHeight();
			break;
        }
        
        // Buffering
        ob_start();
        if ($this->ext === 'png') {
            imagepng($this->resizeImage);
        } else {
            imagejpeg($this->resizeImage);
        }
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    //Change the size with width and height
    protected function contain(){
		$heightRatio = $this->targetHeight / $this->originalHeight;
        $widthRatio = $this->targetWidth / $this->originalWidth;
		if ($widthRatio > $heightRatio) {
			$this->byHeight();
		} else {
			$this->byWidth();
		}
	}

	public function cover(){
		$heightRatio = $this->targetHeight / $this->originalHeight;
        $widthRatio = $this->targetWidth / $this->originalWidth;
		if ($heightRatio > $widthRatio) {
			$this->byHeight();
		} else {
			$this->byWidth();
		}
	}

	//Change the size with the width
	protected function autoHeight() { 
		$activeWidth = $this->targetWidth;
		$activeHeight = $this->originalHeight * $activeWidth / $this->originalWidth;
		$this->targetHeight = $activeHeight;

		$this->resizeImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
		imagealphablending($this->resizeImage, false);
		imagesavealpha($this->resizeImage,true);
		$transparency = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 127);
		imagefilledrectangle($this->resizeImage, 0, 0, $this->targetWidth, $this->targetWidth, $transparency);

		/* $bgcolor = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 0);
		imagefill($this->resizeImage, 0, 0, $bgcolor); */

		imagecopyresampled($this->resizeImage, $this->originalImage, 0, 0, 0, 0, $activeWidth, $activeHeight, $this->originalWidth, $this->originalHeight);
	}
	
	protected function byHeight(){
		$activeHeight = $this->targetHeight;
		$activeWidth =  $this->originalWidth * $activeHeight / $this->originalHeight;
		$trim = ($this->targetWidth - $activeWidth)/2;

		$this->resizeImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
		imagealphablending($this->resizeImage, false);
		imagesavealpha($this->resizeImage,true);
		$transparency = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 127);
		imagefilledrectangle($this->resizeImage, 0, 0, $this->targetWidth, $this->targetWidth, $transparency);

		//$bgcolor = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 0);
		//imagefill($this->resizeImage, 0, 0, $bgcolor);
		imagecopyresampled($this->resizeImage, $this->originalImage, $trim, 0, 0, 0, $activeWidth, $activeHeight, $this->originalWidth, $this->originalHeight);
	}

	protected function byWidth(){
		$activeWidth = $this->targetWidth;
		$activeHeight = $this->originalHeight * $activeWidth / $this->originalWidth;
		$trim = ($this->targetHeight - $activeHeight)/2;

		$this->resizeImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
		imagealphablending($this->resizeImage, false);
		imagesavealpha($this->resizeImage,true);
		$transparency = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 127);
		imagefilledrectangle($this->resizeImage, 0, 0, $this->targetWidth, $this->targetWidth, $transparency);           
		/* $bgcolor = imagecolorallocatealpha($this->resizeImage, 255, 255, 255, 0);
		imagefill($this->resizeImage, 0, 0, $bgcolor); */

		imagecopyresampled($this->resizeImage, $this->originalImage, 0, $trim, 0, 0, $activeWidth, $activeHeight, $this->originalWidth, $this->originalHeight);
	}
}

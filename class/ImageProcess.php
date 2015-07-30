<?php

class ImageProcess
{
    public $image = null;
    public $type = null;
    public $width = null;
    public $heith = null;
    public $output = null;
    public $fileName = null;
    public $originName = null;
    public $originExt = null;
    public $outputType = null;
    public $minWidth = null;
    public $minHeight = null;
    public $maxWidth = null;
    public $maxHeight = null;
    public $resizable = false;

    public function __construct()
    {
        mb_internal_encoding('utf-8');
    }

    /**
     * Load source image.
     *
     * @param string $image
     */
    public function load($image = null)
    {
        if (file_exists($image)) {
            $this->originName = pathinfo($image, PATHINFO_FILENAME);
            $this->originExt = pathinfo($image, PATHINFO_EXTENSION);
            list($this->width, $this->height, $this->type) = $this->imageInfo($image);;
            $this->image = call_user_func("imagecreatefrom{$this->type}", $image);

            if ($this->type === 'jpeg') {
                $exif = exif_read_data($image);

                if(!empty($exif['Orientation'])) {
                    switch($exif['Orientation']) {
                        case 8:
                            $this->image = imagerotate($this->image, 90, 0);
                            break;
                        case 3:
                            $this->image = imagerotate($this->image, 180, 0);
                            break;
                        case 6:
                            $this->image = imagerotate($this->image, -90, 0);
                            break;
                    }
                }
            }


            $this->width = imagesx($this->image);
            $this->height = imagesy($this->image);
        } else {
            echo '<pre>';
            var_dump('檔案不存在');
            echo '</pre>';
            die();
        }
    }

    /**
     * Upload image.
     *
     * @param string $path
     *
     * @return array
     */
    public function upload($file = null, $path = '')
    {
        $status = 'ok';
        $message = '上傳完成';
        $path = $this->fixPath($path);
        $fullPath = '';

        try {
            $tmp = $file['tmp_name'];
            $fileName = $this->fileName === null ? $this->fileName() : $this->fileName;
            $originName = pathinfo($file['name'], PATHINFO_FILENAME);
            $originExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $ext = $originExt;
            $imageInfo = $this->imageInfo($tmp);

            if ($imageInfo === false) {
                throw new Exception('格式錯誤，僅允許 jpeg, png, gif');
            }

            list($width, $height, $type) = $imageInfo;

            if (!preg_match('/(jpeg|png|gif)/i', $type)) {
                throw new Exception('格式錯誤，僅允許 jpeg, png, gif');
            }

            if ($this->maxWidth !== null && $width > $this->maxWidth) {
                throw new Exception('寬度不得大於 '.$this->maxWidth);
            }

            if ($this->maxHeight !== null && $width > $this->maxHeight) {
                throw new Exception('高度不得大於 '.$this->maxHeight);
            }

            if ($this->minWidth !== null && $width < $this->minWidth) {
                throw new Exception('寬度不得小於 '.$this->minWidth);
            }

            if ($this->minHeight !== null && $width < $this->minHeight) {
                throw new Exception('高度不得小於 '.$this->minHeight);
            }

            $this->makeDir($path);

            if ($this->outputType === null) {
                $newFileName = "{$fileName}.{$originExt}";
                move_uploaded_file($tmp, "{$path}{$newFileName}");
            } else {
                $this->load($tmp);
                $this->fileName = $fileName;
                $this->resize(300);
                $this->output($path);
                $ext = $this->outputType;
                $newFileName = "{$fileName}.{$ext}";
            }

            $fullPath = "{$path}{$newFileName}";
        } catch (Exception $e) {
            $status = 'fail';
            $message = $e->getMessage();
        }

        return compact('status', 'message', 'fullPath', 'newFileName', 'ext', 'fileName', 'width', 'height', 'originName', 'originExt');
    }

    /*
     * Crop image
     *
     * @param array $postion top, right, bottom, left
     * @return void
     */
    public function crop($postion = array(0, 0, 0, 0))
    {
        try {
            list($top, $right, $bottom, $left) = $postion;
            $minusWidth = $left + $right;
            $minusHeight = $top + $bottom;

            if ($minusWidth >= $this->width || $minusHeight >= $this->height) {
                throw new Exception('裁切長寬大於原始長寬');
            }

            $width = $this->width - $minusWidth;
            $height = $this->height - $minusHeight;

            $this->output = imagecreatetruecolor($width, $height);
            imagecopyresampled($this->output, $this->image, 0, 0, $left, $top, $this->width, $this->height, $this->width, $this->height);
        } catch (Exception $e) {
            imagedestroy($this->image);
            echo '<pre>';
            var_dump($e->getMessage());
            echo '</pre>';
            die();
        }
    }

    /**
     * Resize image
     *
     * @param integer $ration
     * @return void
     */
    public function resize($ratio = 100)
    {
        if ($this->resizable === true || $ratio < min(array($this->width, $this->height))) {
            if ($this->width >= $this->height) {
                $width = intval($this->width / $this->height * $ratio);
                $height = $ratio;
            } else {
                $height = intval($this->height / $this->width * $ratio);
                $width = $ratio;
            }
        } else {
            $width = $this->width;
            $height = $this->height;
        }

        $this->output = imagecreatetruecolor($width, $height);
        imagecopyresampled($this->output, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
    }

    /**
     * Add water mark
     *
     * @param string $image
     * @param integer $left
     * @param integer $top
     * @return void
     */
    public function waterMark($image = null, $left = 0, $top = 0)
    {
        list($width, $height, $type) = $this->imageInfo($image);
        $waterMark = call_user_func("imagecreatefrom{$type}", $image);
        imagealphablending($waterMark, false);
        imagesavealpha($waterMark, true);

        if ($this->output === null) {
            $this->output = imagecreatetruecolor($this->width, $this->height);
            imagecopyresampled($this->output, $this->image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
        }

        imagecopyresampled($this->output, $waterMark, $left, $top, 0, 0, $width, $height, $width, $height);
        imagedestroy($waterMark);
    }

    /**
     * Add text by with font
     *
     * @param integer $fontSize
     * @param string $text
     * @param integer $left
     * @param integer $top
     * @param array $color R, G, B
     * @param string $font
     * @return void
     */
    public function addText($fontSize = null, $text = null, $left = 0, $top = 0, $color = array(), $font = null)
    {
        if ($this->output === null) {
            $this->output = imagecreatetruecolor($this->width, $this->height);
            imagecopyresampled($this->output, $this->image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
        }

        $color = imagecolorallocate($this->output, $color[0], $color[1], $color[2]);
        imagettftext($this->output, $fontSize, 0, $left, $top, $color, $font, $text);
    }

    /**
     * Get Image info
     *
     * @param string $image
     * @return array
     */
    public function imageInfo($image = null)
    {
        $imageInfo = getimagesize($image);

        if ($imageInfo === false) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo['mime'];
        $types = explode('/', $type);
        $type = $types[1];

        if ($type === 'jpeg') {
            $exif = exif_read_data($image);

            if(!empty($exif['Orientation'])) {
                if ($exif['Orientation'] === 8 || $exif['Orientation'] === 6) {
                    $width = $imageInfo[1];
                    $height = $imageInfo[0];
                }
            }
        }

        return array($width, $height, $type);
    }

    /**
     * Calculate text width
     *
     * @param integer $fontSize
     * @param string $text
     * @param string $font
     * @return integer
     */
    public function textWidth($fontSize = null, $text = null, $font = null)
    {
        $dims = imagettfbbox($fontSize, 0, $font, $text);

        return abs($dims[4] - $dims[0]);
    }

    /**
     * Calculate text height
     *
     * @param integer $fontSize
     * @param string $text
     * @param string $font
     * @return integer
     */
    public function textHeight($fontSize = null, $text = null, $font = null)
    {
        $dims = imagettfbbox($fontSize, 0, $font, $text);

        return abs($dims[5] - $dims[1]);
    }

    /**
     * Output image result
     *
     * @param string $path
     * @return array
     */
    public function output($path = '')
    {
        $type = $this->outputType === null ? $this->type : str_replace('jpg', 'jpeg', $this->outputType);
        $fileName = $this->fileName === null ? $this->fileName() : $this->fileName;
        $path = $this->fixPath($path);
        $this->makeDir($path);
        $ext = str_replace('jpeg', 'jpg', $type);
        $newFileName = "{$fileName}.{$ext}";
        $fullPath = "{$path}{$newFileName}";
        $originName = $this->originName;
        $originExt = $this->originExt;

        if ($type === 'jpeg') {
            call_user_func_array("image{$type}", array($this->output, $fullPath, 100));
        } else {
            call_user_func_array("image{$type}", array($this->output, $fullPath));
        }

        imagedestroy($this->image);
        imagedestroy($this->output);

        list($width, $height) = $this->imageInfo($fullPath);

        return compact(
            'originName',
            'originExt',
            'fullPath',
            'newFileName',
            'fileName',
            'ext',
            'width',
            'height'
        );
    }

    /**
     * Create directory if not exists
     *
     * @param string $dir
     * @return void
     */
    public function makeDir($dir = '')
    {
        if (!is_dir($dir) && $dir !== '') {
            mkdir($dir, 0777);
        }
    }

    /**
     * Add slash to path string
     *
     * @param string $path
     * @return string
     */
    public function fixPath($path = '')
    {
        if ($path !== '') {
            return rtrim($path, '/').'/';
        }

        return $path;
    }

    /**
     * Random file name
     *
     * @return string
     */
    public function fileName()
    {
        return uniqid().rand(100000, 999999);
    }
}

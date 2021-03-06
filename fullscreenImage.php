<?

class fullscreenImage {

  public function listenForPost() {
    $this->rootPath        = $_POST['rootPath'];
    $this->origImagePath   = $this->rootPath.$_POST['imagePath'];
    $this->origImageName   = basename($this->origImagePath);
    $origImageSize         = getimagesize($this->origImagePath);
    $this->origImageWidth  = $origImageSize[0];
    $this->origImageHeight = $origImageSize[1];

    $this->winWidth  = $_POST['winWidth'];
    $this->winHeight = $_POST['winHeight'];

    $this->pixelRatio = $_POST['pixelRatio'];

    $this->widthBreakPoints = $_POST['widthBreakPoints'];

    $newImageData = $this->getNewImageData();

    $this->imageFolder = $this->rootPath.'images/'.$newImageData->width.'/';

    $this->newImageSavePath = $this->imageFolder.$this->origImageName;

    if(is_file($this->newImageSavePath)):
      $this->returnImageData();
    endif;

    $this->resizeImage($newImageData);

    $this->returnImageData();
  }

  private function returnImageData() {
    $returnPathWithoutRootPath = str_replace($this->rootPath, '', $this->newImageSavePath);

    $imageData = array(
      'width'  => $this->getImageDimensions($this->newImageSavePath)->width,
      'height' => $this->getImageDimensions($this->newImageSavePath)->height,
      'path'   => $returnPathWithoutRootPath
    );
    echo json_encode($imageData);
    exit;
  }

  private function resizeImage($newImageData) {
    $newImage = imagecreatetruecolor($newImageData->width, $newImageData->height);

    $sourceImage = imagecreatefromjpeg($this->origImagePath);

    imagecopyresampled(
      $newImage,
      $sourceImage,
      0,
      0,
      0,
      0,
      $newImageData->width,
      $newImageData->height,
      $this->origImageWidth,
      $this->origImageHeight
    );

    if(!is_dir($this->imageFolder))
      mkdir($this->imageFolder, 0777, true);

    imagejpeg($newImage, $this->newImageSavePath);
  }

  private function getNewImageData() {
    foreach($this->widthBreakPoints as $widthBreakPoint):
      if($widthBreakPoint < $this->winWidth)
        continue;

      $newImageWidth = $widthBreakPoint;

      $imageRatio = $this->origImageWidth / $this->origImageHeight;

      $newImageHeight = $newImageWidth / $imageRatio;

      if($newImageHeight < $this->winHeight)
        continue;

      $newImageRatio = $newImageWidth / $newImageHeight;

      if($newImageRatio > $imageRatio)
        $newImageWidth = $newImageHeight * $imageRatio;

      break;
    endforeach;

    $dimensions = new stdClass();

    $dimensions->width  = $newImageWidth * $this->pixelRatio;
    $dimensions->height = $newImageHeight * $this->pixelRatio;

    return $dimensions;
  }

  private function getImageDimensions() {
    list($width, $height) = getimagesize($this->origImagePath);

    $dimensions = new stdClass();

    $dimensions->width  = $width;
    $dimensions->height = $height;

    return $dimensions;
  }
}

$fullscreenImage = new fullscreenImage();
$fullscreenImage->listenForPost();
<?

class fullscreenImage {

  public function listenForPost() {
    $imagePath   = $_POST['imagePath'];
    $imageName   = basename($imagePath);
    $imageSize   = getimagesize($imagePath);
    $imageWidth  = $imageSize[0];
    $imageHeight = $imageSize[1];

    $winWidth  = $_POST['winWidth'];
    $winHeight = $_POST['winHeight'];

    $widthBreakPoints = $_POST['widthBreakPoints'];

    foreach($widthBreakPoints as $widthBreakPoint):
      if($widthBreakPoint < $winWidth)
        continue;

      $newImageWidth = $widthBreakPoint;
      break;
    endforeach;

    $imageFolder = 'images/'.$newImageWidth.'/';

    $newImageSavePath = $imageFolder.$imageName;

    if(is_file($newImageSavePath)):
      $imageData = array(
        'width'  => $this->getImageDimensions($newImageSavePath)->width,
        'height' => $this->getImageDimensions($newImageSavePath)->height,
        'path'   => $newImageSavePath
      );
      echo json_encode($imageData);
      exit;
    endif;

    $imageRatio = $imageWidth / $imageHeight;

    $newImageHeight = -1;
    $newImageRatio = $newImageWidth / $newImageHeight;

    if($newImageRatio > $imageRatio)
      $newImageWidth = $newImageHeight * $imageRatio;
    else
      $newImageHeight = $newImageWidth / $imageRatio;

    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
    $sourceImage = imagecreatefromjpeg($imagePath);
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $imageWidth, $imageHeight);

    $imageFolder = 'images/'.$newImageWidth.'/';

    if(!is_dir($imageFolder))
      mkdir($imageFolder, 0777, true);

    imagejpeg($newImage, $newImageSavePath);

    $imageData = array(
      'width'  => $this->getImageDimensions($newImageSavePath)->width,
      'height' => $this->getImageDimensions($newImageSavePath)->height,
      'path'   => $newImageSavePath
    );
    echo json_encode($imageData);
  }

  private function getImageDimensions($imagePath) {
    list($width, $height) = getimagesize($imagePath);

    $dimensions = new stdClass();

    $dimensions->width  = $width;
    $dimensions->height = $height;

    return $dimensions;
  }
}

$fullscreenImage = new fullscreenImage();
$fullscreenImage->listenForPost();
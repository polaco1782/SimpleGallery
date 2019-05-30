<?php

function createImage($uploadedfile,$newWidth)
{
    //Change the filename to add the filetype
    $filename = "thumbs/".sha1($uploadedfile).".png";

    if(file_exists($filename))
    {
        printf("already encoded: %s, ", $filename);
        return;
    }

    // Capture the original size of the uploaded image
    if(!$info=getimagesize($uploadedfile))
      return false;
   
    switch ($info['mime'])
    {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($uploadedfile);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($uploadedfile);
            break;
        case 'image/png':
            $src = imagecreatefrompng($uploadedfile);
            break;
        default:
            return false;
    }
     
    $size = 200;
    $originalWidth = $info[0];
    $originalHeight = $info[1];
    $ratio = $originalWidth / $originalHeight;

    $targetWidth = $targetHeight = min($originalWidth, $originalHeight, $size);

    if ($ratio < 1) {
        $srcX = 0;
        $srcY = ($originalHeight / 2) - ($originalWidth / 2);
        $srcWidth = $srcHeight = $originalWidth;
    } else {
        $srcY = 0;
        $srcX = ($originalWidth / 2) - ($originalHeight / 2);
        $srcWidth = $srcHeight = $originalHeight;
    }

    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopyresampled($targetImage, $src, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $srcWidth, $srcHeight);
    imagepng($targetImage, $filename);
    imagedestroy($targetImage);
    imagedestroy($src);

    printf("%s: OK, ", $filename);

    return true;
}

function phpmain()
{
    $images_dir = "photos";
    mkdir("thumbs");

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($images_dir));
    $regex = new RegexIterator($iterator, '([^\s]+(\.(?i)(jpg|png|gif|bmp))$)', RecursiveRegexIterator::GET_MATCH);

    try
    {
        foreach($regex as $name => $object)
        {
            $time_start = microtime(true);

            printf("Encoding thumbnail... ");
            createImage($name, 128);

            $time_end = microtime(true);
            $execution_time = (float)($time_end - $time_start);

            printf("time: %f sec.\n", $execution_time);
        }
    }
    catch(Exception $e)
    {
        print "Failed to open: ".$e->GetMessage();
    }
}

phpmain();

?>

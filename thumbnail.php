<?php
include 'common.inc';
include 'object_detail.inc'; 
include 'page_data.inc';
$file = $_GET['file'];
$failed = false;

// make sure nobody is trying to use us to pull down external images from somewhere else
if( strpos($file, ':') === FALSE &&
    strpos($file, '//') === FALSE &&
    strpos($file, '\\') === FALSE )
{
    $fileParts = explode('.', $file);
    $parts = pathinfo($file);
    $type = $parts['extension'];

    $newWidth = 250;
    $w = $_REQUEST['width'];
    if( $w && $w > 20 && $w < 1000 )
        $newWidth = $w;
    $img = null;
    $mime = 'png';
    
    // see if we already have a cached image
    $cachedFile = "$testPath/$file.$newWidth.thm";
    if( is_file($cachedFile) )
    {
        if( !strcasecmp( $type, 'jpg') )
            $mime = 'jpeg';
        else
            $mime = 'png';
    }
    else
    {
        // see if it is a waterfall image
        if( strstr($parts['basename'], 'waterfall') !== false )
        {
            require_once('waterfall.inc');
            $secure = false;
            $haveLocations = false;
            $requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false);
            $pageData = loadPageRunData($testPath, $run, $cached);
            $options = array( 'id' => $id, 'path' => $testPath, 'run' => $run, 'cached' => $cached, 'cpu' => true );
            $img = drawWaterfall($url, $requests, $pageData, false, $options);
            if( !$requests || !$pageData )
                $failed = true;
        }
        elseif( strstr($parts['basename'], 'optimization') !== false )
        {
            require_once('optimizationChecklist.inc');
            $secure = false;
            $haveLocations = false;
            $requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false);
            $pageData = loadPageRunData($testPath, $run, $cached);
            $img = drawChecklist($url, $requests, $pageData);
            if( !$requests || !$pageData )
                $failed = true;
        }
        elseif( !strcasecmp( $type, 'jpg') )
            $img = imagecreatefromjpeg("$testPath/$file");
        elseif( !strcasecmp( $type, 'gif') )
            $img = imagecreatefromgif("$testPath/$file");
        else
            $img = imagecreatefrompng("$testPath/$file");

        if( $img )
        {
            // figure out what the height needs to be
            $width = imagesx($img);
            $height = imagesy($img);
            $scale = $newWidth / $width;
            $newHeight = (int)($height * $scale);
            
            # Create a new temporary image
            $tmp = imagecreatetruecolor($newWidth, $newHeight);

            # Copy and resize old image into new image
            imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($img);
            $img = $tmp;    
        }

        if( !$img )
        {
            // create a blank error image
            $img = imagecreatetruecolor($newWidth, $newWidth);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $newWidth, $newWidth, $black);
            $failed = true;
        }

        // output the image
        if( !strcasecmp( $type, 'jpg') )
        {
            $mime = 'jpeg';
            imagejpeg($img, $cachedFile);
        }
        else
        {
            $mime = 'png';
            imagepng($img, $cachedFile);
        }
    }
    
    // send back the cached file
    header ("Content-type: image/$mime");
    readfile_chunked($cachedFile);
    if( $failed )
        unlink($cachedFile);
}
?>

<?php
header ("Content-type: image/png");
include 'common.inc';
include 'object_detail.inc'; 
include 'page_data.inc';
$pageData = loadPageRunData($testPath, $run, $cached);

$mime = $_REQUEST['mime'];

// get all of the requests
$secure = false;
$haveLocations = false;
$requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false);
$cpu = true;
if( isset($_REQUEST['cpu']) && $_REQUEST['cpu'] == 0 )
    $cpu = false;
$bw = true;
if( isset($_REQUEST['bw']) && $_REQUEST['bw'] == 0 )
    $bw = false;
$dots = true;
if( isset($_REQUEST['dots']) && $_REQUEST['dots'] == 0 )
    $dots = false;
$options = array( 'id' => $id, 'path' => $testPath, 'run' => $run, 'cached' => $cached, 'cpu' => $cpu, 'bw' => $bw, 'dots' => $dots );

// see if we are doing a regular waterfall or a connection view
if( $_REQUEST['type'] == 'connection' )
{
    require_once('contentColors.inc');
    require_once('connectionView.inc');

    // get the color codes for the mime types
    $mimeColors = requestColors($requests);

    $summary = array();
    $connections = getConnections($requests, $summary);
    $im = drawImage($connections, $summary, $url, $mime, $mimeColors, false, $pageData, $options);
}
else
{
    require_once('waterfall.inc');
    $im = drawWaterfall($url, $requests, $pageData, false, $options);
}

// spit the image out to the browser
imagepng($im);
imagedestroy($im);
?>

<?php

// Shared code for creating the visual filmstrips

// build up the actual test data (needs to include testID and RUN in the requests)
$tests = array();
$fastest = null;
$ready = true;
$error = null;

$compTests = explode(',', $_REQUEST['tests']);
foreach($compTests as $t)
{
    $parts = explode('-', $t);
    if( count($parts) >= 1 )
    {
        $test = array();
        $test['id'] = $parts[0];
        $test['cached'] = 0;
        
        for( $i = 1; $i < count($parts); $i++ )
        {
            $p = explode(':', $parts[$i]);
            if( count($p) >= 2 )
            {
                if( $p[0] == 'r' )
                    $test['run'] = (int)$p[1];
                if( $p[0] == 'l' )
                    $test['label'] = $p[1];
                if( $p[0] == 'c' )
                    $test['cached'] = (int)$p[1];
            }
        }
        
        $test['path'] = GetTestPath($test['id']);
        $test['pageData'] = loadAllPageData($test['path']);
        
        $testInfo = parse_ini_file("./{$test['path']}/testinfo.ini",true);
        if( $testInfo !== FALSE )
        {
            if( isset($testInfo['test']) && isset($testInfo['test']['completeTime']) )
                $test['done'] = true;
            else
            {
                $test['done'] = false;
                $ready = false;
                
                if( isset($testInfo['test']) && isset($testInfo['test']['startTime']) )
                    $test['started'] = true;
                else
                    $test['started'] = false;
            }
            
            if( !$test['run'] )
                $test['run'] = GetMedianRun($test['pageData']);

            $loadTime = $test['pageData'][$test['run']][$test['cached']]['fullyLoaded'];
            if( isset($loadTime) && (!isset($fastest) || $loadTime < $fastest) )
                $fastest = $loadTime;

            $tests[] = $test;
        }
    }
}

$count = count($tests);
if( $count )
{
    setcookie('fs', urlencode($_REQUEST['tests']));
    LoadTestData();
}
else
    $error = "No valid tests selected.";

$thumbSize = $_REQUEST['thumbSize'];
if( !isset($thumbSize) || $thumbSize < 50 || $thumbSize > 500 )
{
    if( $count > 6 )
        $thumbSize = 100;
    elseif( $count > 4 )
        $thumbSize = 150;
    else
        $thumbSize = 200;
}

$interval = (int)$_REQUEST['ival'];
if( !$interval )
{
    if( isset($fastest) )
    {
        if( $fastest > 10000 )
            $interval = 1000;
        elseif( $fastest > 2000 )
            $interval = 500;
        else
            $interval = 100;
    }
    else
        $interval = 100;
}
$interval /= 100;

/**
* Load information about each of the tests (particularly about the video frames)
* 
*/
function LoadTestData()
{
    global $tests;

    foreach( $tests as &$test )
    {
        $testPath = GetTestPath($test['id']);
        $test['url'] = htmlspecialchars(file_get_contents("./$testPath/url.txt"));
        
        if( strlen($test['label']) )
            $test['name'] = $test['label'];
        else
            $test['name'] = htmlspecialchars(file_get_contents("./$testPath/label.txt"));
        if( !strlen($test['name']) )
        {
            $test['name'] = $test['url'];
            $test['name'] = str_replace('http://', '', $test['name']);
            $test['name'] = str_replace('https://', '', $test['name']);
        }
        
        $videoPath = "./$testPath/video_{$test['run']}";
        if( $test['cached'] )
            $videoPath .= '_cached';
        
        if( is_dir($videoPath) )
        {
            $test['video'] = array();
            $test['video']['start'] = 20000;
            $test['video']['end'] = 0;
            $test['video']['frames'] = array();
            
            // get the path to each of the video files
            $dir = opendir($videoPath);
            if( $dir )
            {
                while($file = readdir($dir)) 
                {
                    $path = $videoPath  . "/$file";
                    if( is_file($path) && !strncmp($file, 'frame_', 6) && strpos($file, '.thm') === false )
                    {
                        $parts = explode('_', $file);
                        if( count($parts) >= 2 )
                        {
                            $index = (int)$parts[1];
                            
                            if( $index < $test['video']['start'] )
                                $test['video']['start'] = $index;
                            if( $index > $test['video']['end'] )
                                $test['video']['end'] = $index;
                            
                            // figure out the dimensions of the source image
                            if( !$test['video']['width'] || !$test['video']['height'] )
                            {
                                $size = getimagesize($path);
                                $test['video']['width'] = $size[0];
                                $test['video']['height'] = $size[1];
                            }
                            
                            $test['video']['frames'][$index] = "$file";
                        }
                    }
                }

                closedir($dir);
            }
            
            if( !isset($test['video']['frames'][0]) )
                $test['video']['frames'][0] = $test['video']['frames'][$test['video']['start']];
        }
    }
}
?>

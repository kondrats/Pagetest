<?php 
// shared initializiation/loading code
error_reporting(0);

// load the install-specific settings
$settings = parse_ini_file('./settings/settings.ini');

$cdnPath = '';
if( isset($settings['cdn']) )
    $cdnPath = $settings['cdn'];

// allow for disabling of the mybb integration
if( $settings['disableMybb'] )
    unset($_COOKIE['mybbuser']);

// Load the test-specific data
$id = htmlspecialchars($_GET["test"]);

// if we didn't get an id explicitly, try from cookie
if( !$id )
    $id = $_COOKIE['tid'];

if( $id )
{
    // set the test ID cookie (session cookie) if we're looking at a different test
    if( strcmp($id, $_COOKIE['tid']) )
        setcookie('tid', $id);
    
    $testPath = './' . GetTestPath($id);
    $run = htmlspecialchars($_GET["run"]);
    if( !$run )
        $run = (int)1;
    $cached = (int)$_GET["cached"];
    $cachedText = '';
    if( $cached )
        $cachedText = '_Cached';
    if( is_file("$testPath/testinfo.ini") )
        $test = parse_ini_file("$testPath/testinfo.ini",true);
    if( is_file("$testPath/url.txt") )
        $url = htmlspecialchars(file_get_contents("$testPath/url.txt"));
    if( is_file("$testPath/dom.txt") )
        $dom = htmlspecialchars(file_get_contents("$testPath/dom.txt"));
    if( is_file("$testPath/login.txt") )
        $login = htmlspecialchars(file_get_contents("$testPath/login.txt"));
    if( is_file("$testPath/block.txt") )
        $blockString = htmlspecialchars(file_get_contents("$testPath/block.txt"));
}

/**
* Figure out the test path (relative) for the given test id
* 
* @param mixed $id
*/
function GetTestPath($id)
{
    global $settings;
    $testPath = "results/$id";
    if( strpos($id, '_') == 6 )
    {
        $parts = explode('_', $id);
        $testPath = 'results/' . substr($parts[0], 0, 2) . '/' . substr($parts[0], 2, 2) . '/' . substr($parts[0], 4, 2) . '/' . $parts[1];
    }
    elseif( strlen($settings['olddir']) )
    {
        if( $settings['oldsubdir'] )
            $testPath = "results/{$settings['olddir']}/_" . strtoupper(substr($id, 0, 1)) . "/$id";
        else
            $testPath = "results/{$settings['olddir']}/$id";
    }
    return $testPath;
}

/**
* Figure out the path to the video directory given an id
* 
* @param mixed $id
*/
function GetVideoPath($id)
{
    $path = "results/video/$id";
    if( strpos($id, '_') == 6 )
    {
        $parts = explode('_', $id);
        $path = 'results/video/' . substr($parts[0], 0, 2) . '/' . substr($parts[0], 2, 2) . '/' . substr($parts[0], 4, 2) . '/' . $parts[1];
    }

    return $path;
}

/**
* Get the default location
* 
*/
function GetDefaultLocation()
{
    $locations = parse_ini_file('./settings/locations.ini', true);
    $def = $locations['locations']['default'];
    if( !$def )
        $def = $locations['locations']['1'];
    $loc = $locations[$def]['default'];
    if( !$loc )
        $loc = $locations[$def]['1'];
    
    return $locations[$loc];
}

/**
* Recursively delete a directory
* 
* @param mixed $dir
*/
function delTree($dir) 
{
    $files = glob( $dir . '*', GLOB_MARK );
    foreach( $files as $file )
    {
        if( substr( $file, -1 ) == '/' )
            delTree( $file );
        else
            unlink( $file );
    }
   
    if( is_dir($dir) ) 
        rmdir( $dir );
   
} 

/**
* Send a large file a chunk at a time
* 
* @param mixed $filename
* @param mixed $retbytes
* @return bool
*/
function readfile_chunked($filename, $retbytes = TRUE) 
{
    $buffer = '';
    $cnt =0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) 
    {
        return false;
    }
    while (!feof($handle)) 
    {
        $buffer = fread($handle, 1024 * 1024);  // 1MB at a time
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) 
        {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) 
    {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}
?>

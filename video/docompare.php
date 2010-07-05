<?php
chdir('..');
include 'common.inc';

$urls = $_REQUEST['url'];
$labels = $_REQUEST['label'];
$ids = array();
$uid = null;
$user = null;
$ip = $_SERVER['REMOTE_ADDR'];

// some myBB integration to get the requesting user
if( is_dir('./forums') && isset($_COOKIE['mybbuser']) && !$test['uid'] && !$test['user'] )
{
    $dir = getcwd();
    try
    {
        define("IN_MYBB",1);
        chdir('forums'); // path to MyBB
        include './global.php';
        
        $uid = $mybb->user['uid'];
        $user = $mybb->user['username'];
    }
    catch(Exception $e)
    {
    }
    chdir($dir);
}

foreach( $urls as $index => $url )
{
    $url = trim($url);
    if( strlen($url) )
    {
        $id = SubmitTest($url, $labels[$index]);
        if( $id && strlen($id) )
            $ids[] = $id;
    }
}

// now add the industry urls
foreach( $_REQUEST['t'] as $tid )
{
    $tid = trim($tid);
    if( strlen($tid) )
        $ids[] = $tid;
}

// if we were successful, redirect to the result page
if( count($ids) )
{
    $idStr = '';
    if( $_GET['tid'] )
    {
        $idStr = $_GET['tid'];
        if( $_GET['tidlabel'] )
            $idStr .= '-l:' . urlencode($_GET['tidlabel']);
    }
    foreach($ids as $id)
    {
        if( strlen($idStr) )
            $idStr .= ',';
        $idStr .= $id;
    }
    
    $compareUrl = 'http://' . $_SERVER['HTTP_HOST'] . "/video/compare.php?tests=$idStr";
    header("Location: $compareUrl");    
}
else
{
    DisplayError();
}

/**
* Submit a video test request with the appropriate parameters
* 
* @param mixed $url
* @param mixed $label
*/
function SubmitTest($url, $label)
{
    global $uid;
    global $user;
    global $ip;
    $id = null;
    
    $testUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/runtest.php?';
    $testUrl .= 'f=xml&priority=2&runs=3&video=1&mv=1&fvonly=1&url=' . urlencode($url);
    if( $label && strlen($label) )
        $testUrl .= '&label=' . urlencode($label);
    if( $ip )
        $testUrl .= "&ip=$ip";
    if( $uid )
        $testUrl .= "&uid=$uid";
    if( $user )
        $testUrl .= '&user=' . urlencode($uid);
        
    // submit the request
    $result = simplexml_load_file($testUrl, 'SimpleXMLElement',LIBXML_NOERROR);
    if( $result && $result->data )
        $id = (string)$result->data->testId;
    
    return $id;
}

/**
* Something went wrong, give them an error message
* 
*/
function DisplayError()
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page visual comparison</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <style type="text/css">
            <?php 
                include 'pagestyle.css'; 
            ?>
        </style>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = null;
            $headerType = 'video';
            include 'header.inc';
            ?>
            <div class="content">
                <h1>There was an error running the test(s).</h1>
            </div>
        </div>
    </body>
</html>
<?php
}
?>

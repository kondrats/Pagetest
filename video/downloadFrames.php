<?php
chdir('..');
include 'common.inc';
require_once('video.inc');

$dir = "$testPath/video_$run";
if( $cached )
    $dir .= "_cached";
$ok = false;

if( is_dir($dir) )
{
    $file = "$dir/video.zip";
    BuildVideoScript($testPath, $dir);
    ZipVideo($dir);
    
    if( is_file($file) )
    {
        header('Content-disposition: attachment; filename=video.zip');
        header('Content-type: application/zip');
        readfile_chunked($file);
        unlink($file);
        $ok = true;
    }
}

if( !$ok )
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
            div.content
            {
                text-align:center;
                background: black;
                color: white;
                font-family: arial,sans-serif
            }
            .link
            {
                text-decoration: none;
                color: white;
            }
            #player
            {
                margin-left: auto;
                margin-right: auto;
            }
        </style>
        <script type="text/javascript" src="player/flowplayer-3.1.4.min.js"></script>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = null;
            include 'header.inc';
            ?>
            <div class="content">
                <h1>The video requested does not exist.</h1>
            </div>
        </div>
    </body>
</html>
<?php
}

?>

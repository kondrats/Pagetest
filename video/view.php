<?php
chdir('..');
include 'common.inc';
$id = $_REQUEST['id'];
$valid = false;
$done = false;

$ini = null;
$title = "Web page visual comparison";

$dir = GetVideoPath($id);
if( is_dir("./$dir") )
{
    $valid = true;
    $ini = parse_ini_file("./$dir/video.ini");
    if( isset($ini['completed']) )
    {
        $done = true;
        GenerateThumbnail("./$dir");
    }
    
    // get the video time
    $date = date("M j, Y", filemtime("./$dir"));
    if( is_file("./$dir/video.mp4")  )
        $date = date("M j, Y", filemtime("./$dir/video.mp4"));
    $title .= " - $date";

    $labels = json_decode(file_get_contents("./$dir/labels.txt"), true);
    if( count($labels) )
    {
        $title .= ' : ';
        foreach($labels as $index => $label)
        {
            if( $index > 0 )
                $title .= ", ";
            $title .= $label;
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo $title;?></title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <?php
        if( $valid && !$done )
            echo "<meta http-equiv=\"refresh\" content=\"10\"/>\n";
        ?>

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
        <script type="text/javascript" src="player/swfobject.js"></script>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Video';
            $videoId = $id;
            $headerType = 'video';
            include 'header.inc';
            ?>
            <div class="content">
            <?php
            if( $valid && $done )
            {
            ?>
            <?php
                $width = 400;
                $height = 300;
                if( $ini && $ini['width'] )
                    $width = $ini['width'];
                if( $ini && $ini['height'] )
                    $height = $ini['height'];
                $height += 28;  // adjust for the control bar
                echo '<div';
                echo " style=\"display:block; width:{$width}px; height:{$height}px\"";
                echo " id=\"player\">\n";
                echo "</div>\n";
                $hasThumb = false;
                if( is_file("./$dir/video.png") )
                    $hasThumb = true;

                // embed the actual player
                echo "<script>\n";
                echo "var so = new SWFObject('player/player.swf','mpl','$width','$height','9');\n";
                echo "so.addParam('allowfullscreen','true');\n";
                echo "so.addParam('allowscriptaccess','always');\n";
                echo "so.addParam('wmode','opaque');\n";
                echo "so.addVariable('file','/$dir/video.mp4');\n";
                if( is_file("./$dir/video.png") )
                    echo "so.addVariable('image','/$dir/video.png');\n";
                echo "so.write('player');\n";
                echo "</script>\n";

                echo "<br/><a class=\"link\" href=\"/video/download.php?id=$id\">Click here to download the video file...</a>\n";
            }
            elseif( $valid )
            {
            ?>
            <h1>Your video will be available shortly.  Please wait...</h1>
            <?php
            }
            else
            {
            ?>
            <h1>The video requested is invalid.</h1>
            <?php
            }
            ?>
            </div>
        </div>
    </body>
</html>

<?php
/**
* Generate a thumbnail for the video file if we don't already have one
* 
* @param mixed $dir
*/
function GenerateThumbnail($dir)
{
    $dir = realpath($dir);
    if( is_file("$dir/video.mp4") && !is_file("$dir/video.png") )
    {
        $output = array();
        $result;
        $command = "ffmpeg -i \"$dir/video.mp4\" -vframes 1 -ss 00:00:00 -f image2 \"$dir/video.png\"";
        $retStr = exec($command, $output, $result);
    }
}
?>

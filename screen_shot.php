<?php 
include 'common.inc';
require_once('video.inc');
require_once('page_data.inc');
$pageData = loadPageRunData($testPath, $run, $cached);

$videoPath = "$testPath/video_{$run}";
if( $cached )
    $videoPath .= '_cached';
    
// get the status messages
$messages = LoadStatusMessages($testPath . '/' . $run . $cachedText . '_status.txt');
    
// re-build the videos
MoveVideoFiles($testPath);
BuildVideoScript($testPath, $videoPath);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page screen shot</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <style type="text/css">
            <?php 
                include 'pagestyle.css'; 
            ?>
        img.center {
            display:block; 
            margin-left: auto;
            margin-right: auto;
        }
        div.content {
            text-align: center;
        }
        table {
            text-align: left;
            width: 50em;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        table th {
            padding: 0.2em 1em;
            text-align: left;
        }
        table td {
            padding: 0.2em 1em;
        }
        .time {
            white-space:nowrap; 
        }
        </style>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Screen Shot';
            include 'header.inc';
            ?>
            <div class="content">
                <?php
                    if( is_dir("./$videoPath") )
                    {
                        $createPath = "/video/create.php?tests=$id-r:$run-c:$cached&id={$id}.{$run}.{$cached}";
                        echo "<a href=\"$createPath\">Create Video</a> &#8226; ";
                        echo "<a href=\"/video/downloadFrames.php?test=$id&run=$run&cached=$cached\">Download Video Frames</a>";
                    }
                        
                    if($cached == 1)
                        $cachedText='_Cached';
                ?>
                <h1>Fully Loaded
                <?php
                if( isset($pageData) && isset($pageData['fullyLoaded']) )
                    echo ' (' . number_format($pageData['fullyLoaded'] / 1000.0, 3) . '  sec)';
                ?>
                </h1>
		        <img class="center" alt="Screen Shot" src="<?php echo substr($testPath, 1) . '/' . $run . $cachedText; ?>_screen.jpg" BORDER=0/>
                <?php
                    // display the last status message if we have one
                    if( count($messages) )
                    {
                        $lastMessage = end($messages);
                        if( strlen($lastMessage['message']) )
                            echo "\n<br>Last Status Message: \"{$lastMessage['message']}\"\n";
                    }
                    
                    if( is_file($testPath . '/' . $run . $cachedText . '_screen_render.jpg') )
                    {
                        echo '<br><br><h1>Start Render';
                        if( isset($pageData) && isset($pageData['render']) )
                            echo ' (' . number_format($pageData['render'] / 1000.0, 3) . '  sec)';
                        echo '</h1>';
                        echo '<img class="center" alt="Start Render Screen Shot" src="' . substr($testPath, 1) . '/' . $run . $cachedText . '_screen_render.jpg" BORDER=0/>';
                    }
                    if( is_file($testPath . '/' . $run . $cachedText . '_screen_dom.jpg') )
                    {
                        echo '<br><br><h1>DOM Element';
                        if( isset($pageData) && isset($pageData['domTime']) )
                            echo ' (' . number_format($pageData['domTime'] / 1000.0, 3) . '  sec)';
                        echo '</h1>';
                        echo '<img class="center" alt="DOM Element Screen Shot" src="' . substr($testPath, 1) . '/' . $run . $cachedText . '_screen_dom.jpg" BORDER=0/>';
                    }
                    if( is_file($testPath . '/' . $run . $cachedText . '_screen_doc.jpg') )
                    {
                        echo '<br><br><h1>Document Complete';
                        if( isset($pageData) && isset($pageData['docTime']) )
                            echo ' (' . number_format($pageData['docTime'] / 1000.0, 3) . '  sec)';
                        echo '</h1>';
                        echo '<img class="center" alt="Document Complete Screen Shot" src="' . substr($testPath, 1) . '/' . $run . $cachedText . '_screen_doc.jpg" BORDER=0/>';
                    }
                    
                    // display all of the status messages
                    if( count($messages) )
                    {
                        echo "\n<br><br><h1>Status Messages</h1>\n";
                        echo "<table><tr><th>Time</th><th>Message</th></tr>\n";
                        foreach( $messages as $message )
                            echo "<tr><td class=\"time\">{$message['time']} sec.</td><td>{$message['message']}</td></tr>";
                        echo "</table>\n";
                    }
                ?>
                <div style="width:100%;float:none;clear:both;"></div>
            </div>
        </div>
	</body>
</html>

<?php
/**
* Load the status messages into an array
* 
* @param mixed $path
*/
function LoadStatusMessages($path)
{
    $messages = array();
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach( $lines as $line )
    {
        $parts = explode("\t", $line);
        $time = (float)$parts[0] / 1000.0;
        $message = trim($parts[1]);
        if( $time >= 0.0 )
        {
            $msg = array(   'time' => $time,
                            'message' => $message );
            $messages[] = $msg;
        }
    }

    return $messages;
}

?>
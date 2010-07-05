<?php
ob_start();
set_time_limit(300);
include 'common.inc';
require_once('./lib/pclzip.lib.php');
$pub = $settings['publishTo'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo $settings['product'] . ' - where web sites go to get FAST!';?></title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, performance site web, internet performance, website performance, web applications testing, web application performance, Internet Tools, Web Development, Open Source, http viewer, debugger, http sniffer, ssl, monitor, http header, http header viewer">
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
            include 'header.inc';
            ?>
            <div class="content" style="text-align: center;">
            <?php
            echo "<p>Please wait wile the results are uploaded to $pub (could take several minutes)...</p>";
            ob_flush();
            flush();
            echo '<p>';
            $pubUrl = PublishResult();
            if( isset($pubUrl) && strlen($pubUrl) )
                echo "The test has been published to $pub and is available here: <a href=\"$pubUrl\">$pubUrl</a>";
            else
                echo "There was an error publishing the results to $pub. Please try again later";
                
            echo "</p><p><a href=\"/result/$id/\">Back to the test results</a></p>";
            ?>
            </div>
        </div>
    </body>
</html>

<?php

/**
* Publishj the current result
* 
*/
function PublishResult()
{
    global $testPath;
    global $pub;
    $result;
    
    // build the list of files to zip
    $files;
    $dir = opendir("$testPath");
    while($file = readdir($dir))
        if( $file != '.' && $file != '..' )
            $files[] = $testPath . "/$file";
    closedir($dir);

    if( isset($files) && count($files) )
    {    
        // zip up the results
        $zipFile = $testPath . '/publish.zip';
        $zip = new PclZip($zipFile);
        if( $zip->create($files, PCLZIP_OPT_REMOVE_ALL_PATH) != 0 )
        {
            // upload the actual file
            $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
            $data = "--$boundary\r\n";

            $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"publish.zip\"\r\n";
            $data .= "Content-Type: application/zip\r\n\r\n";
            $data .= file_get_contents($zipFile); 
            $data .= "\r\n--$boundary--\r\n";

            $params = array('http' => array(
                               'method' => 'POST',
                               'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
                               'content' => $data
                            ));

            $ctx = stream_context_create($params);
            $url = "http://$pub/work/dopublish.php";
            $fp = fopen($url, 'rb', false, $ctx);
            if( $fp )
            {
                $response = @stream_get_contents($fp);
                if( $response && strlen($response) )
                    $result = "http://$pub/result/$response/";
            }
            
            // delete the zip file
            unlink($zipFile);
        }
    }
    
    return $result;
}
?>

<?php
include 'common.inc';
include 'object_detail.inc'; 
include 'page_data.inc';
$secure = false;
$haveLocations = false;
$requests = getRequests($id, $testPath, $run, $_GET["cached"], $secure, $haveLocations, true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance test - page images</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <style type="text/css">
        <?php 
            include 'pagestyle.css'; 
        ?>
        .images td
        {
            vertical-align: top;
            padding-bottom: 1em;
        }
        </style>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = null;
            include 'header.inc';
            ?>
            <div class="content">
                <p>Page images for <b><a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a></b><br>
                Test completed - <?php echo $test[test][completeTime];?> from <?php echo $test[test][location];?>
                <?php
                if( $dom ) 
                    echo '<br>DOM Element: <b>' . $dom . '</b><br>';
                if( (int)$test[test][authenticated] == 1)
                    echo '<br><b>Authenticated: ' . $login . '</b>';
                if( (int)$test[test][connections] !== 0)
                     echo '<br><b>' . $test[test][connections] . ' Browser connections</b><br>';
                if( strlen($blockString) )
                    echo "<br>Blocked: <b>$blockString</b><br>";
                ?>
                </p>
                <br>
                <p>Images are what are currently being served from the given url and may not necessarily match what was loaded at the time of the test.</p>
                <table class="images">
                <?php
                foreach( $requests as &$request )
                {
                    if( strtolower(substr($request['contentType'], 0, 6)) == 'image/' )
                    {
                        $index = $request['index'] + 1;
                        echo "<tr><td><b>$index:</b></td><td>";
                        $reqUrl = "http://";
                        if( $request['secure'] )
                            $reqUrl = "https://";
                        $reqUrl .= $request['host'];
                        $reqUrl .= $request['url'];
                        echo "$reqUrl<br />";
                        $kb = number_format(((float)$request['objectSize'] / 1024.0), 1);
                        echo "$kb KB {$request['contentType']}<br />";
                        echo "<img src=\"$reqUrl\" />";
                        echo "</td></tr>\n";
                    }
                }
                ?>
                </table>
            </div>
        </div>
    </body>
</html>

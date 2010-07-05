<?php
chdir('..');
include 'common.inc';
$loc = GetDefaultLocation();
$tid=$_GET['tid'];
$run=$_GET['run'];
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
            .urldiv
            {
                padding-top: 0.5em;
            }
            .industry
            {
                margin-right: 2em;
                float: left;
                padding-bottom: 1em;
                width: 14em;
                min-height: 10em;
                font-size: smaller;
            }
            .indHead
            {
                padding-left: 10px;
                padding-top: 2px;
                padding-bottom: 2px;
                background-color: #000040;
                color: white;
                width: 100%;
            }
            .indBody
            {
                padding-left: 1em;
                width: 100%;
            }
            #footer
            {
                clear: both;
            }
            h1
            {
                text-align: center;
            }
        </style>
        <script type="text/javascript" src="<?php echo $cdnPath; ?>/js/jquery.min.js"></script> 
        <script type="text/javascript" src="<?php echo $cdnPath; ?>/video/videotest.js"></script> 
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'New Comparison';
            $headerType = 'video';
            include 'header.inc';
            ?>
            <div class="content">
            <h1>Visual Page Comparison</h1>
            <p>Enter multiple urls to compare them against each other visually.</p>
            <form name="urlEntry" action="/video/docompare.php" method="POST" onsubmit="return ValidateInput(this)">
                <input type="hidden" id="nextid" value="2">
                <div id="urls">
                    <?php
                    if( $tid )
                    {
                        $testPath = './' . GetTestPath($tid);
                        $url = htmlspecialchars(file_get_contents("$testPath/url.txt"));
                        $label = htmlspecialchars(file_get_contents("$testPath/label.txt"));
                        if( strlen($url) )
                        {
                            echo '<div id="urldiv0" class="urldiv">';
                            echo "<input type=\"hidden\" id=\"tid\" name=\"tid\" value=\"$tid\">";
                            echo "<input type=\"hidden\" id=\"run\" name=\"run\" value=\"$run\">";
                            echo "Label: <input id=\"tidlabel\" type=\"text\" name=\"tidlabel\" value=\"$label\" style=\"width:10em\"> ";
                            echo "URL: <input id=\"tidurl\" type=\"text\" style=\"width:30em\" value=\"$url\" disabled=\"disabled\"> ";
                            echo "<a href='#' onClick='return RemoveUrl(\"#urldiv0\");'>Remove</a>";
                            echo "</div>\n";
                        }
                    }
                    ?>
                    <div id="urldiv1" class="urldiv">
                        Label: <input id="label1" type="text" name="label[1]" style="width:10em"> 
                        URL: <input id="url1" type="text" name="url[1]" style="width:30em"> 
                        <a href='#' onClick='return RemoveUrl("#urldiv1");'>Remove</a>
                    </div>
                </div>
                <br />
                <button onclick="return AddUrl();">Add</button> another URL to the comparison.
                <br />
                <?php
                // load the main industry list
                $ind = parse_ini_file('./video/industry.ini', true);
                $ids = json_decode(file_get_contents('./video/dat/industry.dat'), true);
                if( $ind && count($ind) && $ids && count($ids) )
                {
                    $i = 0;
                    echo "<p>and/or compare against industry pages:</p>\n";
                    foreach($ind as $industry => &$pages )
                    {
                        if( $ids[$industry] )
                        {
                            echo "<div class=\"industry\">\n";
                            echo "<div class=\"indHead\">$industry:</div>\n";
                            echo "<div class=\"indBody\">\n";
                            foreach( $pages as $page => $url )
                            {
                                $details = $ids[$industry][$page];
                                if( $details )
                                {
                                    $i++;
                                    $tid = $details['id'];
                                    $date = $details['last_updated'];
                                    echo "<input type=\"checkbox\" name=\"t[]\" value=\"$tid\"> $page";
                                    if( $date )
                                    {
                                        $date = date('m/d/y', strtotime($date));
                                        echo " ($date)";
                                    }
                                    echo "<br>\n";
                                }
                            }
                            echo "</div></div>\n";
                        }
                    }
                }
                ?>
                <p id="footer">For each URL, 3 first-view tests will be run from '<?php echo $loc['label']; ?>' and the median run will be used for comparison.  
                The tests will also be publically available.  If you would like to test with different settings, submit your tests individually from the 
                <a href="/test">main test page</a>.</p>
                <input id="submitbtn" type="submit" value="Run Visual Comparison" name="submitbtn">
            </form>
            </div>
        </div>
    </body>
</html>

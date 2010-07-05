<?php
require_once('optimization_detail.inc.php');

$fv = null;
$rv = null;
$video = false;
if( $test['test']['Capture Video'] )
    $video = true;
    
$pageStats = calculatePageStats($pageData, $fv, $rv);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance test results</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <?php if( !$test['test']['completeTime'] ) { ?>
        <meta http-equiv="refresh" content="30"/>
        <?php } ?>
        <style type="text/css">
            <?php 
            include 'pagestyle.css'; 
            ?>
            h1
            {
                font-size: larger;
            }
            
            #opt
            {
                margin-bottom: 2em;
            }
            #opt_table
            {
                border: 1px solid black;
                border-collapse: collapse;
            }
            #opt_table th
            {
                padding: 5px;
                border: 1px solid black;
                font-weight: normal;
            }
            #opt_table td
            {
                padding: 5px;
                border: 1px solid black;
                font-weight: bold;
            }
        </style>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Summary';
            include 'header.inc';
            ?>
            <div class="content">
			    <div style="float:right; text-align:right;">
                    <?php
                        if( $test['test']['completeTime'] )
                        {
				            echo '<a href="/result/' . $id . '/page_data.csv">Raw page data</a> - <a href="/result/' . $id . '/requests.csv">Raw object data</a>';
                            echo '<br><a href="/export.php?test=' . $id . '">Export HTTP Archive (.har)</a>';
                            if( $settings['publishTo'] )
                                echo "<br><a href=\"/publish.php?test=$id\">Publish to {$settings['publishTo']}</a>";
                        }
                    ?>
			    </div>
		        <div style="float:left;">
			        Web page performance test results for <b><a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a></b><br>
			        Test completed - <?php echo $test['test']['completeTime']; if( strlen($test['test']['location']) ) echo ' from ' . $test['test']['location'];?><br>
			        <?php
                    if( $dom ) 
                        echo 'DOM Element: <b>' . $dom . '</b><br>';
			        if( (int)$test['test']['authenticated'] == 1)
				        echo '<b>Authenticated: ' . $login . '</b><br>';
			        if( (int)$test['test']['connections'] !== 0)
				         echo '<b>' . $test[test][connections] . ' Browser connections</b><br>';
                    if( $test['test']['script'] ) 
                        echo '<b>Scripted test</b><br>';
                    if( strlen($blockString) )
                        echo "Blocked: <b>$blockString</b><br>";
			        ?>
		        </div>
                <div style="text-align:center; width:100%; clear:both;">
                <br>
                <?php if( !$test['test']['completeTime'] ) { ?>
                    <h3>Test is partially complete.  This page will refresh every 30 seconds until the full test is complete.</h3>
                <?php } ?>
                <?php if( $test['test']['completeTime'] ) { ?>
                <div id="average" style="text-align:center;">
                    <table id="tableResults" class="pretty" align="center" border="1" cellpadding="10" cellspacing="0">
                    <tr>
                        <?php
                        $cols = 3;
                        if((float)$fv['domTime'] > 0.0)
                            $cols++;
                        ?>
                        <th align="center" class="empty" valign="middle" style="border:1px white solid;"></th>
                        <th align="center" class="empty" valign="middle" colspan=<?php echo "\"$cols\"";?> ></th>
                        <th align="center" class="border" valign="middle" colspan="3">Document Complete</th>
                        <th align="center" class="border" valign="middle" colspan="3">Fully Loaded</th>
                    </tr>
                    <tr>
                        <th align="center" class="empty" valign="middle"></th>
                        <th align="center" valign="middle">Load Time</th>
                        <th align="center" valign="middle">First Byte</th>
                        <th align="center" valign="middle">Start Render</th>
                        <?php if((float)$fv['domTime'] > 0.0) { ?>
                        <th align="center" valign="middle">DOM Element</th>
                        <?php } ?>

                        <th align="center" class="border" valign="middle">Time</th>
                        <th align="center" valign="middle">Requests</th>
                        <th align="center" valign="middle">Bytes In</th>

                        <th align="center" class="border" valign="middle">Time</th>
                        <th align="center" valign="middle">Requests</th>
                        <th align="center" valign="middle">Bytes In</th>
                    </tr>
                    <tr>
                        <td align="left" valign="middle">First View</td>
                        <?php
                        echo "<td id=\"fvLoadTime\" valign=\"middle\">" . number_format($fv['loadTime'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"fvTTFB\" valign=\"middle\">" . number_format($fv['TTFB'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"fvStartRender\" valign=\"middle\">" . number_format($fv['render'] / 1000.0, 3) . "s</td>\n";
                        if((float)$fv['domTime'] > 0.0)
                            echo "<td id=\"fvDomElement\" valign=\"middle\">" . number_format($fv['domTime'] / 1000.0, 3) . "s</td>\n";

                        echo "<td id=\"fvDocComplete\" class=\"border\" valign=\"middle\">" . number_format($fv['docTime'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"fvRequestsDoc\" valign=\"middle\">" . number_format($fv['requestsDoc'],0) . "</td>\n";
                        echo "<td id=\"fvBytesDoc\" valign=\"middle\">" . number_format($fv['bytesInDoc'] / 1024, 0) . " KB</td>\n";

                        echo "<td id=\"fvFullyLoaded\" class=\"border\" valign=\"middle\">" . number_format($fv['fullyLoaded'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"fvRequests\" valign=\"middle\">" . number_format($fv['requests'],0) . "</td>\n";
                        echo "<td id=\"fvBytes\" valign=\"middle\">" . number_format($fv['bytesIn'] / 1024, 0) . " KB</td>\n";
                        ?>
                    </tr>
                    <?php if( isset($rv) ) { ?>
                    <tr>
                        <td align="left" class="even" valign="middle">Repeat View</td>
                        <?php
                        echo "<td id=\"rvLoadTime\" class=\"even\" valign=\"middle\">" . number_format($rv['loadTime'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"rvTTFB\" class=\"even\" valign=\"middle\">" . number_format($rv['TTFB'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"rvStartRender\" class=\"even\" valign=\"middle\">" . number_format($rv['render'] / 1000.0, 3) . "s</td>\n";
                        if((float)$rv['domTime'] > 0.0)
                            echo "<td id=\"rvDomElement\" class=\"even\" valign=\"middle\">" . number_format($rv['domTime'] / 1000.0, 3) . "s</td>\n";

                        echo "<td id=\"rvDocComplete\" class=\"even border\" valign=\"middle\">" . number_format($rv['docTime'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"rvRequestsDoc\" class=\"even\" valign=\"middle\">" . number_format($rv['requestsDoc'],0) . "</td>\n";
                        echo "<td id=\"rvBytesDoc\" class=\"even\" valign=\"middle\">" . number_format($rv['bytesInDoc'] / 1024, 0) . " KB</td>\n";

                        echo "<td id=\"rvFullyLoaded\" class=\"even border\" valign=\"middle\">" . number_format($rv['fullyLoaded'] / 1000.0, 3) . "s</td>\n";
                        echo "<td id=\"rvRequests\" class=\"even\" valign=\"middle\">" . number_format($rv['requests'],0) . "</td>\n";
                        echo "<td id=\"rvBytes\" class=\"even\" valign=\"middle\">" . number_format($rv['bytesIn'] / 1024, 0) . " KB</td>\n";
                        ?>
                    </tr>
                    <?php } ?>
                    </table><br>
                    <?php if( count($pageData) > 1 && isset($fv['avgRun']) ) 
                            echo '<b><a href="#run' . $fv['avgRun'] . '">Run ' . $fv['avgRun'] . '</a></b> is the closest to the average load time.<br/><br/>'; ?>
                </div>
                <div id="opt" style="text-align:center;">
                    <h1>Key Optimizations</h1>
                    <table id="opt_table" align="center">
                        <?php
                        $optRun = 1;
                        if( isset($fv['avgRun']) )
                            $optRun = $fv['avgRun'];
                        echo keyOptimizationsTable($testPath, $optRun, 0);
                        ?>
                    </table>
                    <?php
                    echo "<a href=\"/result/$id/$optRun/performance_optimization/\">click for optimization details</a>\n";
                    ?>
                </div>
                <?php } ?>
                <div id="tables" style="text-align:left;">
                <h1 style="text-align:center;">Test Results</h1>
                <?php
                $runs = count($pageData);
                $fvonly = 1;
                if( isset($rv) )
                    $fvonly = 0;
                for( $run = 1; $run <= $runs; $run++)
                {
                    if( $runs > 1 )
                        echo '<h4><a name="run' . $run. '">Run ' . $run . ':</a></h4>';
                ?>
                    <table id="table<?php echo $run;?>" class="pretty" align="center" border="1" cellpadding="20" cellspacing="0">
                    <tr>
                        <th align="center" class="empty" valign="middle"></th>
                        <th align="center" valign="middle">Waterfall</th>
                        <th align="center" valign="middle">Optimization Checklist</th>
                        <th align="center" valign="middle">Screen Shot</th>
                        <?php if($video) { ?>
                        <th align="center" valign="middle">Video</th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td align="left" valign="middle">First View<?php 
                            if( isset($pageData[$run][0]['result']) && $pageData[$run][0]['result'] !== 0 && $pageData[$run][0]['result'] !== 99999 )
                                echo '<br>(Error: ' . LookupError($pageData[$run][0]['result']) . ')';
                            else if( isset($pageData[$run][0]['loadTime']) )
                                echo '<br>(' . number_format($pageData[$run][0]['loadTime'] / 1000.0, 3) . 's)';
                        ?></td>
                        <td align="center" valign="middle"><a href="/result/<?php echo "$id/$run/details/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_waterfall_thumb.png" BORDER=0/></a></td>
                        <td align="center" valign="middle"><a href="/result/<?php echo "$id/$run/performance_optimization/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_optimization_thumb.png" BORDER=0/></a></td>
                        <td align="center" valign="middle"><a href="/result/<?php echo "$id/$run/screen_shot/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_screen_thumb.jpg" BORDER=0/></a></td>
                        <?php if($video) { ?>
                        <td align="center" valign="middle">
                            <?php
                            if( is_dir("$testPath/video_$run") )
                            {
                                echo "<a href=\"/video/compare.php?tests=$id-r:$run-c:0\">Filmstrip View</a><br />-<br />";
                                echo "<a href=\"/video/create.php?tests=$id-r:$run-c:0&id={$id}.{$run}.0\">Watch Video</a><br />-<br />";
                                echo "<a href=\"/video/?tid=$id&run=$run&cached=0\">Compare to</a>";
                            }
                            else
                                echo "not available";
                            ?>
                        </td>
                        <?php } ?>
                    </tr>

                    <?php if( $fvonly == 0 ) { ?>
                    <tr>
                        <td align="left" class="even" valign="middle">Repeat View<?php 
                            if( isset($pageData[$run][1]['result']) && $pageData[$run][1]['result'] !== 0 && $pageData[$run][1]['result'] !== 99999 )
                                echo '<br>(Error: ' . LookupError($pageData[$run][1]['result']) . ')';
                            else if( isset($pageData[$run][1]['loadTime']) )
                                echo '<br>(' . number_format($pageData[$run][1]['loadTime'] / 1000.0, 3) . 's)';
                        ?></td>
                        <td align="center" class="even" valign="middle"><a href="/result/<?php echo "$id/$run/details/cached/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_Cached_waterfall_thumb.png" BORDER=0/></a></td>
                        <td align="center" class="even" valign="middle"><a href="/result/<?php echo "$id/$run/performance_optimization/cached/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_Cached_optimization_thumb.png" BORDER=0/></a></td>
                        <td align="center" class="even" valign="middle"><a href="/result/<?php echo "$id/$run/screen_shot/cached/"; ?>"><img width=250 src="<?php echo "/result/$id/$run"; ?>_Cached_screen_thumb.jpg" BORDER=0/></a></td>
                        <?php if($video) { ?>
                        <td align="center" valign="middle">
                            <?php
                            if( is_dir("$testPath/video_{$run}_cached") )
                            {
                                echo "<a href=\"/video/compare.php?tests=$id-r:$run-c:1\">Filmstrip View</a><br />-<br />";
                                echo "<a href=\"/video/create.php?tests=$id-r:$run-c:1&id={$id}.{$run}.1\">Watch Video</a>";
                            }
                            else
                                echo "not available";
                            ?>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                    
                    <tr>
                        <td align="left" valign="middle">Content Breakdown</td>
                        <?php
                        $span = 3;
                        if( $video )
                            $span = 4;
                        echo "<td align=\"left\" valign=\"middle\" colspan=\"$span\">";
                        ?>
                            <a href="/result/<?php echo "$id/$run/breakdown/"; ?>">
                            <img width=390 height=200 src="/breakdownPie.png?width=390&height=200&type=Requests&fontSize=8<?php echo "&test=$id&run=$run"; ?>" BORDER=0/>
                            <img width=390 height=200 src="/breakdownPie.png?width=390&height=200&type=Bytes&fontSize=8<?php echo "&test=$id&run=$run"; ?>" BORDER=0/>
                            </a>
                        </td>
                    </tr>
                    
                    </table><br>
                <?php
                }
                ?>
                </div>
                </div>

		        <br>
                <?php include('./ads/results_bottom.inc'); ?>
		        <br>
            </div>
        </div>
    </body>
</html>

<?php
/**
* Translate an error code into the text description
* 
* @param mixed $error
*/
function LookupError($error)
{
    $errorText = $error;
    
    switch($error)
    {
        case 7: $errorText = "Invalid SSL Cert."; break;
        case 99996: $errorText = "Timed Out waiting for DOM element"; break;
        case 99997: $errorText = "Timed Out"; break;
        case 99998: $errorText = "Timed Out"; break;
        case 88888: $errorText = "Script Error"; break;
        case -2146697211: $errorText = "Failed to Connect"; break;
    }
    
    return $errorText;
}
?>

<?php 
include 'common.inc';
require_once('optimization_detail.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance optimization check results</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <style type="text/css">
            <?php 
                include 'pagestyle.css'; 
            ?>
            td.nowrap {white-space:nowrap;}
            th.nowrap {white-space:nowrap;}
            tr.blank {height:2ex;}
			.indented1 {padding-left: 40pt;}
			.indented2 {padding-left: 80pt;}
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
            $subtab = 'Performance Review';
            include 'header.inc';
            ?>
            <div class="content">
				<p>Web page performance optimization check results for <b><a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a></b><br>
				Test completed - <?php echo $test[test][completeTime];?> from <?php echo $test[test][location];?>
				<?php
				if( (int)$test[test][authenticated] == 1)
					echo '<br><b>Authenticated: ' . $login . '</b>';
				if( (int)$test[test][connections] !== 0)
					 echo '<br><b>' . $test[test][connections] . ' Browser connections</b><br>';
                if( strlen($blockString) )
                    echo "<br>Blocked: <b>$blockString</b><br>";
				?>
				</p>
                <div id="opt" style="text-align:center;">
                    <h1>Key Optimizations</h1>
                    <table id="opt_table" align="center">
                        <?php
                        echo keyOptimizationsTable($testPath, $run, $cached);
                        ?>
                    </table>
                </div>
                <div style="text-align:center;">
                    <h1>Full Optimization Checklist</h1>
                    <img alt="If the optimization results don't display, please try refreshing the page" id="image" src="<?php 
                        echo substr($testPath, 1) . '/' . $run . $cachedText . '_optimization.png"';?>>
                    <br>
                </div>

		        <br>
                <?php include('./ads/optimization_middle.inc'); ?>
		        <br>

                <h2>Details:</h2>
                <?php
                    require 'optimization.inc';
                    dumpOptimizationReport($testPath, $run, $cached);
                    echo '<p></p><br>';
                    include('./ads/optimization_bottom.inc');
                    echo '<br>';
                    dumpOptimizationGlossary($settings);
                ?>
            </div>
        </div>
    </body>
</html>

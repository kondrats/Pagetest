<?php 
include 'common.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance optimization report</title>
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
        </style>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Optimization Report';
            include 'header.inc';
            ?>
            <div class="content">
				<p>Web page performance optimization report for <b><a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a></b><br>
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
                <?php
                    require 'optimization.inc';
                    dumpOptimizationReport($testPath, $run, $cached, true);
                    echo '<br>';
                    dumpOptimizationGlossary($settings);
                ?>
            </div>
        </div>
    </body>
</html>

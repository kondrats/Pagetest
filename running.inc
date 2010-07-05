<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Running web page performance and optimization test...</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <meta http-equiv="refresh" content="10"/>
        <style type="text/css">
        <?php 
            include 'pagestyle.css'; 
        ?>
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            include 'header.inc';
            ?>
            <div class="content">
                <h2>Testing...</h2>
                <p>
                Url: <a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a><br />
                From: <?php echo $test['test']['location'];?><br />
                <?php if( $dom ) echo 'DOM Element: <b>' . $dom . '</b><br>'; ?>
                <?php if( $test['test']['fvonly'] ) echo '<b>First View only</b><br>'; ?>
                <?php if( (int)$test['test']['runs'] > 1 ) echo 'Test runs: <b>' . $test['test']['runs'] . '</b><br>'; ?>
                <?php if( (int)$test['test']['authenticated'] == 1) echo '<br><b>Authenticated: ' . $login . '</b>'; ?>
                <?php if( (int)$test['test']['connections'] !== 0) echo '<b>' . $test['test']['connections'] . ' Browser connections</b><br>';?>
                <?php if( $test['test']['script'] ) echo '<b>Scripted test</b><br>'; ?>
                </p>
                <?php if( $test['test']['startTime'] ) echo $test['test']['startTime'] . ' - Test Started<br>'; ?>
                <?php if( $test['test']['repeatTime'] ) echo $test['test']['repeatTime'] . ' - First View Complete, started testing Repeat View<br>'; ?>
                <p>Your web page performance test has been submitted and is now being processed.  This page will automatically refresh with the test status as 
                the test continues to run and when the test is complete the final results will be available here.</p>
                <p>You can either leave this page open in your browser until the test
                 is complete or come back later and check on the status of the test (the browser does not need to remain open for testing to continue).</p>

		        <br><br>
                <?php include('./ads/test_progress_bottom.inc'); ?>
            </div>
        </div>
    </body>
</html>

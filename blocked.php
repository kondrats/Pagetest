<?php 
require_once('common.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Access Denied</title>
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
            <div class="content">
                <h1>Oops...</h1>
                <p>Your test request was intercepted by our spam filters (or because we need to talk to you about how you are submitting tests).  Most free web hosts have been blocked from testing because of excessive link spam.  
                <?php if($settings['contact']) echo 'If there is a site you want tested that was blocked, please <a href="mailto:' . $settings['contact'] . '">contact us</a>'. ' and send us your IP address (below) and URL that you are trying to test'; ?>.</p>
                <p>
                Your IP address: <b><?php echo $_SERVER['REMOTE_ADDR']; ?></b><br>
                </p>
            </div>
        </div>
    </body>
</html>

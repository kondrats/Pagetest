<?php 
include 'common.inc';
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
            $tab = 'Home';
            include 'header.inc';
            ?>
            <div class="content">
                <!-- google_ad_section_start -->
                WebPagetest is currently unavailable - A router software update went bad and we are in the process of getting things running again.  
                Shouldn't be down for more than a couple of hours, sorry for the inconvenience (and a perfect case of the risks involved in remotely updating code).
                <!-- google_ad_section_end -->
                <h2 style="text-align:center;"><a href="/test">Start Testing</a></h2>
            </div>
        </div>
	</body>
</html>

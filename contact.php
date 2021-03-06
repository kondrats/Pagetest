<?php 
include 'common.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo $settings['product'] . ' web page performance test';?></title>
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
            $tab = 'Contact';
            include 'header.inc';
            ?>
            <div class="content">
            <?php
            if( is_file('./settings/contact.inc') )
                include './settings/contact.inc';
            else
                echo 'If you are having any problems of just have questions about the site, please feel free to <a href="mailto:' . $settings['contact'] . '">contact us</a>.';
            ?>
            </div>
        </div>
    </body>
</html>

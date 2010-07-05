<?php 
chdir('..');
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
            include 'style.css';
        ?>
        </style>
    </head>
    <body>
        <div class="page">
            <?php
            include 'header.inc';
            ?>
            <div class="content">
               <form name="urlEntry" action="bulktest.php" method="POST">
                    <div class="stepname">Location:</div>
                    <div class="stepcontents">
                    <input id="locationDulles" type="radio" checked=checked name="location" value="Dulles">Dulles<br>
                    <input id="locationOffice" type="radio" name="location" value="Office">Office LAN<br>
                    </div>
                    <br>
                    <div class="stepname">Enter list of urls to test (one per line):</div>
                    <br>
                    <div class="stepcontents"><textarea id="urls" rows="30" cols="80" name="urls"></textarea><br></div>
                    <br>
                    <div class="greytextbutton"><input class="artzBtn def" id="Submit" type="submit" value="Submit"></div>
                    <br>
               </form>
            </div>
        </div>
    </body>
</html>

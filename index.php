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
        <meta name="google-site-verification" content="Ll0Vtft15M5ultcLb-xXOHOaXRjeawvHmY8Cw_1GgjQ" />
        <meta name="msvalidate.01" content="72FFE46381F8A781FDE4BC4F84DCE459" />
        <meta name="author" content="Patrick Meenan">
        <style type="text/css">
        <?php 
            include 'pagestyle.css'; 
        ?>
        #testing
        {
            border: solid 1px black;
            border-collapse: collapse;
            text-align:center;
            table-layout: fixed;
            margin-left: auto;
            margin-right: auto;
            margin-top: 1em;
            margin-bottom: 1em;
            width: 700px;
        }
        #testing th
        {
            border: solid 1px black;
            font-size: x-large;
            padding: 10px;
        }
        #testing td
        {
            border: solid 1px black;
            padding: 10px;
            vertical-align: top;
        }
        #testing img
        {
            border: none;
        }
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
                <?php include('./settings/intro.inc'); ?>
                <!-- google_ad_section_end -->
                <table id="testing">
                <tr><th colspan="2">Start Testing</th></tr>
                <tr>
                <td><a href="/test">By The Numbers</a><br /><br /><a href="/test"><img src="<?php echo $cdnPath; ?>/images/numbers.png" /></a></td>
                <td><a href="/video/">Visual Comparison</a><br /><br /><a href="/video/"><img src="<?php echo $cdnPath; ?>/images/visual.png" /></a></td>
                </tr>
                </table>
                <?php include('./settings/intro_more.inc'); ?>
            </div>
        </div>
	</body>
</html>

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
            <?php
                $urls = explode("\n", $_REQUEST['urls']);
                if( count($urls) )
                {
                    echo "<OL><LH>Click on an url to open it's test result (results will open in a new tab or window):</LH>\n";
                    foreach($urls as $url)
                    {
                        $url = trim($url);
                        if( strlen($url) )
                        {
                            $testResult = TestUrl($url);
                            if( strlen($testResult) )
                                echo "<LI><a href=\"$testResult\" target=\"_blank\">$url</a></LI>\n";
                            else
                                echo "<LI>Error: $url</LI>\n";
                        }
                    }
                    echo "</OL>\n";
                }
                else
                    echo 'No URLS submitted for testing';
            ?>
            </div>
        </div>
    </body>
</html>

<?php
/**
* Submit an url for testing
* 
* @param mixed $url
*/
function TestUrl($url)
{
    $ret;
    
    // hard-code it to the office system (for now at least)
    $testUrl = 'http://pagetest.office.aol.com/runtest.php?f=xml&location=' . $_REQUEST['location'] . '&url=' . rawurlencode($url);
    $doc = new DOMDocument();
    $doc->load($testUrl);
    $nodes = $doc->getElementsByTagName('userUrl');
    $ret = trim($nodes->item(0)->nodeValue);

    return $ret;
}
?>
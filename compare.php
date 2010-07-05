<?php
include 'common.inc';
require_once('page_data.inc');

// get the list of tests
$tests = $_REQUEST['t'];
$runs = $_REQUEST['r'];
$labels = $_REQUEST['l'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance test comparison</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
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
            $tab = null;
            include 'header.inc';
            ?>
            <div class="content">
                <form  name="compare" method="get" action="/compare.php">
                    <h1>This is still experimental and under heavy development.  Feel free to poke around but don't be surprised if things don't look right or work.</h1>
                    <table>
                    <h1>Comparison Settings:</h1>
                    <tr><th>Test ID</th><th>Run</th><th>Label</th><th>Url</th></tr>
                    <?php
                    foreach( $tests as $index => $test )
                    {
                        echo "<tr><td><a target=\"_blank\" href=\"/result/$test/\">$test</a><input type=\"hidden\" name=\"t[$index]\" value=\"$test\" /></td>\n";
                        
                        // populate the runs
                        $run = $runs[$index];
                        if(!$run )
                            $run = 1;
                        $runCount = 10;
                        echo "<td><select id=\"run$index\" name=\"r[$index]\">\n";
                        for( $i = 1; $i <= $runCount; $i++ )
                        {
                            $selected = '';
                            if( $run == $i )
                                $selected = ' selected';
                            echo "<option value=\"$i\" $selected>$i</option>\n";
                        }
                        echo '</select></td>';
                        
                        // populate the label
                        echo "<td><input id=\"label$index\" type=\"text\" name=\"l[$index]\" style=\"width:30em\" value=\"{$labels[$index]}\" /></td>";
                        
                        // populate the url
                        echo "<td><a rel=\"nofollow\" href=\"$url\">$url</a></td>\n";
                        
                        echo "</tr>\n";
                    }
                    ?>
                    </table>
                    <input id="CompareBtn" type="submit" value="Update Comparison">
                </form>
                
                <h1>Load Times:</h1>
                <?php
                    echo "<img id=\"chartLoad\" alt=\"Load  Times Chart\" src=\"/compareChart.php?type=times" . CompParams() . "\">";
                ?>
            </div>
        </div>
    </body>
</html>

<?php
/**
* Spit out the standard paramaters that we need to pass to everything
* 
*/
function CompParams()
{
    $out = '';
    
    global $tests;
    global $runs;
    global $labels;
    
    foreach( $tests as $index => $t )
        $out .= "&t[$index]=$t";

    foreach( $runs as $index => $r )
        $out .= "&r[$index]=$r";

    foreach( $labels as $index => $label )
    {
        $l = urlencode($label);
        $out .= "&l[$index]=$l";
    }
    
    return $out;
}
?>
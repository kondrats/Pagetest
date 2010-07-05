<div class="header">
    <?php
        // see if we have a location that the test was run from (for a custom logo and ad)
        $testLoc;
        if( isset($test) && isset($test['test']) && isset($test['test']['loc']) )
            $testLoc = $test['test']['loc'];
        elseif(isset($_COOKIE["cfg"]))
            $testLoc = $_COOKIE["cfg"];
        /*
        if( strlen($testLoc) && is_dir("./custom/$testLoc") )
        {
            echo "<a href=\"/\"><img class= \"logo\" src=\"/images/logo.png\"></a>";

            if( is_file("./custom/$testLoc/headerAd.inc") )
                include("./custom/$testLoc/headerAd.inc");
            elseif( is_file('./ads/header.inc') )
                include('./ads/header.inc');
        }
        else
        {
            echo "<a href=\"/\"><img class= \"logo\" src=\"/images/logo.png\"></a>";
            if( is_file('./ads/header.inc') )
                include('./ads/header.inc');
        }
*/
    ?>

    <div id="htabs">
        <ul>
        <?php
            if( $id )
                $resultUrl = "/result/$id/";
                
            $tabs = array( 'Home' => '/');
            $tabs['New Test'] = '/test';
            $tabs['New Comparison'] = '/video/';
                            
            if( $headerType == 'video' )
            {
                if( !$filmstrip )
                    $filmstrip = urldecode($_COOKIE['fs']);
                
                if( $filmstrip )
                    $tabs['Visual Comparison'] = '/video/compare.php?tests=' . urlencode($filmstrip);

                if( $videoId )
                    $tabs['Video'] = '/video/view.php?id=' . urlencode($videoId);
            }
            else
            {
                
                $tabs['Test Result'] = $resultUrl;
                $tabs['Test History'] = '/testlog/1';
            }

            if( is_dir('forums') && !$settings['disableMybb'] )
                $tabs['Forums'] = '/forums/';
                            
            if( $settings['contact'] )
                $tabs['Contact'] = '/contact';
            
            foreach( $tabs as $tabName => $tabUrl )
            {
                // make sure we have a test result to navigate to
                if( strlen($tabUrl) )
                {
                    // highlight the current tab
                    $current = '';
                    if( !strcasecmp($tabName, $tab) )
                        $current = ' id="current"';
                    echo "<li$current><a href=\"$tabUrl\"><span>$tabName</span></a></li>";
                }
            }
        ?>
        </ul>
    </div>
    <div class="header" style="float:none; clear:both; height:0;"></div>
    <?php
    // put up the sub-menu if we are looking at test results
    if( !strcasecmp('Test Result', $tab) )
    {
        echo '<div id="navcontainer">';
        echo '<ul id="navlist">';
        
        if( !$run )
        {
            if( isset($fv) && isset($fv['avgRun']) )
                $run = $fv['avgRun'];
            else
                $run = 1;
        }
        
        $cTxt = '';
        if( $cached )
            $cTxt = 'cached/';

        $tabs = array( 'Summary' => "/result/$id/",
                    'Detailed Results' => "/result/$id/$run/details/$cTxt",
                    'Performance Review' => "/result/$id/$run/performance_optimization/$cTxt",
                    'Optimization Report' => "/result/$id/$run/optimization_report/$cTxt",
                    'Content Breakdown' => "/result/$id/$run/breakdown/$cTxt",
                    'Domain Breakdown' => "/result/$id/$run/domains/$cTxt",
                    'Screen Shot' => "/result/$id/$run/screen_shot/$cTxt"
                    );

        foreach( $tabs as $tabName => $tabUrl )
        {
            // make sure we have a test result to navigate to
            if( strlen($tabUrl) )
            {
                // highlight the current tab
                $current = '';
                if( !strcasecmp($tabName, $subtab) )
                    $current = ' id="active"';
                echo "<li$current><a href=\"$tabUrl\">$tabName</a></li>";
            }
        }

        echo '</ul>';
        echo '</div>';
    }
    ?>
</div>
<?php
    if( is_file('./settings/alert.txt') )
    {
        $alert = file_get_contents('./settings/alert.txt');
        echo '<div class="alert">';
        echo $alert;
        echo '</div>';
    }

    if( is_file('./settings/info.txt') )
    {
        $info = file_get_contents('./settings/info.txt');
        echo '<div class="info">';
        echo $info;
        echo '</div>';
    }
?>

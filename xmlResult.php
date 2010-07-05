<?php
include 'common.inc';
include 'page_data.inc';
$pageData = loadAllPageData($testPath);

// if we don't have an url, try to get it from the page results
if( !strlen($url) )
    $url = $pageData[1][0]['URL'];

// see if it is one of the ancient tests before we moved to php
if( is_file("$testPath/testinfo.js") )
    $test['test']['completeTime'] = date("F d Y H:i:s.", filemtime("$testPath/testinfo.js"));

// see if the test is actually finished
if( isset($test['test']['completeTime']) )
{
    header ('Content-type: text/xml');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<response>\n";
    echo "<statusCode>200</statusCode>\n";
    echo "<statusText>Ok</statusText>\n";
    if( strlen($_REQUEST['r']) )
        echo "<requestId>{$_REQUEST['r']}</requestId>\n";
    echo "<data>\n";
    
    // spit out the calculated averages
    $fv = null;
    $rv = null;
    $pageStats = calculatePageStats($pageData, $fv, $rv);
    
    $runs = count($pageData);
    echo "<runs>$runs</runs>\n";
    echo "<average>\n";
    echo "<firstView>\n";
    foreach( $fv as $key => $val )
        echo "<$key>" . number_format($val,0, '.', '') . "</$key>\n";
    echo "</firstView>\n";
    if( isset($rv) )
    {
        echo "<repeatView>\n";
        foreach( $rv as $key => $val )
            echo "<$key>" . number_format($val,0, '.', '') . "</$key>\n";
        echo "</repeatView>\n";
    }
    echo "</average>\n";

    // spit out the raw data for each run
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $path = substr($testPath, 1);

    for( $i = 1; $i <= $runs; $i++ )
    {
        echo "<run>\n";
        echo "<id>$i</id>\n";

        // first view
        if( isset( $pageData[$i] ) )
        {
            if( isset( $pageData[$i][0] ) )
            {
                echo "<firstView>\n";
                echo "<results>\n";
                foreach( $pageData[$i][0] as $key => $val )
                    echo "<$key>$val</$key>\n";
                echo "</results>\n";

                // links to the relevant pages
                echo "<pages>\n";
                echo "<details>http://$host$uri/result/$id/$i/details/</details>\n";
                echo "<checklist>http://$host$uri/result/$id/$i/performance_optimization/</checklist>\n";
                echo "<report>http://$host$uri/result/$id/$i/optimization_report/</report>\n";
                echo "<breakdown>http://$host$uri/result/$id/$i/breakdown/</breakdown>\n";
                echo "<domains>http://$host$uri/result/$id/$i/domains/</domains>\n";
                echo "<screenShot>http://$host$uri/result/$id/$i/screen_shot/</screenShot>\n";
                echo "</pages>\n";
                
                // urls for the relevant images
                echo "<thumbnails>\n";
                echo "<waterfall>http://$host$uri/result/$id/{$i}_waterfall_thumb.png</waterfall>\n";
                echo "<checklist>http://$host$uri/result/$id/{$i}_optimization_thumb.png</checklist>\n";
                echo "<screenShot>http://$host$uri/result/$id/{$i}_screen_thumb.jpg</screenShot>\n";
                echo "</thumbnails>\n";

                echo "<images>\n";
                echo "<waterfall>http://$host$uri$path/{$i}_waterfall.png</waterfall>\n";
                echo "<checklist>http://$host$uri$path/{$i}_optimization.png</checklist>\n";
                echo "<screenShot>http://$host$uri$path/{$i}_screen.jpg</screenShot>\n";
                echo "</images>\n";

                // raw results
                echo "<rawData>\n";
                echo "<headers>http://$host$uri$path/{$i}_report.txt</headers>\n";
                echo "<pageData>http://$host$uri$path/{$i}_IEWPG.txt</pageData>\n";
                echo "<requestsData>http://$host$uri$path/{$i}_IEWTR.txt</requestsData>\n";
                if( is_file("$testPath/{$i}_progress.csv") )
                    echo "<utilization>http://$host$uri$path/{$i}_progress.csv</utilization>\n";
                echo "</rawData>\n";
                
                // video frames
                $frames = loadVideo("$testPath/video_{$i}");
                if( $frames && count($frames) )
                {
                    echo "<videoFrames>\n";
                    foreach( $frames as $time => $frameFile )
                    {
                        echo "<frame>\n";
                        echo "<time>" . number_format((double)$time / 10.0, 1) . "</time>\n";
                        echo "<image>http://$host$uri$path/video_{$i}/$frameFile</image>\n";
                        echo "</frame>\n";
                    }
                    echo "</videoFrames>\n";
                }
                
                echo "</firstView>\n";
            }

            // repeat view
            if( isset( $pageData[$i][1] ) )
            {
                echo "<repeatView>\n";
                echo "<results>\n";
                foreach( $pageData[$i][1] as $key => $val )
                    echo "<$key>$val</$key>\n";
                echo "</results>\n";

                // links to the relevant pages
                echo "<pages>\n";
                echo "<details>http://$host$uri/result/$id/$i/details/cached/</details>\n";
                echo "<checklist>http://$host$uri/result/$id/$i/performance_optimization/cached/</checklist>\n";
                echo "<report>http://$host$uri/result/$id/$i/optimization_report/cached/</report>\n";
                echo "<breakdown>http://$host$uri/result/$id/$i/breakdown/</breakdown>\n";
                echo "<domains>http://$host$uri/result/$id/$i/domains/</domains>\n";
                echo "<screenShot>http://$host$uri/result/$id/$i/screen_shot/cached/</screenShot>\n";
                echo "</pages>\n";
                
                // urls for the relevant images
                echo "<thumbnails>\n";
                echo "<waterfall>http://$host$uri/result/$id/{$i}_Cached_waterfall_thumb.png</waterfall>\n";
                echo "<checklist>http://$host$uri/result/$id/{$i}_Cached_optimization_thumb.png</checklist>\n";
                echo "<screenShot>http://$host$uri/result/$id/{$i}_Cached_screen_thumb.jpg</screenShot>\n";
                echo "</thumbnails>\n";

                echo "<images>\n";
                echo "<waterfall>http://$host$uri$path/{$i}_Cached_waterfall.png</waterfall>\n";
                echo "<checklist>http://$host$uri$path/{$i}_Cached_optimization.png</checklist>\n";
                echo "<screenShot>http://$host$uri$path/{$i}_Cached_screen.jpg</screenShot>\n";
                echo "</images>\n";

                // raw results
                echo "<rawData>\n";
                echo "<headers>http://$host$uri$path/{$i}_Cached_report.txt</headers>\n";
                echo "<pageData>http://$host$uri$path/{$i}_Cached_IEWPG.txt</pageData>\n";
                echo "<requestsData>http://$host$uri$path/{$i}_Cached_IEWTR.txt</requestsData>\n";
                if( is_file("$testPath/{$i}_Cached_progress.csv") )
                    echo "<utilization>http://$host$uri$path/{$i}_Cached_progress.csv</utilization>\n";
                echo "</rawData>\n";
                
                // video frames
                $frames = loadVideo("$testPath/video_{$i}_cached");
                if( $frames && count($frames) )
                {
                    echo "<videoFrames>\n";
                    foreach( $frames as $time => $frameFile )
                    {
                        echo "<frame>\n";
                        echo "<time>" . number_format((double)$time / 10.0, 1) . "</time>\n";
                        echo "<image>http://$host$uri$path/video_{$i}_cached/$frameFile</image>\n";
                        echo "</frame>\n";
                    }
                    echo "</videoFrames>\n";
                }
                
                echo "</repeatView>\n";
            }
        }

        echo "</run>\n";
    }
    
    echo "</data>\n";
    echo "</response>\n";
}
else
{
    header ('Content-type: text/xml');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<response>\n";

    // see if it was a valid test
    if( $test['test']['runs'] )
    {
        echo "<statusCode>100</statusCode>\n";
        echo "<statusText>Test Pending</statusText>\n";
    }
    else
    {
        echo "<statusCode>404</statusCode>\n";
        echo "<statusText>Invalid Test ID: $id</statusText>\n";
    }

    if( strlen($_REQUEST['r']) )
        echo "<requestId>{$_REQUEST['r']}</requestId>\n";
    echo "<data>\n";
    echo "</data>\n";
    echo "</response>\n";
}

/**
* Load and sort the video frame files into an arrray
* 
* @param mixed $path
*/
function loadVideo($path)
{
    $frames = null;
    
    if( is_dir($path) )
    {
        $files = glob( $path . '/frame_*.jpg', GLOB_NOSORT );
        if( $files && count($files) )
        {
            $frames = array();
            foreach( $files as $file )
            {
                $file = basename($file);
                $parts = explode('_', $file);
                if( count($parts) >= 2 )
                {
                    $index = (int)$parts[1];
                    $frames[$index] = $file;
                }
            }
            
            
            // sort the frames in order
            ksort($frames, SORT_NUMERIC);
        }
    }
    
    return $frames;
}
?>

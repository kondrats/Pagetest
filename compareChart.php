<?php
include 'common.inc';
require_once('page_data.inc');

include ("graph/jpgraph.php");
include ("graph/jpgraph_bar.php"); 

$tests = $_REQUEST['t'];
$runs = $_REQUEST['r'];
$labels = $_REQUEST['l'];

$width = 800;
$height = 600;

$graph = new Graph($width,$height,"auto");    
$graph->SetScale("textlin");
$graph->SetFrame(false);

$fvData;
$rvData;
BuildDataSet($fvData, $rvData);

// create the graphs
$bars = array();
foreach($fvData as &$data )
{
    $plot = new BarPlot($data['values']);
    $plot->SetFillColor($data['color']);
    $plot->SetLegend($data['label']);
    $bars[] = $plot;
}

$stacked = new AccBarPlot($bars);

// set other options
$graph->title->SetFont( FF_ARIAL, FS_NORMAL, $fontSize);
$graph->title->Set( $chartType );
$graph->xaxis->SetTickLabels($labels);
$graph->yaxis->SetFont(FF_FONT1,FS_NORMAL,9);
$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL,9);
//$graph->img->SetMargin(20,20,50,200);

// ...and add it to the graPH
$graph->Add($stacked);
$graph->img->SetExpired(false);
$graph->Stroke(); 

/**
* Build the data set for the given graph
* 
*/
function BuildDataSet(&$fvData, &$rvData)
{
    global $tests;
    global $runs;
    global $labels;

    $type = $_REQUEST['type'];
    
    // load the page data for the tests of interest
    $fv = array();
    $rv = array();
    foreach( $tests as $index => $test )
    {
        $testPath = GetTestPath($test);
        $run = 1;
        if( $runs[$index] )
            $run = $runs[$index];
            
        $fv[$index] = loadPageRunData($testPath, $run, 0);
        $rv[$index] = loadPageRunData($testPath, $run, 1);
    }
    
    // generate the data set for the graph
    $fvData = array();
    $rvData = array();
    if( $type == 'times' )
    {
        // TTFB
        $fdata = array( 'label' => 'First Byte', 'color' => 'red' );
        $rdata = array( 'label' => 'First Byte', 'color' => 'red' );

        $fdata['values'] = array();
        $rdata['values'] = array();
        foreach( $tests as $index => $test)
        {
            $value = 0;
            if( $fv[$index] )
                $value = $fv[$index]['TTFB'] / 1000.0;
            $fdata['values'][] = $value;

            $value = 0;
            if( $rv[$index] )
                $value = $rv[$index]['TTFB'] / 1000.0;
            $rdata['values'][] = $value;
        }
        $fvData[] = $fdata;
        $rvData[] = $rdata;

        // Render Start
        $fdata = array( 'label' => 'Render Start', 'color' => 'green' );
        $rdata = array( 'label' => 'Render Start', 'color' => 'green' );

        $fdata['values'] = array();
        $rdata['values'] = array();
        foreach( $tests as $index => $test)
        {
            $value = 0;
            if( $fv[$index] )
            {
                $ttfb = $fv[$index]['TTFB'];
                $render = $fv[$index]['render'];
                if( $render > $ttfb )
                    $value = ($render - $ttfb) / 1000.0;
            }
            $fdata['values'][] = $value;

            $value = 0;
            if( $rv[$index] )
            {
                $ttfb = $rv[$index]['TTFB'];
                $render = $rv[$index]['render'];
                if( $render > $ttfb )
                    $value = ($render - $ttfb) / 1000.0;
            }
            $rdata['values'][] = $value;
        }
        $fvData[] = $fdata;
        $rvData[] = $rdata;
        
        // Document Complete
        $fdata = array( 'label' => 'Document Complete', 'color' => 'blue' );
        $rdata = array( 'label' => 'Document Complete', 'color' => 'blue' );

        $fdata['values'] = array();
        $rdata['values'] = array();
        foreach( $tests as $index => $test)
        {
            $value = 0;
            if( $fv[$index] )
            {
                $ttfb = $fv[$index]['TTFB'];
                $render = $fv[$index]['render'];
                $doc = $fv[$index]['docTime'];
                if( $doc > max($render, $ttfb) )
                    $value = ($doc - max($render, $ttfb)) / 1000.0;
            }
            $fdata['values'][] = $value;

            $value = 0;
            if( $rv[$index] )
            {
                $ttfb = $rv[$index]['TTFB'];
                $render = $rv[$index]['render'];
                $doc = $rv[$index]['docTime'];
                if( $doc > max($render, $ttfb) )
                    $value = ($doc - max($render, $ttfb)) / 1000.0;
            }
            $rdata['values'][] = $value;
        }
        $fvData[] = $fdata;
        $rvData[] = $rdata;

        // Fully Loaded
        $fdata = array( 'label' => 'Fully Loaded', 'color' => 'orange' );
        $rdata = array( 'label' => 'Fully Loaded', 'color' => 'orange' );

        $fdata['values'] = array();
        $rdata['values'] = array();
        foreach( $tests as $index => $test)
        {
            $value = 0;
            if( $fv[$index] )
            {
                $ttfb = $fv[$index]['TTFB'];
                $render = $fv[$index]['render'];
                $doc = $fv[$index]['docTime'];
                $full = $fv[$index]['fullyLoaded'];
                if( $full > max($render, $ttfb, $doc) )
                    $value = ($full - max($render, $ttfb, $doc)) / 1000.0;
            }
            $fdata['values'][] = $value;

            $value = 0;
            if( $rv[$index] )
            {
                $ttfb = $rv[$index]['TTFB'];
                $render = $rv[$index]['render'];
                $doc = $rv[$index]['docTime'];
                $full = $rv[$index]['fullyLoaded'];
                if( $full > max($render, $ttfb, $doc) )
                    $value = ($full - max($render, $ttfb, $doc)) / 1000.0;
            }
            $rdata['values'][] = $value;
        }
        $fvData[] = $fdata;
        $rvData[] = $rdata;
    }
}
?>

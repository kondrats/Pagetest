<?php
require_once('page_data.inc');
require_once('object_detail.inc');

/**
* Parse the page data and load the optimization-specific details
* 
* @param mixed $testPath
* @param mixed $run
* @param mixed $cached
* @param mixed $includeObject
*/
function getOptimizationDetails($testPath, $run, $cached, $includeObject)
{
    $opt = null;
    
    $pageData = loadPageRunData($testPath, $run, $cached);
    if( $pageData )
    {
        $opt = array();
        
        // put them in rank-order
        $opt['keep-alive'] = array();
        $opt['gzip'] = array();
        $opt['image_compression'] = array();
        $opt['caching'] = array();
        $opt['combine'] = array();
        $opt['cdn'] = array();
        $opt['cookies'] = array();
        $opt['minify'] = array();
        $opt['e-tags'] = array();

        // get the scores
        $opt['keep-alive']['score'] = $pageData['score_keep-alive'];
        $opt['gzip']['score'] = $pageData['score_gzip'];
        $opt['image_compression']['score'] = $pageData['score_compress'];
        $opt['caching']['score'] = $pageData['score_cache'];
        $opt['combine']['score'] = $pageData['score_combine'];
        $opt['cdn']['score'] = $pageData['score_cdn'];
        $opt['cookies']['score'] = $pageData['score_cookies'];
        $opt['minify']['score'] = $pageData['score_minify'];
        $opt['e-tags']['score'] = $pageData['score_etags'];
        
        // define the labels for all  of them
        $opt['keep-alive']['label'] = 'Enable keep-alive';
        $opt['gzip']['label'] = 'Compress Text';
        $opt['image_compression']['label'] = 'Compress Images';
        $opt['caching']['label'] = 'Cache static content';
        $opt['combine']['label'] = 'Combine js and css files';
        $opt['cdn']['label'] = 'Use a CDN';
        $opt['cookies']['label'] = 'No cookies on static content';
        $opt['minify']['label'] = 'Minify javascript';
        $opt['e-tags']['label'] = 'Disable E-Tags';
        
        // flag the important ones
        $opt['keep-alive']['important'] = true;
        $opt['gzip']['important'] = true;
        $opt['image_compression']['important'] = true;
        $opt['caching']['important'] = true;
        $opt['combine']['important'] = true;
        $opt['cdn']['important'] = true;
        
        // apply grades
        foreach( $opt as &$item )
        {
            $grade = 'N/A';
            $weight = 0;
            if( isset($item['score']) )
            {
                $weight = 100;
                if( $item['score'] >= 90 )
                    $grade = 'A';
                elseif( $item['score'] >= 80 )
                    $grade = 'B';
                elseif( $item['score'] >= 70 )
                    $grade = 'C';
                elseif( $item['score'] >= 60 )
                    $grade = 'D';
                elseif( $item['score'] >= 0 )
                    $grade = 'F';
                else
                    $weight = 0;
            }
            $item['grade'] = $grade;
            $item['weight'] = $weight;
        }
    }
    
    return $opt;
}  

/**
* Build a table for the key optimizations
* 
* @param mixed $testPath
* @param mixed $run
* @param mixed $cached
*/
function keyOptimizationsTable( $testPath, $run, $cached )
{
    $html = '';

    $opt = getOptimizationDetails( $testPath, $run, $cached, false );
    if( $opt && count($opt) )
    {
        $html .= '<thead><tr>';
        foreach( $opt as &$item )
            if( $item['important'] === true )
                $html .= "<th class=\"opt_label\">{$item['label']}</th>";
        $html .= "</tr></thead>\n";
        $html .= '<tbody><tr>';
        foreach( $opt as &$item )
            if( $item['important'] === true )
            {
                $grade = $item['grade'];
                if( $grade == 'N/A' )
                    $grade = 'NA';
                $html .= "<td class=\"opt_grade_$grade\">{$item['grade']}</td>";
            }
        $html .= "</tr></tbody>\n";
    }

    return $html;
}
?>

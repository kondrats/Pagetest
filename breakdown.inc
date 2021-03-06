<?php
include 'object_detail.inc'; 

/**
* Aggregate the individual requests by mime type
* 
* @param mixed $requests
*/
function getBreakdown($id, $testPath, $run, $cached, &$requests)
{
    // get all of the requests
    $secure = false;
    $haveLocations = false;
    $requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false);
    
    $breakdown = array();
    
    foreach($requests as $request)
    {
        $contentType = $request['contentType'];
        
        // collapse the various javascript content types together
        if( !strcasecmp($contentType, "application/x-javascript") || 
            !strcasecmp($contentType, "text/javascript") || 
            !strcasecmp($contentType, "text/ecmascript") || 
            !strcasecmp($contentType, "text/js") || 
            !strcasecmp($contentType, "application/ecmascript") || 
            !strcasecmp($contentType, "application/javascript") )
            $contentType = "javascript";
        
        $object = strtolower($request['url']);
        if( strlen($contentType) && (strstr($object, 'favicon.ico') === FALSE) )
        {
            if( $breakdown["$contentType"] === null )
            {
                $current = array();
                $breakdown["$contentType"] = $current;
            }
            
            $breakdown["$contentType"]['bytes'] += $request['bytesIn'];
            $breakdown["$contentType"]['requests']++;
        }
    }
    
    return $breakdown;
}

/**
* Get a combined result that has both first and repeat view data
* 
* @param mixed $id
* @param mixed $run
*/
function getBreakdownCombined($id, $testPath, $run)
{
    $combined = getBreakdown($id, $testPath, $run, 0);
    $rv = getBreakdown($id, $testPath, $run, 1);

    // combine the two data sets
    foreach( $combined as $type => $data )
    {
        unset($rvData);
        $rvData = $rv[$type];
        if( !count($rvData) )
        {
            $rvData['bytes'] = 0;
            $rvData['requests'] = 0;
        }
        $combined[$type]['rvBytes'] = $rvData['bytes'];
        $combined[$type]['rvRequests'] = $rvData['requests'];
    }

    // grab any that had data in the repeat view but not first view
    foreach( $rv as $type => $data )
    {
        unset($fv);
        $fv = $combined[$type];
        if( !count($fv) )
        {
            $fv = array();
            $fv['bytes'] = 0;
            $fv['requests'] = 0;
            $fv['rvBytes'] = $data['bytes'];
            $fv['rvRequests'] = $data['requests'];
            $combined[$type] = $fv;
        }
    }
    
    return $combined;
}
?>

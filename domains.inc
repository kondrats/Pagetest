<?php
require 'object_detail.inc'; 

/**
* Aggregate the individual requests by domain
* 
* @param mixed $requests
*/
function getDomainBreakdown($id, $testPath, $run, $cached, &$requests)
{
    // get all of the requests
    $secure = false;
    $haveLocations = false;
    $requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false);
    
    $breakdown = array();
    
    foreach($requests as $request)
    {
        $domain = strrev(strtok($request['host'],':'));
        $object = strtolower($request['url']);
        if( strlen($domain) && (strstr($object, 'favicon.ico') === FALSE) )
        {
            if( $breakdown["$domain"] === null )
            {
                $current = array();
                $breakdown["$domain"] = $current;
            }
            
            $breakdown["$domain"]['bytes'] += $request['bytesIn'];
            $breakdown["$domain"]['requests']++;
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
function getDomainBreakdownCombined($id, $testPath, $run)
{
    $combined = getBreakdown($id, $testPath, $run, 0);
    $rv = getBreakdown($id, $testPath, $run, 1);

    // combine the two data sets
    foreach( $combined as $domain => $data )
    {
        unset($rvData);
        $rvData = $rv[$domain];
        if( !count($rvData) )
        {
            $rvData['bytes'] = 0;
            $rvData['requests'] = 0;
        }
        $combined[$domain]['rvBytes'] = $rvData['bytes'];
        $combined[$domain]['rvRequests'] = $rvData['requests'];
    }

    // grab any that had data in the repeat view but not first view
    foreach( $rv as $domain => $data )
    {
        unset($fv);
        $fv = $combined[$domain];
        if( !count($fv) )
        {
            $fv = array();
            $fv['bytes'] = 0;
            $fv['requests'] = 0;
            $fv['rvBytes'] = $data['bytes'];
            $fv['rvRequests'] = $data['requests'];
            $combined[$domain] = $fv;
        }
    }
    
    return $combined;
}
?>
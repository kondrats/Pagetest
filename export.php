<?php

/******************************************************************************
* 
*   Export a result data set  in HTTP archive format:
*   http://groups.google.com/group/firebug-working-group/web/http-tracing---export-format
* 
******************************************************************************/

include 'common.inc';
include 'page_data.inc';
include 'object_detail.inc';
require_once('lib/json.php');

// see if we are loading a single run or all of them
if( isset($testPath) )
{
    $pageData;
    if( isset($_REQUEST["run"]) && $_REQUEST["run"] )
    {
        $pageData[0] = array();
        if( isset($cached) )
            $pageData[$run][$cached] = loadPageRunData($testPath, $run, $cached);
        else
        {
            $pageData[$run][0] = loadPageRunData($testPath, $run, 0);
            $pageData[$run][1] = loadPageRunData($testPath, $run, 1);
        }
    }
    else
        $pageData = loadAllPageData($testPath);

    // build up the array
    $result = BuildResult($pageData);

    // spit it out as json
    header('Content-disposition: attachment; filename=pagetest.har');
    header('Content-type: application/json');
    
    $json = new Services_JSON();
    $out = $json->encode($result);
    
    echo $out;
}

function msdate($mstimestamp)
{
    $timestamp = floor($mstimestamp);
    $milliseconds = round(($mstimestamp - $timestamp) * 1000);
    
    $date = date('c', $timestamp);
    $msDate = substr($date, 0, 19) . '.' . sprintf('%03d', $milliseconds) . substr($date, 19);

    return $msDate;
}

/**
* Build the data set
* 
* @param mixed $pageData
*/
function BuildResult(&$pageData)
{
    global $id;
    global $testPath;
    $result = array();
    $entries = array();
    
    $result['log'] = array();
    $result['log']['version'] = '1.1';
    $result['log']['creator'] = array( 'name' => 'WebPagetest', 'version' => '1.8' );
    $result['log']['browser'] = array( 'name' => 'Internet Explorer', 'version' => '' );
    $result['log']['pages'] = array();
    foreach( $pageData as $run => &$pageRun )
    {
        foreach( $pageRun as $cached => &$data )
        {
            $pd = array();
            $pd['startedDateTime'] = msdate($data['date']);
            $pd['title'] = "Run $run, ";
            if( $cached )
                $pd['title'] .= "Repeat View";
            else
                $pd['title'] .= "First View";
            $pd['title'] .= " for " . $data['URL'];
            $pd['id'] = "page_{$run}_{$cached}";
            $pd['pageTimings'] = array( 'onLoad' => $data['docTime'], 'onContentLoad' => -1 );
            
            // add the page-level ldata to the result
            $result['log']['pages'][] = $pd;
            
            // now add the object-level data to the result
            $secure = false;
            $haveLocations = false;
            $requests = getRequests($id, $testPath, $run, $cached, $secure, $haveLocations, false, true);
            foreach( $requests as &$r )
            {
                $entry = array();
                $entry['pageref'] = $pd['id'];
                $entry['startedDateTime'] = msdate((double)$data['date'] + ($r['offset'] / 1000.0));
                $entry['time'] = $r['totalTime'];
                
                $request = array();
                $request['method'] = $r['method'];
                $protocol = 'http://';
                if( $r['secure'] )
                    $protocol = 'https://';
                $request['url'] = $protocol . $r['host'] . $r['url'];
                $request['headersSize'] = -1;
                $request['bodySize'] = -1;
                $request['cookies'] = array();
                $request['headers'] = array();
                $ver = '';
                if( isset($r['headers']) && isset($r['headers']['request']) )
                {
                    foreach($r['headers']['request'] as &$header)
                    {
                        $pos = strpos($header, ':');
                        if( $pos > 0 )
                        {
                            $name = trim(substr($header, 0, $pos));
                            $val = trim(substr($header, $pos + 1));
                            if( strlen($name) )
                                $request['headers'][] = array('name' => $name, 'value' => $val);

                            // parse out any cookies
                            if( !strcasecmp($name, 'cookie') )
                            {
                                $cookies = explode(';', $val);
                                foreach( $cookies as &$cookie )
                                {
                                    $pos = strpos($cookie, '=');
                                    if( $pos > 0 )
                                    {
                                        $name = (string)trim(substr($cookie, 0, $pos));
                                        $val = (string)trim(substr($cookie, $pos + 1));
                                        if( strlen($name) )
                                            $request['cookies'][] = array('name' => $name, 'value' => $val);
                                    }
                                }
                            }
                        }
                        else
                        {
                            $pos = strpos($header, 'HTTP/');
                            if( $pos >= 0 )
                                $ver = (string)trim(substr($header, $pos + 5, 3));
                        }
                    }
                }
                $request['httpVersion'] = $ver;

                $request['queryString'] = array();
                $parts = parse_url($request['url']);
                if( isset($parts['query']) )
                {
                    $qs = array();
                    parse_str($parts['query'], $qs);
                    foreach($qs as $name => $val)
                        $request['queryString'][] = array('name' => (string)$name, 'value' => (string)$val );
                }
                
                if( !strcasecmp(trim($request['method']), 'post') )
                {
                    $request['postData'] = array();
                    $request['postData']['mimeType'] = '';
                    $request['postData']['text'] = '';
                }
                
                $entry['request'] = $request;

                $response = array();
                $response['status'] = (int)$r['responseCode'];
                $response['statusText'] = '';
                $response['headersSize'] = -1;
                $response['bodySize'] = (int)$r['objectSize'];
                $response['headers'] = array();
                $ver = '';
                $loc = '';
                if( isset($r['headers']) && isset($r['headers']['response']) )
                {
                    foreach($r['headers']['response'] as &$header)
                    {
                        $pos = strpos($header, ':');
                        if( $pos > 0 )
                        {
                            $name = (string)trim(substr($header, 0, $pos));
                            $val = (string)trim(substr($header, $pos + 1));
                            if( strlen($name) )
                                $response['headers'][] = array('name' => $name, 'value' => $val);
                            
                            if( !strcasecmp($name, 'location') )
                                $loc = (string)$val;
                        }
                        else
                        {
                            $pos = strpos($header, 'HTTP/');
                            if( $pos >= 0 )
                                $ver = (string)trim(substr($header, $pos + 5, 3));
                        }
                    }
                }
                $response['httpVersion'] = $ver;
                $response['redirectURL'] = $loc;

                $response['content'] = array();
                $response['content']['size'] = (int)$r['objectSize'];
                if( isset($r['contentType']) && strlen($r['contentType']) )
                    $response['content']['mimeType'] = (string)$r['contentType'];
                else
                    $response['content']['mimeType'] = '';
                
                // unsupported fields that are required
                $response['cookies'] = array();

                $entry['response'] = $response;
                
                $entry['cache'] = (object)array();
                
                $timings = array();
                $timings['blocked'] = -1;
                $timings['dns'] = (int)$r['dnsTime'];
                if( !$timings['dns'] )
                    $timings['dns'] = -1;
                $timings['connect'] = (int)($r['socketTime'] + $r['sslTime']);
                if( !$timings['connect'] )
                    $timings['connect'] = -1;
                $timings['send'] = 0;
                $timings['wait'] = (int)$r['ttfb'];
                if( $r['loadTime'] && $r['ttfb'] )
                    $timings['receive'] = $r['loadTime'] - $r['ttfb'];
                else
                    $timings['receive'] = 0;
                $entry['timings'] = $timings;

                $entry['time'] = (int)($r['dnsTime'] + $r['socketTime'] + $r['sslTime'] +  $r['ttfb'] + $timings['receive']);
                
                // add it to the list of entries
                $entries[] = $entry;
            }
        }
    }
    
    $result['log']['entries'] = $entries;
    
    return $result;
}
?>

<?php
    require_once('common.inc');
    require_once('auth.inc');
    import_request_variables('PG', 'req_');

    $xml = false;
    if( !strcasecmp($req_f, 'xml') )
        $xml = true;
    $json = false;
    if( !strcasecmp($req_f, 'json') )
        $json = true;
    
    // pull in the test parameters
    $test = array();
    $test['url'] = trim($req_url);
    
    $parts = explode('.', trim($req_location));
    $test['location'] = $parts[0];
    $test['connectivity'] = $parts[1];
    
    $test['domElement'] = trim($req_domelement);
    $test['login'] = trim($req_login);
    $test['password'] = trim($req_password);
    $test['runs'] = (int)$req_runs;
    $test['fvonly'] = (int)$req_fvonly;
    $test['connections'] = (int)$req_connections;
    $test['speed'] = (int)$req_speed;
    $test['private'] = $req_private;
    $test['web10'] = $req_web10;
    $test['ignoreSSL'] = $req_ignoreSSL;
    $test['script'] = trim($req_script);
    $test['block'] = $req_block;
    $test['authType'] = trim($req_authType);
    $test['notify'] = trim($req_notify);
    $test['video'] = $req_video;
    $test['label'] = htmlspecialchars(trim($req_label));
    $test['industry'] = trim($req_ig);
    $test['industry_page'] = trim($req_ip);
    $test['median_video'] = (int)$req_mv;
    $test['ip'] = $req_addr;
    $test['uid'] = $req_uid;
    $test['user'] = $req_user;
    $test['priority'] = (int)$req_priority;
    $test['bwIn'] = (int)$req_bwDown;
    $test['bwOut'] = (int)$req_bwUp;
    $test['latency'] = (int)$req_latency;
    $test['plr'] = trim($req_plr);

    // default API requests to a lower priority
    if( !$test['priority'] )
    {
        if( $_SERVER['REQUEST_METHOD'] == 'GET' || $xml || $json )
            $test['priority'] =  5;
    }
    
    // take the ad-blocking request and create a custom block from it
    if( $req_ads == 'blocked' )
        $test['block'] .= ' adsWrapper.js adsWrapperAT.js adsonar.js sponsored_links1.js switcher.dmn.aol.com';

    // see if they selected blaank ads
    if( $req_ads == 'blank' )
    {
        if( strpos($test['url'], '?') === false )
            $test['url'] .= '?atwExc=blank';
        else
            $test['url'] .= '&atwExc=blank';
    }
        
    // some myBB integration to get the requesting user
    if( is_dir('./forums') && isset($_COOKIE['mybbuser']) && !$test['uid'] && !$test['user'] )
    {
        $dir = getcwd();
        try
        {
            define("IN_MYBB",1);
            chdir('forums'); // path to MyBB
            include './global.php';
            
            $test['uid'] = $mybb->user['uid'];
            $test['user'] = $mybb->user['username'];
        }
        catch(Exception $e)
        {
        }
        chdir($dir);
    }
    
    // check to make sure the referrer is the same as the host
    if( CheckReferrer() && CheckIp($test) && CheckUrl($test['url']) )
    {
        // load the location information
        $locations = parse_ini_file('./settings/locations.ini', true);
        $error = NULL;
        
        ValidateParameters($test, $locations, $error);
        if( !$error )
        {
            if( $test['remoteUrl'] )
            {
                // send the test request to the remote system (only allow this for POST requests for now)
                SendRemoteTest($test, $_POST, $error);
            }
            else
            {
                // generate the test ID
                include_once('unique.inc');
                $id = null;
                if( $test['private'] )
                    $id = md5(uniqid(rand(), true));
                else
                    $id = uniqueId();
                $today = new DateTime("now", new DateTimeZone('America/New_York'));
                $test['id'] = $today->format('ymd_') . $id;
                $test['path'] = './' . GetTestPath($test['id']);
                
                // make absolutely CERTAIN that this test ID doesn't already exist
                while( is_dir($test['path']) )
                {
                    // fall back to random ID's
                    $id = md5(uniqid(rand(), true));
                    $test['id'] = $today->format('ymd_') . $id;
                    $test['path'] = './' . GetTestPath($test['id']);
                }

                // create the folder for the test results
                if( !is_dir($test['path']) )
                    mkdir($test['path'], 0777, true);
                
                // write out the url, DOM element and login
                file_put_contents("{$test['path']}/url.txt",  $test['url']);
                if( strlen($test['domElement']) )
                    file_put_contents("{$test['path']}/dom.txt",  $test['domElement']);
                if( strlen($test['login']) )
                    file_put_contents("{$test['path']}/login.txt",  $test['login']);
                if( strlen($test['label']) )
                    file_put_contents("{$test['path']}/label.txt",  $test['label']);
                
                // write out the ini file
                $testInfo = "[test]\r\n";
                $testInfo .= "fvonly={$test['fvonly']}\r\n";
                $testInfo .= "runs={$test['runs']}\r\n";
                $testInfo .= "location={$test['locationText']}\r\n";
                $testInfo .= "loc={$test['location']}\r\n";
                $testInfo .= "id={$test['id']}\r\n";
                if( strlen($test['login']) )
                    $testInfo .= "authenticated=1\r\n";
                $testInfo .= "connections={$test['connections']}\r\n";
                if( strlen($test['script']) )
                    $testInfo .= "script=1\r\n";
                if( strlen($test['notify']) )
                    $testInfo .= "notify={$test['notify']}\r\n";
                if( strlen($test['video']) )
                    $testInfo .= "Capture Video=1\r\n";
                if( strlen($test['industry']) && strlen($test['industry_page']) )
                {
                    $testInfo .= "industry=\"{$test['industry']}\"\r\n";
                    $testInfo .= "industry_page=\"{$test['industry_page']}\"\r\n";
                }
                $testInfo .= "id={$test['id']}\r\n";

                if( isset($test['connectivity']) )
                {
                    $testInfo .= "connectivity={$test['connectivity']}\r\n";
                    $testInfo .= "bwIn={$test['bwIn']}\r\n";
                    $testInfo .= "bwOut={$test['bwOut']}\r\n";
                    $testInfo .= "latency={$test['latency']}\r\n";
                    $testInfo .= "plr={$test['plr']}\r\n";
                }
                
                $testInfo .= "\r\n[runs]\r\n";
                if( $test['median_video'] )
                    $testInfo .= "median_video=1\r\n";

                file_put_contents("{$test['path']}/testinfo.ini",  $testInfo);

                // build up the actual test commands
                $testFile = '';
                if( strlen($test['domElement']) )
                    $testFile .= "\r\nDOMElement={$test['domElement']}";
                if( $test['fvonly'] )
                    $testFile .= "\r\nfvonly=1";
                if( $test['connections'] )
                    $testFile .= "\r\nconnections={$test['connections']}";
                if( $test['speed'] )
                    $testFile .= "\r\nspeed={$test['speed']}";
                if( $test['web10'] )
                    $testFile .= "\r\nweb10=1";
                if( $test['ignoreSSL'] )
                    $testFile .= "\r\nignoreSSL=1";
                if( $test['video'] )
                    $testFile .= "\r\nCapture Video=1";
                if( $test['block'] )
                {
                    $testFile .= "\r\nblock={$test['block']}";
                    file_put_contents("{$test['path']}/block.txt",  $test['block']);
                }
                $testFile .= "\r\nruns={$test['runs']}\r\n";
                
                if( isset($test['connectivity']) )
                {
                    $testFile .= "bwIn={$test['bwIn']}\r\n";
                    $testFile .= "bwOut={$test['bwOut']}\r\n";
                    $testFile .= "latency={$test['latency']}\r\n";
                    $testFile .= "plr={$test['plr']}\r\n";
                }


                // see if we need to generate a SNS authentication script
                if( strlen($test['login']) && strlen($test['password']) )
                {
                    if( $test['authType'] && $settings['authConfigFile'] ){
                        $test['script'] = generateAuthScript($test, $settings);
					} else {
                        $testFile .= "\r\nBasic Auth={$test['login']}:{$test['password']}\r\n";
					}
                }
                    
                if( !SubmitUrl($test['id'], $testFile, $test) )
                    $error = "Error sending url to test machine.  Please try back later.";
            }
        }
            
        // redirect the browser to the test results page
        if( !$error )
        {
            // log the test results
            LogTest($test);

            // only redirect for local tests, otherwise the redirect has already been taken care of
            if( !$test['remoteUrl'] )
            {
                $host  = $_SERVER['HTTP_HOST'];
                $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

                if( $xml )
                {
                    header ('Content-type: text/xml');
                    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    echo "<response>\n";
                    echo "<statusCode>200</statusCode>\n";
                    echo "<statusText>Ok</statusText>\n";
                    if( strlen($req_r) )
                        echo "<requestId>{$req_r}</requestId>\n";
                    echo "<data>\n";
                    echo "<testId>{$test['id']}</testId>\n";
                    echo "<xmlUrl>http://$host$uri/xmlResult/{$test['id']}/</xmlUrl>\n";
                    echo "<userUrl>http://$host$uri/result/{$test['id']}/</userUrl>\n";
                    echo "</data>\n";
                    echo "</response>\n";
                    
                }
                elseif( $json )
                {
                    $ret = array();
                    $ret['statusCode'] = 200;
                    $ret['statusText'] = 'Ok';
                    if( strlen($req_r) )
                        $ret['requestId'] = $req_r;
                    $ret['data'] = array();
                    $ret['data']['testId'] = $test['id'];
                    $ret['data']['jsonUrl'] = "http://$host$uri/jsonResult/{$test['id']}/";
                    $ret['data']['userUrl'] = "http://$host$uri/result/{$test['id']}/";
                    header ("Content-type: application/json");
                    echo '(' . json_encode($ret) . ')';
                }
                else
                {
                    header("Location: http://$host$uri/result/{$test['id']}/");    
                }
            }
        }
        else
        {
            if( $xml )
            {
                header ('Content-type: text/xml');
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                echo "<response>\n";
                echo "<statusCode>400</statusCode>\n";
                echo "<statusText>" . $error . "</statusText>\n";
                if( strlen($req_r) )
                    echo "<requestId>" . $req_r . "</requestId>\n";
                echo "</response>\n";
            }
            elseif( $json )
            {
                $ret = array();
                $ret['statusCode'] = 400;
                $ret['statusText'] = $error;
                if( strlen($req_r) )
                    $ret['requestId'] = $req_r;
                header ("Content-type: application/json");
                echo '(' . json_encode($ret) . ')';
            }
            else
            {
                ?>
                <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html>
                    <head>
                        <title>Test error</title>
                        <style type="text/css">
                        <?php 
                            include 'pagestyle.css'; 
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
                                echo "<p>$error</p>\n";
                                ?>
                            </div>
                        </div>
                    </body>
                </html>
                <?php
            }
        }
    }
    else
        include 'blocked.php';
    
/**
* Validate the test options and set intelligent defaults
*     
* @param mixed $test
* @param mixed $locations
*/
function ValidateParameters(&$test, $locations, &$error)
{
    if( strlen($test['url']) )
    {
        $settings = parse_ini_file('./settings/settings.ini');
        $maxruns = (int)$settings['maxruns'];
        if( !$maxruns )
            $maxruns = 10;
        
        // make sure the url starts with http://
        if( strncasecmp($test['url'], 'http:', 5) && strncasecmp($test['url'], 'https:', 6) )
            $test['url'] = 'http://' . $test['url'];
            
        ValidateURL($test, $error, $settings);
        if( !$error )
        {
            // make sure the test runs are between 1 and 200
            if( $test['runs'] > $maxruns )
                $test['runs'] = $maxruns;
            elseif( $test['runs'] < 1 )
                $test['runs'] = 1;
                
            // if fvonly is set, make sure it is to an explicit value of 1
            if( $test['fvonly'] > 0 )
                $test['fvonly'] = 1;

            // make sure private is explicitly 1 or 0
            if( $test['private'] )
                $test['private'] = 1;
            else
                $test['private'] = 0;
                
            // make sure web10 is explicitly 1 or 0
            if( $test['web10'] )
                $test['web10'] = 1;
            else
                $test['web10'] = 0;

            // make sure ignoreSSL is explicitly 1 or 0
            if( $test['ignoreSSL'] )
                $test['ignoreSSL'] = 1;
            else
                $test['ignoreSSL'] = 0;
                
            // make sure the number of connections is in a sensible range
            if( $test['connections'] > 20 )
                $test['connections'] = 20;
            elseif( $test['connections'] < 0 )
                $test['connections'] = 0;
            
            // use the default location if one wasn't specified
            if( !strlen($test['location']) )
            {
                $def = $locations['locations']['default'];
                if( !$def )
                    $def = $locations['locations']['1'];
                $loc = $locations[$def]['default'];
                if( !$loc )
                    $loc = $locations[$def]['1'];
                $test['location'] = $loc;
            }
            
            // see if we need to pick the default connectivity
            if( (!isset($locations[$test['location']]['connectivity']) || !strlen($locations[$test['location']]['connectivity'])) && !isset($test['connectivity']) )
                $test['connectivity'] = 'DSL';
                
            // filter out a SPAM bot that is hitting us
            //  for scripted tests, the block command will be in the script
            // if( strlen($test['script']) && strlen($test['block']) )
            //    $error = 'Your test request was flagged by our system as potentially spam-related.  Please contact us if you think this was an error.';
            
            // figure out what the location working directory and friendly name are
            $test['locationText'] = $locations[$test['location']]['label'];
            $test['workdir'] = $locations[$test['location']]['localDir'];
            $test['remoteUrl']  = $locations[$test['location']]['remoteUrl'];
            $test['remoteLocation'] = $locations[$test['location']]['remoteLocation'];
            if( !strlen($test['workdir']) && !strlen($test['remoteUrl']) )
                $error = "Invalid Location, please try submitting your test request again.";
                
            if( isset($test['connectivity']) )
            {
                $test['locationText'] .= " - <b>{$test['connectivity']}</b>";
                $connectivity = parse_ini_file('./settings/connectivity.ini', true);
                if( isset($connectivity[$test['connectivity']]) )
                {
                    $test['bwIn'] = (int)$connectivity[$test['connectivity']]['bwIn'] / 1000;
                    $test['bwOut'] = (int)$connectivity[$test['connectivity']]['bwOut'] / 1000;
                    $test['latency'] = (int)$connectivity[$test['connectivity']]['latency'];
                    $test['plr'] = $connectivity[$test['connectivity']]['plr'];
                }
            }
            
            // adjust the latency for any last-mile latency at the location
            if( isset($test['latency']) && $locations[$test['location']]['latency'] )
                $test['latency'] = max(0, $test['latency'] - $locations[$test['location']]['latency'] );
                
            // if the speed wasn't specified and there is one for the location, pass it on
            if( !$test['speed'] && $locations[$test['location']]['speed'] )
                $test['speed'] = $locations[$test['location']]['speed'];
                
            if( strlen($test['script']) )
                ValidateScript($test, $error);
        }
    }
    else
        $error = "Invalid URL, please try submitting your test request again.";
    
    return $ret;
}

/**
* Validate the uploaded script to make sure it should be run
* 
* @param mixed $test
* @param mixed $error
*/
function ValidateScript(&$test, &$error)
{
    $ok = false;
    $lines = explode("\n", $test['script']);
    foreach( $lines as $line )
    {
        $tokens = explode("\t", $line);
        $command = trim($tokens[0]);
        if( !strcasecmp($command, 'navigate') )
            $ok = true;
        elseif( !strcasecmp($command, 'loadVariables') )
            $error = "loadVariables is not a supported command for uploaded scripts.";
        elseif( !strcasecmp($command, 'loadFile') )
            $error = "loadFile is not a supported command for uploaded scripts.";
        elseif( !strcasecmp($command, 'fileDialog') )
            $error = "fileDialog is not a supported command for uploaded scripts.";
    }
    
    if( !$ok )
        $error = "Invalid Script.  Please contact us if you need help with your test script.";
}

/**
* Make sure the URL they requested looks valid
* 
* @param mixed $test
* @param mixed $error
*/
function ValidateURL(&$test, &$error, &$settings)
{
    $url = parse_url($test['url']);
    $host = $url['host'];
    
    if( strpos($host, '.') === FALSE )
        $error = "Please enter a Valid URL.  <b>$host</b> is not a valid Internet host name";
    elseif( !strcmp($host, "127.0.0.1") || ((!strncmp($host, "192.168.", 8)  || !strncmp($host, "10.", 3)) && !$settings['allowPrivate']) )
        $error = "You can not test <b>$host</b> from the public Internet.  Your web site needs to be hosted on the public Internet for testing";
}

/**
* Generate a SNS authentication script for the given URL
* 
* @param mixed $test
*/
function GenerateSNSScript($test)
{
    $script = "logdata\t0\n\n";
    
    $script .= "setEventName\tLaunch\n";
    $script .= "setDOMElement\tname=loginId\n";
    $script .= "navigate\t" . 'https://my.screenname.aol.com/_cqr/login/login.psp?mcState=initialized&sitedomain=search.aol.com&authLev=1&siteState=OrigUrl%3Dhttp%253A%252F%252Fsearch.aol.com%252Faol%252Fwebhome&lang=en&locale=us&seamless=y' . "\n\n";

    $script .= "setValue\tname=loginId\t{$test['login']}\n";
    $script .= "setValue\tname=password\t{$test['password']}\n";
    $script .= "setEventName\tLogin\n";
    $script .= "submitForm\tname=AOLLoginForm\n\n";
    
    $script .= "logData\t1\n\n";
    
    if( strlen($test['domElement']) )
        $script .= "setDOMElement\t{$test['domElement']}\n";
    $script .= "navigate\t{$test['url']}\n";
    
    return $script;
}    

/**
* Submit the test request file to the server
* 
* @param mixed $run
* @param mixed $testRun
* @param mixed $test
*/
function SubmitUrl($run, $testRun, &$test)
{
    $ret = false;
    
    // make sure the work directory exists
    if( !is_dir($test['workdir']) )
        mkdir($test['workdir'], 0777, true);
    
    $out = '';
    if( !strlen($test['script']) )
        $out = $test['url'];
    else
    {
        $out = "script://$run.pts";
        
        // write out the script file
        file_put_contents($test['workdir'] . "/$run.pts", $test['script']);
    }
    
    // write out the actual test file
    $out .= $testRun;
    $ext = 'url';
    if( $test['priority'] )
        $ext = "p{$test['priority']}";
    if( file_put_contents($test['workdir'] . "/$run.$ext", $out) )
        $ret = true;
    
    return $ret;
}

/**
* Log the actual test in the test log file
* 
* @param mixed $test
*/
function LogTest(&$test)
{
    // open the log file
    $filename = "./logs/" . gmdate("Ymd") . ".log";
    $file = fopen( $filename, "a+b",  false);
    $video = 0;
    if( strlen($test['video']) )
        $video = 1;
    if( $file )
    {
        // TODO: add a timeout to the locking loop
        $ok = false;
        $count = 0;
        while( !$ok &&  $count < 500 )
        {
            $count++;
            if( flock($file, LOCK_EX) )
                $ok = true;
            else
                usleep(10000);
        }

        if( $ok )
        {            
            $ip = $_SERVER['REMOTE_ADDR'];
            if( $test['ip'] && strlen($test['ip']) )
                $ip = $test['ip'];
                
            $log = gmdate("Y-m-d G:i:s") . "\t$ip" . "\t0" . "\t0";
            $log .= "\t{$test['id']}" . "\t{$test['url']}" . "\t{$test['locationText']}" . "\t{$test['private']}";
            $log .= "\t{$test['uid']}" . "\t{$test['user']}" . "\t$video" . "\t{$test['label']}" . "\r\n";
            
            fwrite($file, $log);
        }
        
        fclose($file);
    }
}

/**
* Forward the test request to a remote system
* 
* @param mixed $test
* @param mixed $params
* @param mixed $error
*/
function SendRemoteTest(&$test, $params, &$error)
{
    // patch in the correct location (local to the remote test system)
    if( $test['remoteLocation'] )
        $params['location'] = $test['remoteLocation'];
        
    $data = http_build_query($params);
    $params = array('http' => array(
                      'method' => 'POST',
                      'content' => $data
                      ));
    $ctx = stream_context_create($params);
    $fp = fopen($test['remoteUrl'], 'r', false, $ctx);

    if( $fp )
    {
        $url = '';
        // see if we got redirected
        $meta_data = stream_get_meta_data($fp);
        foreach($meta_data['wrapper_data'] as $response) 
            if (substr(strtolower($response), 0, 10) == 'location: ') 
                $url = trim(substr($response, 10));
                
        // if we didn't get redirected, parse the body looking for the javascript redirect (from the old runtest.exe)
        unset($response);
        if( !strlen($url) )
        {
            $response = stream_get_contents($fp);
            if( $response )
            {
                $lines = explode("\n", $response);
                foreach( $lines as $line )
                {
                    if( !strncasecmp($line, "window.location", 15) )
                    {
                        $tokens = explode("=", $line);
                        $relativeUrl = trim($tokens[1], "\r\n\t \"");
                    }
                }
            }
            
            if( strlen($relativeUrl) )
            {
                // build the absolute URL
                $parts = parse_url($test['remoteUrl']);
                $url = $parts['scheme'] . "://" . $parts['host'];
                if( $parts['port'] )
                    $url .= ":" . $parts['port'];
                $url .= $relativeUrl;
            }
        }
                
        // redirect the requestor to the real result
        if( strlen($url) )
        {
            $test['id'] = $url;
            header("Location: $url");
        }
        else
            $error = "Error submitting the test request to the remote system (unexpected response).  Please try again later.";
    }
    else
        $error = "Error submitting the test request to the remote system.  Please try again later.";
}

/**
* Check the referrer to make sure it is the same as the host we are serviing from
* 
*/
function CheckReferrer()
{
    $ok = true;
/*    $settings = parse_ini_file('./settings/settings.ini');
    if( $settings['checkReferrer'] )
    {
        $host  = $_SERVER['HTTP_HOST'];
        $referrer = parse_url($_SERVER['HTTP_REFERER']);
        if( strcmp($host, $referrer['host']) )
        {
            $ok = false;
            
            // return a 403
            header("HTTP/1.0 403 Forbidden");
        }
    }
    */
    return $ok;
}

/**
* Make sure the requesting IP isn't on our block list
* 
*/
function CheckIp(&$test)
{
    $ok = true;
    $ip2 = $test['ip'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $blockIps = file('./settings/blockip.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach( $blockIps as $block )
    {
        $block = trim($block);
        if( strlen($block) )
        {
            if( ereg($block, $ip) )
            {
                $ok = false;
                break;
            }
            
            if( $ip2 && strlen($ip2) && ereg($block, $ip) )
            {
                $ok = false;
                break;
            }
        }
    }
    
    return $ok;
}

/**
* Make sure the url isn't on our block list
* 
* @param mixed $url
*/
function CheckUrl($url)
{
    $ok = true;
    $blockUrls = file('./settings/blockurl.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach( $blockUrls as $block )
    {
        $block = trim($block);
        if( strlen($block) && ereg($block, $url) )
        {
            $ok = false;
            break;
        }
    }
    
    return $ok;
}
?>

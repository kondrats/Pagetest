<?php 
include 'common.inc';
include 'auth.inc';

if( !$settings['maxruns'] )
    $settings['maxruns'] = 10;
    
$connectivity = parse_ini_file('./settings/connectivity.ini', true);
$locations = LoadLocations();
$loc = ParseLocations($locations);

$uid = NULL;
$user = NULL;
$admin = false;
// some myBB integration to get the requesting user
if( is_dir('./forums') && isset($_COOKIE['mybbuser']) )
{
    $dir = getcwd();
    try
    {
        define("IN_MYBB",1);
        chdir('forums'); // path to MyBB
        include './global.php';

        $uid = $mybb->user['uid'];
        $user = $mybb->user['username'];
        if( $mybb->usergroup['cancp'] )
            $admin = true;
    }
    catch(Exception $e)
    {
    }
    chdir($dir);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php echo $settings['product'] . ' web page performance test';?></title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, performance site web, internet performance, website performance, web applications testing, web application performance, Internet Tools, Web Development, Open Source, http viewer, debugger, http sniffer, ssl, monitor, http header, http header viewer">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">
        <script type="text/javascript" src="<?php echo $cdnPath; ?>/pagetest.js">
        /***********************************************
        * Tab Content script v2.2- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
        * This notice MUST stay intact for legal use
        * Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
        ***********************************************/
        </script>
        <style type="text/css">
        <?php 
            include 'pagestyle.css'; 
            include 'style.css';
        ?>
        </style>
        <script type="text/javascript" src="<?php echo $cdnPath; ?>/js/jquery.min.js"></script> 
        <script type="text/javascript">
        <?php 
            echo "var maxRuns = {$settings['maxruns']};\n";
            echo "var locations = " . json_encode($locations) . ";\n";
            echo "var connectivity = " . json_encode($connectivity) . ";\n";
            include './js/test.js'; 
        ?>
        </script>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'New Test';
            include 'header.inc';
            ?>
            <div class="content">
                <div class="form">
                <form name="urlEntry" action="/runtest.php" method="POST" onsubmit="return ValidateInput(this)">
                    <div>
                        <table>
                            <tr><td><span class="stepname">Step 1 - </span></td><td><span class="stepname">Enter Test URL: </span></td><td><input id="url" type="text" name="url" style="width:30em"></td></tr>
                            <?php
                            //if( $uid )
                                echo '<tr style=""><td></td><td style="text-align:right;">Label: </td><td><input id="label" type="text" name="label" style="width:30em"> (i.e. AOL News)</td></tr>';
                            ?>
                        </table>
                    </div>
                    <br>
                    <div class="stepname">Step 2 - Choose Test Location</div>
                    <div class="spacer"></div>
                    <div class="stepcontents">
                        <table class="locations" border="0">
                        <?php
                        echo "\n";
                        foreach($loc['locations'] as &$location)
                        {
                            $checked = '';
                            if( $location['checked'] )
                                $checked = ' checked=checked';
                                
                            echo "<tr><td><input id=\"location{$location['name']}\" type=\"radio\" name=\"where\" value=\"{$location['name']}\"$checked>{$location['label']}</td>";
                            echo "<td style=\"padding-left:2em;\">{$location['comment']}</td>";
                            echo "</tr>\n";
                        }
                        ?>
                        </table>
                    </div>
                    <br>
                    <div class="stepname">Step 3 - Choose a Configuration</div>
                    <div class="spacer"></div>
                    <div class="stepcontents">
                        <table class="configuration">
                        <tr>
                            <th>Browser:</th>
                            <th>Connection:</th>
                            <th>Bandwidth:</th>
                            <th>Pending Tests (Backlog):</th>
                        </tr>
                        <tr>
                            <td>
                                <select id="browser" size="4" style="width: 5em;">
                                <?php
                                echo "\n";
                                foreach( $loc['browsers'] as $key => &$browser )
                                {
                                    $selected = '';
                                    if( $browser['selected'] )
                                        $selected = ' selected';
                                    echo "<option value=\"{$browser['key']}\"$selected>{$browser['label']}</option>\n";
                                }
                                ?>
                                </select>
                            </td>
                            <td>
                                <select id="connection" name="location" size="4" style="width: 7em;">
                                <?php
                                echo "\n";
                                foreach( $loc['connections'] as $key => &$connection )
                                {
                                    $selected = '';
                                    if( $connection['selected'] )
                                        $selected = ' selected';
                                    echo "<option value=\"{$connection['key']}\"$selected>{$connection['label']}</option>\n";
                                }
                                ?>
                                </select>
                            </td>
                            <td style="text-align:right">
                            <?php
                                $disabled;
                                if( count($loc['connections']) )
                                {
                                    $disabled = ' disabled="disabled"';
                                }
                                echo '<table id="bwTable">';
                                echo '<tr><td class="label"><label>Down:</label></td><td class="value"><input id="bwDown" type="text" name="bwDown" style="width:3em; text-align: right;" value="' . $loc['bandwidth']['down'] . '"' . $disabled . '> Kbps</td></tr>';
                                echo '<tr><td class="label"><label>Up:</label></td><td class="value"><input id="bwUp" type="text" name="bwUp" style="width:3em; text-align: right;" value="' . $loc['bandwidth']['up'] . '"' . $disabled . '> Kbps</td></tr>';
                                echo '<tr><td class="label"><label>Latency:</label></td><td class="value"><input id="latency" type="text" name="latency" style="width:3em; text-align: right;" value="' . $loc['bandwidth']['latency'] . '"' . $disabled . '> ms</td></tr>';
                                echo '<tr><td class="label"><label>Pkt Loss:</label></td><td class="value"><input id="plr" type="text" name="plr" style="width:3em; text-align: right;" value="' . $loc['bandwidth']['plr'] . '"' . $disabled . '> %</td></tr>';
                                echo '</table>';
                            ?>
                            </td>
                            <td id="backlog" style="text-align: center; font-size: larger; vertical-align: middle;">
                            </td>
                        </tr>
                        </table>
                    </div>
                    <br>
                    <div class="stepname">Step 4 - Test Options</div>
                    <div class="spacer"></div>
                    <div class="stepcontents" id="optionsDiv">
                        <ul id="tabs" class="shadetabs">
                            <li><a href="#" rel="Basic" class="selected">Basic Settings</a></li>
                            <li><a href="#" rel="Advanced">Advanced Settings</a></li>
                            <li><a href="#" rel="Auth">Auth</a></li>
                            <li><a href="#" rel="Script">Script</a></li>

                            <?php if($settings['enableAdBlocking']) { ?>
                            <li><a href="#" rel="AdBlocking">Ad Blocking</a></li>
                            <?php } ?>

                            <li><a href="#" rel="Block">Block</a></li>

                            <?php if($settings['enableVideo'] || isset($_COOKIE['mybbuser'])) { ?>
                            <li><a href="#" rel="video">Video</a></li>
                            <?php } ?>
                        </ul>
                        
                        <div style="border:1px solid gray; width:42em; height:12em; margin-bottom: 1em; padding: 1em 15px 15px 15px"> 
                            <div id="Basic" class="tabcontent">
                                <?php
                                $runs = (int)$_COOKIE["runs"];
                                if( $runs < 1 || $runs > $settings['maxruns'] )
                                    $runs = 1;
                                ?>
                                Number of runs (1-<?php echo $settings['maxruns']; ?>): <input id="runs" size=3 maxlength=3 type="text" name="runs" value=<?php echo "\"$runs\""; ?>><br>
                                <br>
                                <?php
                                $fvOnly = (int)$_COOKIE["testOptions"] & 2;
                                ?>
                                <input id="viewBoth" type="radio" name="fvonly" <?php if( !$fvOnly ) echo 'checked=checked'; ?> value="0">First View and Repeat View<br>
                                <input id="viewFirst" type="radio" name="fvonly" <?php if( $fvOnly ) echo 'checked=checked'; ?> value="1">First View Only<br>
                                <br>
                                <input id="private" type="checkbox" name="private"<?php if( (int)$_COOKIE["testOptions"] & 1 ) echo " checked=checked"; ?>>Keep test results private (don't log them in the test history and use a non-guessable test ID)<br>
                            </div>

                            <div id="Advanced" class="tabcontent">
                                <input id="docComplete" type="checkbox" name="web10">Stop measurement at Document Complete (usually measures until activity stops)<br>
                                <input id="ignoreSSL" type="checkbox" name="ignoreSSL">Ignore SSL Certificate errors (name mismatch, self-signed certs, etc.)<br><br>
                                DOM Element: <input id="DOMElement" style="width:70%;" type="text" name="domelement">
                                <div class="tooltip">Waits for and records when the indicated DOM element becomes available on the page.  The DOM element 
                                is identified in <b>attribute=value</b> format where "attribute" is the attribute to match on (id, className, name, innerText, etc.)
                                and "value" is the value of that attribute (case sensitive).  For example, on SNS pages <b>name=loginId</b>
                                would be the DOM element for the Screen Name entry field.<br><br>
                                There are 3 special  attributes that will match on a HTTP request: <b>RequestEnd</b>, <b>RequestStart</b> and <b>RequestTTFB</b> will mark the End, Start or TTFB of the
                                first request that contains the given value in the url. i.e. <b>RequestTTFB=favicon.ico</b> will mark the first byte time of the favicon.ico request.
                                </div>
                            </div>
							
							<?php echo generateAuthForm( $settings );  ?>

                            <div id="Script" class="tabcontent">
                                Enter Script (Go <a href="http://sourceforge.net/apps/mediawiki/pagetest/index.php?title=Hosted_Scripting">here</a> for information on scripting):<br>
                                <textarea id="script" rows="8" cols="80" name="script"></textarea>
                            </div>

                            <?php if($settings['enableAdBlocking']) { ?>
                            <div id="AdBlocking" class="tabcontent">
                                Ad Behavior:<br><br>
                                <input id="adsNormal" type="radio" name="ads" value="normal" checked="checked"><b>Normal</b> (loads ads as expected)<br>
                                <input id="adsBlank" type="radio" name="ads" value="blank"><b>Blank</b> (Serves blank ads - UAC only)<br>
                                <input id="adsBlocked" type="radio" name="ads" value="blocked"><b>Blocked</b> (Blocks UAC, Quigo AdSonar and some Sponsored Links)<br>
                                <br><p>For more advanced blocking please use the "block" tab where you can block arbitrary content.</p>
                            </div>
                            <?php } ?>

                            <div id="Block" class="tabcontent">
                                Block requests containing (space separated list):<br>
                                <textarea id="block" rows="8" cols="80" name="block"></textarea>
                            </div>

                            <?php if($settings['enableVideo'] || isset($_COOKIE['mybbuser'])) { ?>
                            <div id="video" class="tabcontent">
                                <input id="videoCheck" type="checkbox" name="video">Capture video (alpha)<br><br>
                                This is still in the VERY early stages but you can capture a video of the page load for viewing and visually comparing to other pages.<br><br>
                                The video of the tested page is available on the screen shot page of the test results.  To compare against other tests, go into the test log and select 
                                the tests you want to compare.
                            </div>
                            <?php } ?>

                        </div>
                        <script type="text/javascript">
                        var tabs=new ddtabcontent("tabs")
                        tabs.setselectedClassTarget("link") //"link" or "linkparent"
                        tabs.init()
                        </script>                
                    </div>
                    <br>
                    <div class="stepname">Step 5 - Submit Test </div>
                    <div class="spacer"></div>
                    <div class="greytextbutton"><input class="artzBtn def" id="Submit" type="submit" value="Submit"></div>
                </form>
                </div>
            </div>
        </div>
        <?php if($settings[analytics]) { ?>
        <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
        var pageTracker = _gat._getTracker(<?php echo '"' . $settings[analytics] . '"'; ?>);
        pageTracker._initData();
        pageTracker._trackPageview();
        </script>
        <?php } ?>
    </body>
</html>

<?php

/**
* Load the location information
* 
*/
function LoadLocations()
{
    $locations = parse_ini_file('./settings/locations.ini', true);
    
    // strip out any sensitive information
    foreach( $locations as $index => &$loc )
    {
        // count the number of tests at each location
        if( isset($loc['localDir']) )
        {
            $loc['backlog'] = CountTests($loc['localDir']);
            unset( $loc['localDir'] );
        }
        
        if( isset($loc['key']) )
            unset( $loc['key'] );
        if( isset($loc['remoteDir']) )
            unset( $loc['remoteDir'] );
    }
    
    return $locations;
}

/**
* Run through the location selections and build the default selections (instead of doing this in javascript)
* 
* @param mixed $locations
*/
function ParseLocations(&$locations)
{
    global $connectivity;
    $loc = array();
    $loc['locations'] = array();

    // build the list of locations
    foreach($locations['locations'] as $index => $name)
    {
        if( is_numeric($index) )
        {
            if( !$locations[$name]['hidden'] || $_REQUEST['hidden'])
            {
                $location['label'] = $locations[$name]['label'];
                $location['comment'] = str_replace("'", '"', $locations[$name]['comment']);
                $location['name'] = $name;
                $loc['locations'][$name] = $location;
            }
        }
    }
    
    // see if they have a saved location from their cookie
    $currentLoc = GetLocationFromConfig($locations, $_COOKIE["cfg"] );
    if( !$currentLoc || !isset($loc['locations'][$currentLoc]) )
    {
        // nope, try thee default
        $currentLoc = $locations['locations']['default'];
    }
    if( !$currentLoc || !isset($loc['locations'][$currentLoc]) )
    {
        // if all else fails, just select the first one
        foreach( $loc['locations'] as $key => &$val )
        {
            $currentLoc = $key;
            break;
        }
    }
    
    // select the location
    $loc['locations'][$currentLoc]['checked'] = true;
    
    // build the list of browsers for the location
    $loc['browsers'] = array();
    foreach($locations[$currentLoc] as $index => $config)
    {
        if( is_numeric($index) )
        {
            $browser = $locations[$config]['browser'];
            $browserKey = str_replace(' ', '', $browser);
            if( strlen($browserKey) && strlen($browser) )
            {
                $b = array();
                $b['label'] = $browser;
                $b['key'] = $browserKey;
                $loc['browsers'][$browserKey] = $b;
            }
        }
    }
    
    // default to the browser from their saved cookie
    $currentBrowser;
    if( $_COOKIE["cfg"] && isset($locations[$_COOKIE["cfg"]]) )
    {
        $currentBrowser = str_replace(' ', '', $locations[$_COOKIE["cfg"]]['browser']);
        $currentConfig = $_COOKIE["cfg"];
    }
    if( !strlen($currentBrowser) || !isset($loc['browsers'][$currentBrowser]) )
    {
        // try the browser from the default config
        $cfg = $locations[$currentLoc]['default'];
        if( strlen($cfg) )
        {
            $currentBrowser = str_replace(' ', '', $locations[$cfg]['browser']);
            $currentConfig = $cfg;
        }
    }
    if( !strlen($currentBrowser) || !isset($loc['browsers'][$currentBrowser]) )
    {
        // just select the first one if all else fails
        foreach( $loc['browsers'] as $key => &$val )
        {
            $currentBrowser = $key;
            break;
        }
    }
    $loc['browsers'][$currentBrowser]['selected'] = true;
    
    // build the list of connection types
    $loc['bandwidth']['dynamic'] = false;
    $loc['connections'] = array();
    foreach($locations[$currentLoc] as $index => $config)
    {
        if( is_numeric($index) )
        {
            $browserKey = str_replace(' ', '', $locations[$config]['browser']);
            if( strlen($browserKey) && $browserKey == $currentBrowser )
            {
                if( isset($locations[$config]['connectivity']) )
                {
                    $connection = array();
                    $connection['key'] = $config;
                    $connection['label'] = $locations[$config]['connectivity'];
                    $loc['connections'][$config] = $connection;
                }
                else
                {
                    $loc['bandwidth']['dynamic'] = true;
                    $loc['bandwidth']['down'] = 1500;
                    $loc['bandwidth']['up'] = 384;
                    $loc['bandwidth']['latency'] = 50;
                    $loc['bandwidth']['plr'] = 0;

                    foreach( $connectivity as $key => &$conn )
                    {
                        $connKey = $config . '.' . $key;
                        if( !$currentConfig )
                            $currentConfig = $connKey;

                        $connection = array();
                        $connection['key'] = $connKey;
                        $connection['label'] = $conn['label'];
                        $loc['connections'][$connKey] = $connection;
                        
                        if( $currentConfig == $connKey )
                        {
                            $loc['bandwidth']['down'] = $conn['bwIn'] / 1000;
                            $loc['bandwidth']['up'] = $conn['bwOut'] / 1000;
                            $loc['bandwidth']['latency'] = $conn['latency'];
                            if( isset($conn['plr']) )
                                $loc['bandwidth']['plr'] = $conn['plr'];
                        }
                    }
                    
                    // add the custom config option
                    $connKey = $config . '.custom';
                    $connection = array();
                    $connection['key'] = $connKey;
                    $connection['label'] = 'Custom';
                    $loc['connections'][$connKey] = $connection;
                    
                    if( !$currentConfig )
                        $currentConfig = $connKey;
                }
            }
        }
    }
    
    // default to the first connection type if we don't have a better option
    if( !$currentConfig || !isset($loc['connections'][$currentConfig]) )
    {
        foreach( $loc['connections'] as $key => &$val )
        {
            $currentConfig = $key;
            break;
        }
    }
    $loc['connections'][$currentConfig]['selected'] = true;
    
    // figure out the bandwidth settings
    if( !$loc['bandwidth']['dynamic'] )
    {
        $loc['bandwidth']['down'] = $locations[$currentConfig]['down'] / 1000;
        $loc['bandwidth']['up'] = $locations[$currentConfig]['up'] / 1000;
        $loc['bandwidth']['latency'] = $locations[$currentConfig]['latency'];
        $loc['bandwidth']['plr'] = 0;
    }
    
    return $loc;
}

/**
* From a given configuration, figure out what location it is in
* 
* @param mixed $locations
* @param mixed $config
*/
function GetLocationFromConfig(&$locations, $config)
{
    $ret;
    
    foreach($locations as $location => &$values)
        foreach($values as $cfg)
            if( $cfg == $config )
            {
                $ret = $location;
                break 2;
            }
    
    return $ret;
}

/**
* Count the number of test files in the given directory
* 
* @param mixed $dir
* @param mixed $path
*/
function CountTests($path)
{
    $files = glob( $path . '/*.url', GLOB_NOSORT );
    $count = count($files);
    
    return $count;
}
?>

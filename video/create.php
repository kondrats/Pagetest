<?php
if( !isset($_REQUEST['tests']) && isset($_REQUEST['t']) )
{
    $tests = '';
    foreach($_REQUEST['t'] as $t)
    {
        $parts = explode(',', $t);
        if( count($parts) >= 1 )
        {
            if( strlen($tests) )
                $tests .= ',';
            $tests .= trim($parts[0]);
            if( $parts[1] )
                $tests .= "-r:{$parts[1]}";
            if( $parts[2] )
                $tests .= '-l:' . urlencode($parts[2]);
            if( $parts[3] )
                $tests .= "-c:{$parts[1]}";
        }
    }

    $host  = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['PHP_SELF'];
    $params = '';
    foreach( $_GET as $key => $value )
        if( $key != 't' && !is_array($value))
            $params .= "&$key=" . urlencode($value);
    header("Location: http://$host$uri?tests=$tests{$params}");    
}
else
{
    // move up to the base directory
    $cwd = getcwd();
    chdir('..');

    include 'common.inc';
    require_once('page_data.inc');
    require_once('video.inc');
    
    // make sure the work directory exists
    if( !is_dir('./work/video/tmp') )
        mkdir('./work/video/tmp', 0777, true);

    // get the list of tests and test runs
    $tests = array();
    $id = null;
    
    $exists = false;
    if( isset($_REQUEST['id']) )
    {
        // see if the video already exists
        $id = $_REQUEST['id'];
        $path = GetVideoPath($id);
        if( is_file("./$path/video.mp4") )
            $exists = true;
    }

    if( !$exists )
    {
        $labels = array();
        
        $compTests = explode(',', $_REQUEST['tests']);
        foreach($compTests as $t)
        {
            $parts = explode('-', $t);
            if( count($parts) >= 1 )
            {
                $test = array();
                $test['id'] = $parts[0];
                $test['cached'] = 0;
                
                for( $i = 1; $i < count($parts); $i++ )
                {
                    $p = explode(':', $parts[$i]);
                    if( count($p) >= 2 )
                    {
                        if( $p[0] == 'r' )
                            $test['run'] = (int)$p[1];
                        if( $p[0] == 'l' )
                            $test['label'] = urldecode($p[1]);
                        if( $p[0] == 'c' )
                            $test['cached'] = (int)$p[1];
                    }
                }
                
                $test['path'] = GetTestPath($test['id']);
                $test['pageData'] = loadAllPageData($test['path']);
                BuildVideoScripts("./{$test['path']}");

                if( !$test['run'] )
                    $test['run'] = GetMedianRun($test['pageData']);

                $test['videoPath'] = "./{$test['path']}/video_{$test['run']}";
                if( $test['cached'] )
                    $test['videoPath'] .= '_cached';
                    
                if( !strlen($test['label']) )
                    $test['label'] = trim(file_get_contents("./{$test['path']}/label.txt"));
                if( !strlen($test['label']) )
                    $test['label'] = trim(file_get_contents("./{$test['path']}/url.txt"));
                $labels[] = $test['label'];
                
                if( is_dir($test['videoPath']) )
                    $tests[] = $test;
            }
        }

        $count = count($tests);
        if( $count )
        {
            if( $count == 1 )
                $id = "{$test['id']}.{$test['run']}.{$test['cached']}";
            else
            {
                date_default_timezone_set('UTC');
                if( !strlen($id) )
                {
                    // try and create a deterministic id so multiple submissions of the same tests will result in the same id
                    if( strlen($_REQUEST['tests']) )
                    {
                        $date = date('ymd_');
                        $hashstr = $_REQUEST['tests'];
                        if( strpos($hashstr, '_') == 6 )
                            $date = substr($hashstr, 0, 7);
                        $id = $date . sha1($hashstr);
                    }
                    else
                        $id = date('ymd_') . md5(uniqid(rand(), true));
                }
            }

            if( $_REQUEST['slow'] )
                $id .= ".slow";
            $path = GetVideoPath($id);
            if( is_file("./$path/video.mp4") )
                $exists = true;

            if( !$exists )
            {                
                // load the appropriate script file
                $scriptFile = "./video/templates/$count.avs";
                if( strlen($_REQUEST['template']) )
                    $scriptFile = "./video/templates/{$_REQUEST['template']}.avs";
                
                $script = file_get_contents($scriptFile);
                if( strlen($script) )
                {
                    // figure out the job id
                    require_once('./lib/pclzip.lib.php');

                    $zipFile = "./work/video/tmp/$id.zip";
                    $zip = new PclZip($zipFile);
                    if( $zip )
                    {
                        // zip up the video files
                        foreach( $tests as $index => &$test )
                        {
                            $files = array();
                            $dir = opendir($test['videoPath']);
                            if( $dir )
                            {
                                while($file = readdir($dir)) 
                                {
                                    $path = $test['videoPath'] . "/$file";
                                    if( is_file($path) && (stripos($file, '.jpg') || stripos($file, '.avs')) &&  strpos($file, '.thm') === false )
                                        $files[] = $path;
                                }

                                closedir($dir);
                            }
                            
                            // update the label in the script
                            $script = str_replace("%$index%", $test['label'], $script);

                            if( count($files) )
                                $zip->add($files, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, "$index");
                        }
                    }
                    
                    // see if they want the video in slow motion
                    if( $_REQUEST['slow'] )
                        $script .= "\r\nAssumeFPS(2)\r\n";

                    // add the script to the zip file
                    $tmpScript = "./work/video/tmp/$id.avs";
                    file_put_contents($tmpScript, $script);
                    $zip->add($tmpScript, PCLZIP_CB_PRE_ADD, 'ZipAvsCallback');
                    unlink($tmpScript);
                    
                    // create an ini file for the job as well
                    $ini = "[info]\r\n";
                    $ini .= "id=$id\r\n";
                    $tmpIni = "./work/video/tmp/$id.ini";
                    file_put_contents($tmpIni, $ini);
                    $zip->add($tmpIni, PCLZIP_CB_PRE_ADD, 'ZipIniCallback');
                    unlink($tmpIni);
                    
                    // set up the result directory
                    $dest = './' . GetVideoPath($id);
                    if( !is_dir($dest) )
                        mkdir($dest, 0777, true);
                    if( is_file($scriptFile . '.ini') )
                        copy($scriptFile . '.ini', "$dest/video.ini");
                    if( count($labels) )
                        file_put_contents("$dest/labels.txt", json_encode($labels));
                    
                    // move the file to the video work directory
                    rename( $zipFile, "./work/video/$id.zip" );
                }
            }
        }
    }

    // redirect to the destination page
    if( $id )
    {
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: http://$host$uri/view.php?id=$id");    
    }
}

/**
* Override the script file name
* 
* @param mixed $p_event
* @param mixed $p_header
* @return mixed
*/
function ZipAvsCallback($p_event, &$p_header)
{
    $p_header['stored_filename'] = 'video.avs';
    return 1;
}

/**
* Override the ini file name
* 
* @param mixed $p_event
* @param mixed $p_header
* @return mixed
*/
function ZipIniCallback($p_event, &$p_header)
{
    $p_header['stored_filename'] = 'video.ini';
    return 1;
}
?>

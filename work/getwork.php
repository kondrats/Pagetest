<?php
set_time_limit(300);

// see if there is a video  job
$done = false;
if( $_GET['video'] )
    $done = GetVideoJob();

if( !$done )
{
    header('Content-type: text/plain');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

    $location = $_GET['location'];
    $key = $_GET['key'];

    // load all of the locations
    $locations = parse_ini_file('../settings/locations.ini', true);
    $settings = parse_ini_file('../settings/settings.ini');

    $workDir = $locations[$location]['localDir'];
    $locKey = $locations[$location]['key'];
    if( strlen($workDir) && (!strlen($locKey) || !strcmp($key, $locKey)) )
    {
        // keep track of the last time this location reported in
        if( !is_dir('./times') )
            mkdir('./times');
        $timeFile = fopen( "./times/$location.tm", "wb", false );
        if( $timeFile )
            fclose($timeFile);

        // lock the working directory for the given location
        $lockFile = fopen( $workDir . '/lock.dat', 'a+b',  false);
        if( $lockFile )
        {
            $ok = false;
            $count = 0;
            while( !$ok &&  $count < 500 )
            {
                $count++;
                if( flock($lockFile, LOCK_EX) )
                    $ok = true;
                else
                    usleep(10000);
            }
                
            // load the first work file
            $files = scandir($workDir);
            $fileName;
            $fileExt;
            $testId;
            
            // loop through all of the possible extension types in priority order
            $priority = array( "url", "p1", "p2", "p3", "p4", "p5", "p6", "p7", "p8", "p9" );
            foreach( $priority as $ext )
            {
                foreach( $files as $file )
                {
                    if(is_file("$workDir/$file"))
                    {
                        $parts = pathinfo($file);
                        if( !strcasecmp( $parts['extension'], $ext) )
                        {
                            $testId = basename($file, ".$ext");;
                            $fileName = "$workDir/$file";
                            $fileExt = $parts['extension'];
                            break 2;
                        }
                    }
                }
            }
            
            if( strlen($fileName) )
            {
                $testInfo = file_get_contents($fileName);
                echo "Test ID=$testId\r\nurl=" . $testInfo;
                unlink($fileName);
                
                // see if there is a script file
                $fileName = str_replace(".$fileExt", '.pts', $fileName);
                if( is_file($fileName) )
                {
                    $script = trim(file_get_contents($fileName));
                    unlink($fileName);
                    if( strlen($script) )
                    {
                        echo "\r\n[Script]\r\n";
                        echo $script;
                    }
                }
                $ok = true;
                
                // figure out the path to the results
                $testPath = "../results/$testId";
                if( strpos($testId, '_') == 6 )
                {
                    $parts = explode('_', $testId);
                    $testPath = '../results/' . substr($parts[0], 0, 2) . '/' . substr($parts[0], 2, 2) . '/' . substr($parts[0], 4, 2) . '/' . $parts[1];
                }
                elseif( strlen($settings['olddir']) )
                {
                    if( $settings['oldsubdir'] )
                        $testPath = "../results/{$settings['olddir']}/_" . strtoupper(substr($testId, 0, 1)) . "/$testId";
                    else
                        $testPath = "../results/{$settings['olddir']}/$testId";
                }

                // flag the test with the start time
                $ini = file_get_contents("$testPath/testinfo.ini");
                $start = "[test]\r\nstartTime=" . date("m/d/y G:i:s");
                $out = str_replace('[test]', $start, $ini);
                file_put_contents("$testPath/testinfo.ini", $out);
            }
            
            fclose($lockFile);
        }
    }
}

/**
* See if there is a video rendering job that needs to be done
* 
*/
function GetVideoJob()
{
    $ret = false;
    
    $videoDir = './video';
    if( is_dir($videoDir) )
    {
        // lock the directory
        $lockFile = fopen( $videoDir . '/lock.dat', 'a+b',  false);
        if( $lockFile )
        {
            $ok = false;
            $count = 0;
            while( !$ok &&  $count < 500 )
            {
                $count++;
                if( flock($lockFile, LOCK_EX) )
                    $ok = true;
                else
                    usleep(10000);
            }
                
            // look for the first zip file
            $dir = opendir($videoDir);
            if( $dir )
            {
                $testFile = null;
                while(!$testFile && $file = readdir($dir)) 
                {
                    $path = $videoDir . "/$file";
                    if( is_file($path) && stripos($file, '.zip') )
                        $testFile = $path;
                }
                
                if( $testFile )
                {
                    header('Content-Type: application/zip');
                    header("Cache-Control: no-cache, must-revalidate");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

                    readfile_chunked($testFile);
                    $ret = true;
                    
                    // delete the test file
                    unlink($testFile);
                }

                closedir($dir);
            }

            fclose($lockFile);
        }
    }
    
    return $ret;
}

/**
* Send a large file a chunk at a time
* 
* @param mixed $filename
* @param mixed $retbytes
* @return bool
*/
function readfile_chunked($filename, $retbytes = TRUE) 
{
    $buffer = '';
    $cnt =0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) 
    {
        return false;
    }
    while (!feof($handle)) 
    {
        $buffer = fread($handle, 1024 * 1024);  // 1MB at a time
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) 
        {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) 
    {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}
?>

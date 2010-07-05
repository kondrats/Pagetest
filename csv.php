<?php
header ("Content-type: text/csv");
include 'common.inc';

$fileType = 'IEWPG.txt';
if( $_GET['requests'] )
    $fileType = 'IEWTR.txt';

// loop through all  of the results files (one per run) - both cached and uncached
$includeHeader = true;
for( $i = 1; $i <= $test['test']['runs']; $i++ )
{
    // build up the file name
    $fileName = "$testPath/{$i}_$fileType";
    csvFile($fileName, $includeHeader);
    $includeHeader = false;
    $fileName = "$testPath/{$i}_Cached_$fileType";
    csvFile($fileName, $includeHeader);
}

/**
* Take a tab-separated file, convert it to csv and spit it out
* 
* @param mixed $fileName
* @param mixed $includeHeader
*/
function csvFile($fileName, $includeHeader)
{
    $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if( $lines)
    {
        // loop through each line in the file
        foreach($lines as $linenum => $line) 
        {
            if( $linenum > 0 || $includeHeader )
            {
                $line = trim($line);
                $line = str_replace('"', '""', $line);
                $line = str_replace('"', '""', $line);
                $line = str_replace("\t", '","', $line);
                echo '"' . $line . '"' . "\r\n";
            }
        }
    }
}
?>

<?php
// check and see if all of the locations have checked in within the last 30 minutes

header('Content-type: text/plain');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

$locations = parse_ini_file('../settings/locations.ini', true);
$settings = parse_ini_file('../settings/settings.ini');

$files = scandir('./times');
foreach( $files as $file )
{
    if(is_file("./times/$file"))
    {
        $parts = pathinfo($file);
        if( !strcasecmp( $parts['extension'], 'tm') )
        {
            $loc = basename($file, ".tm");;
            $fileName = "./times/$file";
            
            $updated = filemtime($fileName);
            $now = time();
            $elapsed = 0;
            if( $now > $updated )
                $elapsed = $now - $updated;
            $minutes = (int)($elapsed / 60);
            
            // if it has been over 30 minutes, send out a notification    
            if( $minutes > 30 )
            {
                // figure out who to notify
                $to = $settings['notify'];
                if( $locations[$loc]['notify'] )
                {
                    if( $to )
                        $to .= ',';
                    $to .= $locations[$loc]['notify'];
                }
                
                if( $to )
                    mail($to, "$loc WebPagetest ALERT", "The $loc location has not checked for new jobs in $minutes minutes." );
            }
            
            echo "$loc: $elapsed sec\r\n";
        }
    }
}
?>

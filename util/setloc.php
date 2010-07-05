<?php
// drop the location cookie
if( isset($_REQUEST['location']) && strlen($_REQUEST['location']) )
    setcookie('cfg', $_REQUEST['location'], time()+60*60*24*365, '/');

// now redirect
$host  = $_SERVER['HTTP_HOST'];
header("Location: http://$host/");    
?>

<?php 

/**
* Generate a authentication block
*
* @param array $settings
*
*/
function generateAuthForm( $settings ){
	$result = "<div id=\"Auth\" class=\"tabcontent\">";

	if($settings['authConfigFile']) {
		$result .= "<label>Select Auth Type: <select name=\"authType\">";
		$authBlocks = parse_ini_file( $settings['authConfigFile'], true);

		foreach($authBlocks as $authBlock => $authBlockContent) {
			$checkedAuth = '';

			// Ignore global Auths settings
			if( strcasecmp($authBlock, 'Auths') ) {

				// Check default auth block
				if( strlen($authBlocks['Auths']['default']) ) {
					if( !strcasecmp($authBlock, $authBlocks['Auths']['default']) ) {
						$checkedAuth = ' selected="selected"';
					}
				}
				$result .= "<option value=\"". $authBlock."\"".$checkedAuth.">".$authBlockContent["Title"]."</option>";
			}

		}
		$result .= "</select></label><br/>";

	} else {
		$result .= "HTTP Basic Authentication<br><br>";
	}
	$result .= "<label>Login: <input id=\"login\" style=\"width:20em;\" type=\"text\" name=\"login\"></label><br>";
	$result .= "<label>Password: <input id=\"password\" style=\"width:18em;\" type=\"password\" autocomplete=\"off\" name=\"password\"></label><br><br>";
	$result .= "<span style=\"color:red; font-weight:bold;\">PLEASE USE A TEST ACCOUNT!</span>&nbsp;";
	$result .= "We also strongly recommend making the test request private as your credentials may be available to anyone viewing the results.</div>";

	return $result; 	
}


/**
* Generate a authentication script
*
* @param mixed $test
*/
function generateAuthScript( $test, &$settings ) {

    $authBlocks = parse_ini_file( $settings['authConfigFile'] , true);
	
	// Get selected auth type
    $id = $test['authType'];
    
    // Switch of logging
    $script = "logData\t0\n\n";
	
    $script .= "setEventName\tCheck in\n";
    $script .= "setDOMElement\tname={$authBlocks[$id]['FormInputPassword']}\n";

    // Set URL of Auth Form location
    $script .= "navigate\t" . $authBlocks[$id]['FormURL'] . "\n\n";

    // Set value to login input
    $script .= "setValue\tname={$authBlocks[$id]['FormInputLogin']}\t{$test['login']}\n";

    // Set value to password input
    $script .= "setValue\tname={$authBlocks[$id]['FormInputPassword']}\t{$test['password']}\n";

    if( strlen($authBlocks[$id]['FormId']) ) {
        $script .= "submitForm\tid={$authBlocks[$id]['FormId']}\n\n";
    } else {
        $script .= "submitForm\tname={$authBlocks[$id]['FormName']}\n\n";
    }

    return $script;
}

?>

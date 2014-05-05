<?php

/**
* Ce script gère la migration des secrets et plus particulièrement l'externalisation des applications.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-05-02
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();

if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
	$_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}	

$Script = URL_BASE . $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];
$IP_Source = $_SERVER[ 'REMOTE_ADDR' ];

if ( ! isset( $_SESSION[ 'idn_id' ] ) )
	header( 'Location: ' . URL_BASE . '/SM-login.php' );

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );

$Action = '';
$Choose_Language = 0;


// Charge les libellés.
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-admin.php' );
//include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );


$PageHTML = new HTML();

if ( ! $PageHTML->is_connect() ) {
	header( 'Location: '. URL_BASE . '/SM-login.php' );
	exit();
}

$Groups = new IICA_Groups();

$Secrets = new IICA_Secrets();


$Alert_Syslog = $PageHTML->getParameter( 'alert_syslog' );
$Alert_Mail = $PageHTML->getParameter( 'alert_mail' );

$groupsRights = $PageHTML->getGroups( $_SESSION[ 'idn_id' ] );
//print_r( $groupsRights );

$Security = new Security();


if ( array_key_exists( 'Expired', $_SESSION ) ) {
	// Contrôle si la session n'a pas expirée.
	if ( ! $PageHTML->validTimeSession() ) {
		header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX&expired' );
	} else {
		$PageHTML->saveTimeSession();
	}
} else {
	header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX' );
}


if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}

$Verbosity_Alert = $PageHTML->getParameter( 'verbosity_alert' );
	
$innerJS = '';

$JS_Scripts = array( 'Ajax_secrets.js', 'Ajax_admin.js', 'SecretManager.js' );

 // Cas de l'import des fonctions JS gérant les mots de passe.
if ( preg_match("/^SCR/i", $Action ) ) {
    include( DIR_LIBRARIES . '/password_js.php' );
    $JS_Scripts[] = 'Ajax_home.js';
}

$L_Title = "Migrate in v0.8-4";

if ( ! preg_match("/X$/i", $Action ) ) {
	print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $JS_Scripts, $innerJS ) .
	 "   <!-- debut : zoneTitre -->\n" .
	 "   <div id=\"zoneTitre\">\n" .
	 "    <div id=\"icon-access\" class=\"icon36\"></div>\n" .
	 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
	 $PageHTML->afficherActions( $PageHTML->is_administrator() ) .
	 "   </div> <!-- fin : zoneTitre -->\n" .
	 "\n" .
	 "   <!-- debut : zoneMilieuComplet -->\n" .
	 "   <div id=\"zoneMilieuComplet\">\n" .
	 "\n" );
}

if ( isset( $_POST[ 'iMessage']) ) {
	print( "<script>\n" .
	 "     var myVar=setInterval(function(){cacherInfo()},3000);\n" .
	 "     function cacherInfo() {\n" .
	 "        document.getElementById(\"success\").style.display = \"none\";\n" .
	 "        clearInterval(myVar);\n" .
	 "     }\n" .
	 "</script>\n" .
	 "    <div id=\"success\">\n" .
	 $_POST[ 'iMessage' ] .
	 "    </div>\n" );
}

print( "    <div id=\"dashboard\">\n" .
	"     <table class=\"table-bordered\" style=\"margin: 10px auto;width: 95%;\">\n" .
	"      <thead>\n" .
	"       <tr>\n" .
	"        <th colspan=\"4\">" . $L_Title . "</th>\n" .
	"       </tr>\n" .
	"      </thead>\n" );


switch( $Action ) {
 default:
	print( "      <tbody id=\"liste\">\n" .
		"       <tr>\n" .
		"        <td>Do you confirm this migration?</td>\n" .
		"        <td>\n" .
		"Before clicking [Yes], make sure you have excute the file [Installation/upd-1-v0.8-4-SecretManager.sql] in MySQL with [root] user<br/>" .
		"<a class=\"button\" href=\"" . $Script . "?action=migrate\">Yes</a>" .
		"<a class=\"button\" href=\"" . URL_BASE . "/SM-home.php\">No</a>" .
		"        </td>\n" .
		"       </tr>\n" .
		"      </tbody>\n" );

	break;


 case 'MIGRATE':
	print( "      <tbody id=\"liste\">\n" );

	// ========
	// Step 1.
	print( "       <tr>\n" .
		"        <td style=\"width:50%\">Check new column in table \"scr_secrets\"</td>\n" );

	$Error = '';
	$Request = 'SELECT app_id FROM scr_secrets;';

	if ( ! $Result = $PageHTML->prepare( $Request ) ) {
		$Error = $Result->errorInfo();
	}
	
	if ( ! @$Result->execute() ) {
		$Error = $Result->errorInfo();
	}

	if ( $Error != '' ) {
		$Status = $Error[2];
		$Class = "bg-orange";
	} else {
		$Status = "OK";
		$Class = "bg-green";
	}
	
	print( "        <td><span class=\"bold " . $Class . "\">&nbsp;" . $Status . "&nbsp;</span></td>\n" .
		"       </tr>\n" );

	// ========
	// Step 2.
	print( "       <tr>\n" .
		"        <td>Check the new table \"app_applications\"</td>\n" );

	$Error = '';
	$Request = 'SELECT app_id FROM app_applications;';

	if ( ! $Result = $PageHTML->prepare( $Request ) ) {
		$Error = $Result->errorInfo();
	}
	
	if ( ! @$Result->execute() ) {
		$Error = $Result->errorInfo();
	}

	if ( $Error != '' ) {
		$Status = $Error[2];
		$Class = "bg-orange";
	} else {
		$Status = "OK";
		$Class = "bg-green";
	}
	
	print( "        <td><span class=\"bold " . $Class . "\">&nbsp;" . $Status . "&nbsp;</span></td>\n" .
		"       </tr>\n" );

	// ========
	// Step 3.
	print( "       <tr>\n" .
		"        <td>Export application from table \"scr_secrets\" to table \"app_applications\"</td>\n" );

	$Error = '';
	$Request = 'SELECT DISTINCT scr_application FROM scr_secrets WHERE scr_application IS NOT NULL AND scr_application <> \'\';';

	if ( ! $Result = $PageHTML->prepare( $Request ) ) {
		$Error = $Result->errorInfo();
	}
	
	if ( ! @$Result->execute() ) {
		$Error = $Result->errorInfo();
	}

	while ( $Occurrence = $Result->fetchObject() ) {
		$Request_1 = 'INSERT INTO app_applications ( app_name ) VALUES (\'' . $Occurrence->scr_application . '\');';

		if ( ! $Result_1 = $PageHTML->prepare( $Request_1 ) ) {
			$Error = $Result_1->errorInfo();
			break;
		}
		
		if ( ! @$Result_1->execute() ) {
			$Error = $Result_1->errorInfo();
			break;
		}
	}

	if ( $Error != '' ) {
		$Status = $Error[2];
		$Class = "bg-orange";
	} else {
		$Status = "OK";
		$Class = "bg-green";
	}
	
	print( "        <td><span class=\"bold " . $Class . "\">&nbsp;" . $Status . "&nbsp;</span></td>\n" .
		"       </tr>\n" );

	// ========
	// Step 4.
	print( "       <tr>\n" .
		"        <td>Synchronize table \"scr_secrets\" width \"id\" from table \"app_applications\"</td>\n" );

	$Error = '';
	$Request = 'SELECT app_id,app_name FROM app_applications;';

	if ( ! $Result = $PageHTML->prepare( $Request ) ) {
		$Error = $Result->errorInfo();
	}
	
	if ( ! @$Result->execute() ) {
		$Error = $Result->errorInfo();
	}

	while ( $Occurrence = $Result->fetchObject() ) {
		$Request_1 = 'UPDATE scr_secrets SET app_id=\'' . $Occurrence->app_id . '\' WHERE scr_application=\'' . $Occurrence->app_name . '\';';

		if ( ! $Result_1 = $PageHTML->prepare( $Request_1 ) ) {
			$Error = $Result_1->errorInfo();
			break;
		}
		
		if ( ! @$Result_1->execute() ) {
			$Error = $Result_1->errorInfo();
			break;
		}
	}

	if ( $Error != '' ) {
		$Status = $Error[2];
		$Class = "bg-orange";
	} else {
		$Status = "OK";
		$Class = "bg-green";
	}
	
	print( "        <td><span class=\"bold " . $Class . "\">&nbsp;" . $Status . "&nbsp;</span></td>\n" .
		"       </tr>\n" .
		"       <tr>\n" .
		"        <td colspan=\"2\" class=\"align-center\"><span class=\"bold orange\">After you're control, don't forget to execute the file [Installation/upd-2-v0.8-4-SecretManager.sql] in MySQL with [root] user</span></td>\n" .
		"       </tr>\n" );

	print( "      </tbody>\n" );

	break;
}

$Logout_button = 1;

print( "      <tfoot>\n" .
 "       <tr><th>&nbsp;</th><th><a class=\"button\" href=\"" . URL_BASE . "/SM-home.php\">Exit</a></th></tr>\n" .
 "      </tfoot>\n" .
 "     </table>\n" .
 "    </div> <!-- fin : dashboard -->\n" .
 "   </div> <!-- fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( $Logout_button ) .
 $PageHTML->piedPageHTML() );

?>
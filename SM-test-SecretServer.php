<?php

/**
* Ce script gère la connexion, la déconnexion et le changement de mot de passe
* des utilisateurs.
*
* @brief Gestion des connexions des utiisateurs
* @author Pierre-Luc MARY
* @date 2012-10
* @version 1.0
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
*
* @param[in] $_GET['action'] Action spécifique à faire réaliser par le composant
* @param[in] $_GET['expired'] Information pour faire afficher un message spécifique
* @param[in] $_GET['mandatory'] Information pour faire afficher un message spécifique
* @param[in] $_GET['rp'] Précise la page de retour (pour peut qu'il soit possible de réaliser ce retour
*/

session_start();

include_once( 'Libraries/Constants.php' );

// Initialise la langue Française par défaut.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'en';

// Récupère le code langue, quand celui-ci est précisé.
if ( array_key_exists( 'Lang', $_GET ) ) {
	$_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}

$_SESSION[ 'idn_login' ] = 'plm';
$_SESSION[ 'Expired' ] = time() + 3600;
$_SESSION[ 'idn_super_admin' ] = 1;

$Script = $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];
$IP_Source = $_SERVER[ 'REMOTE_ADDR' ];

// Force la connexion en HTTPS.
if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: https://' . $Server . $URI );

$Action = '';
$Choose_Language = 1;

include( 'Libraries/Class_HTML.inc.php' );
//include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
include( 'Libraries/Config_Access_DB.inc.php' );
include( 'Libraries/Config_Hash.inc.php' );

include( 'Libraries/Class_Secrets_Server.inc.php' );
$Secret_Server = new Secret_Server();


// Initialise l'objet de gestion des pages HTML.
$PageHTML = new HTML();

// Initialise l'objet de gestion des authentifications.
include( 'Libraries/Class_IICA_Authentications_PDO.inc.php' );
$Authentication = new IICA_Authentications( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

// Initialise l'objet de gestion des paramètres.
include( 'Libraries/Class_IICA_Parameters_PDO.inc.php' );
$Parameters = new IICA_Parameters( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

// Initialise l'objet de gestion des paramètres.
include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
$Secrets = new IICA_Secrets( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

// Initialise l'objet de gestion des entrés et sorties.
include_once( 'Libraries/Class_Security.inc.php' );
$Security = new Security();


// Récupère l'action spécifique à réaliser dans ce script.
if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}


// Exécute l'action spécifique à réaliser.
switch( $Action ) {
 default:
	$L_Title = 'Test SecretServer';

	print( $PageHTML->enteteHTML( $L_Title ) .
	 "   <!-- debut : zoneTitre -->\n" .
	 "   <div id=\"zoneTitre\">\n" .
	 "    <div id=\"icon-users\" class=\"icon36\"></div>\n" .
	 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
	 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
	 "    </div> <!-- Fin : zoneTitre -->\n" .
	 "\n" .
	 "    <div id=\"zoneGauche\" >&nbsp;</div>\n" .
	 "    <!-- debut : zoneMilieuComplet -->\n" .
	 "    <div id=\"zoneMilieuComplet\">\n" .
	 "     <center>\n" );
	 
	
	print( "      <form method=\"post\" name=\"connectForm\" action=\"". 
	 $Script . "?action=CMD\" style=\"width:90%;\">\n" .
	 "       <center>\n" .
var_dump( $_SESSION ) .
	 "        <table>\n" .
	 "         <tr>\n" .
	 "          <th colspan=\"6\">Select testing command</th>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'set_path'</td>\n" .
	 "          <td colspan=\"5\"><input type=\"radio\" name=\"Command\" value=\"set_path\"/> no parameter need</td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'load'</td>\n" .
	 "          <td><input type=\"radio\" name=\"Command\" value=\"load\"/></td>\n" .
	 "          <td>Operator key</td>\n" .
	 "          <td colspan=\"3\"><input type=\"text\" name=\"O_Key_1\" /></td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'status'</td>\n" .
	 "          <td colspan=\"5\"><input type=\"radio\" name=\"Command\" value=\"status\"/> no paramter need</td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'init'</td>\n" .
	 "          <td><input type=\"radio\" name=\"Command\" value=\"init\"/></td>\n" .
	 "          <td>Operator key</td>\n" .
	 "          <td><input type=\"text\" name=\"O_Key_2\" /></td>\n" .
	 "          <td>Mother key</td>\n" .
	 "          <td><input type=\"text\" name=\"M_Key_1\" /></td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'encrypt'</td>\n" .
	 "          <td><input type=\"radio\" name=\"Command\" value=\"encrypt\"/></td>\n" .
	 "          <td>Value</td>\n" .
	 "          <td colspan=\"3\"><input type=\"text\" name=\"Encrypt_Value\" /></td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'decrypt'</td>\n" .
	 "          <td><input type=\"radio\" name=\"Command\" value=\"decrypt\"/></td>\n" .
	 "          <td>Value</td>\n" .
	 "          <td colspan=\"3\"><input type=\"text\" name=\"Decrypt_Value\" /></td>\n" .
	 "         </tr>\n" .

	 "         <tr>\n" .
	 "          <td>Command 'shutdown'</td>\n" .
	 "          <td colspan=\"5\"><input type=\"radio\" name=\"Command\" value=\"shutdown\"/> no parameter need</td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td>&nbsp;</td>\n" .
	 "          <td colspan=\"5\"><input type=\"submit\" class=\"button\" value=\"Send\" /></td>\n" .
	 "         </tr>\n" .
	 "        </table>\n" .
	 "       </center>\n" .
	 "       <script>\n" .
	 "        document.connectForm.Command.focus();\n" .
	 "       </script>\n" .
	 "      </form>\n" .
	 "     </center>\n" .
	 "    </div> <!-- fin : zoneMilieuComplet -->\n" .
	 $PageHTML->construireFooter() .
	 $PageHTML->piedPageHTML() );

	break;


 // Enregistre le changement de mot de passe.
 case 'CMD':
	include( 'Libraries/Config_Hash.inc.php' );
	include( 'Libraries/Config_Authentication.inc.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
	
	$Flag_Error = 0;
	
	print( 'Exécute commande : ' . $_POST[ 'Command' ] . "<br/>" );

	switch( $_POST[ 'Command' ] ) {
	 case 'set_path':
		try {
			$Result = $Secret_Server->SS_setSessionPath();
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}
		break;

	 case 'load':
		try {
			$Result = $Secret_Server->SS_loadMotherKey( $_POST[ 'O_Key_1' ] );
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}

		break;

	 case 'init':
		try {
			list( $Status, $O_Key, $M_Key ) = $Secret_Server->SS_initMotherKey(
			 $_POST[ 'O_Key_2' ], $_POST[ 'M_Key_1' ] );

			$Result = "Status = '" . $Status . "', Operator Key = '" . $O_Key . "', " .
			 "Mother Key = '" . $M_Key . "'";
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
			}

		break;

	 case 'status':
		try {
			list( $Status, $Operator, $Creating_Date ) = $Secret_Server->SS_statusMotherKey();
			$Result = "Status = '$Status', Operator = '$Operator', Creating date = '$Creating_Date'";
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}

		break;

	 case 'encrypt':
		try {
			$Result = $Secret_Server->SS_encryptValue( $_POST[ 'Encrypt_Value' ] );
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}

		break;

	 case 'decrypt':
		try {
			$Result = $Secret_Server->SS_decryptValue( $_POST[ 'Decrypt_Value' ] );
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}

		break;

	 case 'shutdown':
		try {
			$Result = $Secret_Server->SS_Shutdown();
		} catch( Exception $e ) {
			$Result = $e->getMessage();
			$Flag_Error = 1;
		}

		break;
	}

	if ( $Flag_Error == 1 ) {
		$Result = ${rtrim( $Result )};
	}

	print( $PageHTML->returnPage( 'SecretServer result', $Result, $Script, $Flag_Error ) );

	break;
}
?>
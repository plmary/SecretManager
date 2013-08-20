<?php

/**
* Ce script gère la connexion, la déconnexion et le changement de mot de passe
* des utilisateurs.
*
* @brief Gestion des connexions des utiisateurs
* @author Pierre-Luc MARY
* @date 2013-07-09
* @version 1.1
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
*
* @param[in] $_GET['action'] Action spécifique à faire réaliser par le composant
* @param[in] $_GET['expired'] Information pour faire afficher un message spécifique
* @param[in] $_GET['mandatory'] Information pour faire afficher un message spécifique
* @param[in] $_GET['rp'] Précise la page de retour (pour peut qu'il soit possible de réaliser ce retour
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();

// Initialise la langue Française par défaut.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

// Récupère le code langue, quand celui-ci est précisé.
if ( array_key_exists( 'Lang', $_GET ) ) {
   $_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}

$Script = URL_BASE . $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];
$IP_Source = $_SERVER[ 'REMOTE_ADDR' ];

// Force la connexion en HTTPS.
if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );

$Action = '';
$Choose_Language = 1;

include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );


// Initialise l'objet de gestion des pages HTML.
$PageHTML = new HTML();

// Initialise l'objet de gestion des paramètres.
$Secrets = new IICA_Secrets();

// Initialise l'objet de gestion des entrés et sorties.
$Security = new Security();


// Récupère l'action spécifique à réaliser dans ce script.
if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}


// Exécute l'action spécifique à réaliser.
switch( $Action ) {
 // Traite la déconnexion d'un utilisateur.
 case 'DCNX':
	if ( array_key_exists( 'expired', $_GET ) ) {
		if ( strpos( $Script, '?' ) === false ) {
			$Signal = '?expired';
		} else {
			$Signal = '&expired';
		}
	} else $Signal = '';
	
	// Formate le message en événement standard
	$alert_message = $Secrets->formatHistoryMessage( $L_Disconnect . ' ' .
	 $_SESSION[ 'cvl_first_name' ] . ' ' . $_SESSION[ 'cvl_last_name' ] .
	 '(' . $_SESSION[ 'idn_login' ] . ')' );

	// Stocke le message dans l'historique de SecretManager.
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

	$PageHTML->disconnect();
   
	header( 'Location: ' . URL_BASE . '/SM-login.php' .
	 $Signal );

	break;


 // Traite le changement de mot de passe d'un utilisateur.
 case 'CMDP':
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	
	if ( array_key_exists( 'rp', $_GET ) ) {
		$Previous_Page = URL_BASE . '/SM-' . $_GET[ 'rp' ] . '.php';
	} else {
		$Previous_Page = $Script . '?action=DCNX';
	}
	
	print( $PageHTML->enteteHTML( $L_Title ) .
	 "   <!-- debut : zoneTitre -->\n" .
	 "   <div id=\"zoneTitre\">\n" .
	 "    <div id=\"icon-users\" class=\"icon36\"></div>\n" .
	 "    <span id=\"titre\">". $L_Title_CMDP . "</span>\n" .
	 $PageHTML->afficherActions( $PageHTML->is_administrator() ) .
	 "    </div> <!-- Fin : zoneTitre -->\n" .
	 "\n" .
	 "    <div id=\"zoneGauche\" >&nbsp;</div>\n" .
	 "    <!-- debut : zoneMilieuComplet -->\n" .
	 "    <div id=\"zoneMilieuComplet\">\n" .
	 "     <center>\n" );
	 
	if ( array_key_exists( 'mandatory', $_GET ) ) {
    	print( "        <h3 id=\"alert\">" . $L_Change_Password ."</h3>\n" );
    }

		print( "     <script>\n" .
		 "function checkPassword(Password_Field, Result_Field, Complexity, Size) {\n" .
		 " var Ok_Size = 0;\n" .
		 " var Result = '';\n" .
		 " var pwd = document.getElementById(Password_Field).value;\n" .
		 " if ( Complexity < 1 || Complexity > 3 ) Complexity = 3;\n" .
		 " if ( pwd.length < Size ) {\n" .
		 "  Result += '" . $L_No_Good_Size . " ' + Size + '). ';\n" .
		 "  document.getElementById(Result_Field).title = Result;\n" .
		 " }\n" .
		 " switch( Complexity ) {\n" .
		 "  case 1:\n" .
		 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
		 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
		 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
		 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
		 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   break;\n" .
		 "  case 2:\n" .
		 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
		 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
		 "   var regex_num = new RegExp('[0-9]', 'g');\n" .
		 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
		 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
		 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   if ( ! pwd.match( regex_num ) ) {\n" .
		 "    Result += '" . $L_Use_Number . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   break;\n" .
		 "  case 3:\n" .
		 "   var regex_lcase = new RegExp('[a-z]', 'g');\n" .
		 "   var regex_ucase = new RegExp('[A-Z]', 'g');\n" .
		 "   var regex_num = new RegExp('[0-9]', 'g');\n" .
		 "   var regex_sc = new RegExp('[^\\\\w]', 'g');\n" .
		 "   if ( ! pwd.match( regex_lcase ) ) {\n" .
		 "    Result += '" . $L_Use_Lowercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   if ( ! pwd.match( regex_ucase ) ) {\n" .
		 "    Result += '" . $L_Use_Uppercase . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "	 if ( ! pwd.match( regex_num ) ) {\n" .
		 "    Result += '" . $L_Use_Number . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   if ( ! pwd.match( regex_sc ) ) {\n" .
		 "    Result += '" . $L_Use_Special_Chars . ". ';\n" .
		 "    document.getElementById(Result_Field).title = Result;\n" .
		 "   }\n" .
		 "   break;\n" .
		 "  }\n" .
		 "  if ( Result != '' && pwd != '' ) {\n" .
		 "   document.getElementById(Result_Field).alt = 'Ko';\n" .
		 "   document.getElementById(Result_Field).src = '" . URL_PICTURES . "/s_attention.png'\n" .
		 "  }\n" .
		 "  if ( Result == '' && pwd != '' ) {\n" .
		 "   document.getElementById(Result_Field).alt = 'Ok';\n" .
		 "   document.getElementById(Result_Field).title = 'Ok';\n" .
		 "   document.getElementById(Result_Field).src = '" . URL_PICTURES . "/s_okay.png'\n" .
		 "  }\n" .
		 "}\n" .
		 "     </script>\n" );
	
	print( "      <form method=\"post\" name=\"connectForm\" action=\"". 
	 $Script . "?action=CMDPX\" style=\"width:50%;\">\n" .
	 "       <center>\n" .
	 "        <table>\n" .
	 "         <tr>\n" .
	 "          <td>" . $L_Password . "</td>\n" .
	 "          <td><input type=\"password\" name=\"O_Password\" /></td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td>" . $L_New_Password . "</td>\n" .
	 "          <td><input id=\"iPassword\" type=\"password\" name=\"N_Password\"  onkeyup=\"checkPassword('iPassword', 'Result', " . $_Password_Complexity . ", " . $_Min_Size_Password . ");\" onchange=\"checkPassword('iPassword', 'Result', " . $_Password_Complexity . ", " . $_Min_Size_Password . ");\" /><img id=\"Result\" class=\"no-border\" alt=\"Ok\" src=\"" . URL_PICTURES . "/blank.gif\" width=\"16\" /></td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td>" . $L_Conf_Password . "</td>\n" .
	 "          <td><input type=\"password\" name=\"C_Password\" /></td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td>&nbsp;</td>\n" .
	 "          <td><input type=\"submit\" class=\"button\" value=\"" . 
	 $L_Modify . "\" /><a href=\"" . $Previous_Page . "\" class=\"button\">" . 
	 $L_Return . "</a></td>\n" .
	 "         </tr>\n" .
	 "        </table>\n" .
	 "       </center>\n" .
	 "       <script>\n" .
	 "        document.connectForm.User.focus();\n" .
	 "       </script>\n" .
	 "      </form>\n" .
	 "     </center>\n" .
	 "    </div> <!-- fin : zoneMilieuComplet -->\n" .
	 "    <script>\n" .
	 "     document.connectForm.O_Password.focus();\n" .
	 "    </script>\n" .
	 $PageHTML->construireFooter() .
	 $PageHTML->piedPageHTML() );

	break;


 // Enregistre le changement de mot de passe.
 case 'CMDPX':
	include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

	$Secrets = new IICA_Secrets();

	
	$Error = 0;

	if ( $_POST[ 'O_Password' ] == '' or $_POST[ 'N_Password' ] == ''
	 or $_POST[ 'C_Password' ] == '' ) {
		$Error_Message = $L_ERR_Mandatories_Fields;
		$Error = 1;
	}
	
	if (  $_POST[ 'N_Password' ] != $_POST[ 'C_Password' ] ) {
		$Error_Message = $L_ERR_Password_Confirmation;
		$Error = 1;
	}
	
	if ( $_POST[ 'O_Password' ] == $_POST[ 'N_Password' ] ) {
		$Error_Message = $L_ERR_Old_Password_Forbidden;
		$Error = 1;
	}
	
	if ( strlen( $_POST[ 'N_Password' ] ) < $_Min_Size_Password ) {
		$Error_Message = $L_ERR_Min_Size;
		$Error = 1;
	}
	
	
	if ( ! $Security->complexityPasswordControl( $_POST[ 'N_Password' ],
	 $_Password_Complexity ) ) {
		$Error_Message = ${'L_ERR_Complexity_' . $_Password_Complexity} ;
		$Error = 1;
	}

	
	if ( $Error == 1 ) {
		print( $PageHTML->returnPage( $L_Title_CMDP, $Error_Message, $Script .
		 "?action=CMDP" ) );

		exit();
	}
	
	try {
		if ( ! $PageHTML->changePassword( $_SESSION[ 'idn_id' ],
		 $_POST[ 'O_Password' ], $_POST[ 'N_Password' ] ) ) {
			print( $PageHTML->returnPage( $L_Title_CMDP, $L_ERR_Modify_Password, $Script .
			 "?action=CMDP" ) );

			exit();
		}
	} catch( Exception $e ) {
		print( $PageHTML->returnPage( $L_Title_CMDP, $e->getMessage(), $Script .
		 "?action=CMDP" ) );

		exit();
	}

	$alert_message = $Secrets->formatHistoryMessage( $_SESSION[ 'cvl_first_name' ] . ' ' .
	 $_SESSION[ 'cvl_last_name' ] . '(' . $_SESSION[ 'idn_login' ] . ') - ' .
	 $L_Password_Modified );

	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

	$PageHTML->disconnect();

	$Return_Page = $Script;

	print( 	"<form method=\"post\" name=\"fInfoMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"infoMessage\" value=\"". $L_Password_Modified . "\" />\n" .
		"</form>\n" .
		"<script>document.fInfoMessage.submit();</script>\n" );

	break;


 // Récueille les informations d'authentification.
 default:
	include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
   
	print( $PageHTML->enteteHTML( $L_Title, $Choose_Language ) .
     "    <div id=\"icon-users\" class=\"icon36\" style=\"float: left; margin: 3px 9px 3px 3px;\"></div>\n" .
	 "    <h2>" . $L_Title . "</h2>\n" .
	 "    <div id=\"zoneGauche\" >&nbsp;</div>\n" .
	 "    <!-- debut : zoneMilieuComplet -->\n" .
	 "    <div id=\"zoneMilieuComplet\">\n" );

	if ( array_key_exists( 'infoMessage', $_POST ) ) {
		print( "<script>\n" .
		 "     var myVar=setInterval(function(){cacherInfo()},3000);\n" .
		 "     function cacherInfo() {\n" .
		 "        document.getElementById(\"msgSuccess\").style.display = \"none\";\n" .
		 "        clearInterval(myVar);\n" .
		 "     }\n" .
		 "</script>\n" .
		 "    <div id=\"msgSuccess\">\n" .
		 $_POST[ 'infoMessage' ] .
		 "    </div>\n" );
    }

	print( "     <center>\n" .
	 "      <form method=\"post\" name=\"connectForm\" action=\"". 
     $Script . "?action=cnx\" style=\"width:50%;\">\n" );

	if ( array_key_exists( 'expired', $_GET ) ) {
    	print( "        <h3 id=\"alert\">" . $L_User_Session_Expired ."</h3>\n" );
    }

	print( "        <table class=\"espace-20 espace-interne-10\">\n" .
	 "         <tr>\n" .
	 "          <td class=\"align-middle\"><label class=\"control-label\" for=\"iUser\">" . $L_Username . "</label></td>\n" .
	 "          <td class=\"align-middle\"><input type=\"text\" name=\"User\" id=\"iUser\" /></td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td class=\"align-middle\"><label class=\"control-label\" for=\"iPassword\">" . $L_Password . "</label></td>\n" .
	 "          <td class=\"align-middle\"><input type=\"password\" name=\"Password\" id=\"iPassword\" /></td>\n" .
	 "         </tr>\n" .
	 "         <tr>\n" .
	 "          <td>&nbsp;</td>\n" .
	 "          <td class=\"espace-interne-10\"><input type=\"submit\" class=\"button\" value=\"" . 
	 $L_Connect . "\" /></td>\n" .
	 "         </tr>\n" .
	 "        </table>\n" .
	 "       <script>\n" .
	 "        document.connectForm.User.focus();\n" .
	 "       </script>\n" .
	 "      </form>\n" .
	 "     </center>\n" .
	 "    </div> <!-- fin : zoneMilieuComplet -->\n" .
	 $PageHTML->construireFooter() .
	 $PageHTML->piedPageHTML() );

	break;


 // Contrôle les éléments d'authentification.
 case 'CNX':
	if ( $_POST[ 'User' ] == '' and $_POST[ 'Password' ] == '' ) {
		print( $PageHTML->returnPage( $L_Title, $L_ERR_Mandatories_Fields, $Script ) );
		exit();;
	}
	
	switch ( strtoupper( $PageHTML->getParameter( 'authentication_type' ) ) ) {
	 default:
		$Authentication_Type = 'database';
		break;
		
	 case 'R':
		$Authentication_Type = 'radius';
		break;
		
	 case 'L':
		$Authentication_Type = 'ldap';
		break;
	}

	try {
		// Récupère le "salt" spécifique de l'utilisateur.
		if ( ! ($Salt = $PageHTML->getSalt( $_POST[ 'User' ] )) ) {
			$alert_message = $Secrets->formatHistoryMessage( $L_Err_Auth . ' (' .
			 $_POST[ 'User' ] . ') [' . $Authentication_Type . ']' );

			$Secrets->updateHistory( '', 0, $alert_message, $IP_Source );
			
			print( $PageHTML->returnPage( $L_Title, $L_Err_Auth, $Script ) );

			exit();
		}
		
		// Contrôle l'authentication à partir des éléments fournis.
		$PageHTML->authentication( $_POST[ 'User' ], $_POST[ 'Password' ], $Authentication_Type, $Salt );
	} catch( Exception $e ) {
		// Si problème d'authentification et que l'utilisateur existe alors incrémentation du nombre de tentative de connexion.
		$PageHTML->addAttempt( $_POST[ 'User' ] );
			
		$alert_message = $Secrets->formatHistoryMessage( $e->getMessage() . ' (' .
		 $_POST[ 'User' ] . ')' );

		$Secrets->updateHistory( '', 0, $alert_message, $IP_Source );
			
		print( $PageHTML->returnPage( $L_Title, $e->getMessage(), $Script ) );

		exit();
	}

	// Si l'indicateur de changement de mot de passe est à "vrai". L'utilisateur doit changer son mot de passe.
	if ( $_SESSION[ 'idn_change_authenticator' ] == 1 ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_Change_Password . ' ' .
		 $_SESSION[ 'cvl_first_name' ] . ' ' . $_SESSION[ 'cvl_last_name' ] .
		 ' (' . $_SESSION[ 'idn_login' ] . ')' );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		header( 'Location: ' . $Script . '?action=CMDP&mandatory' );
		
		break;
	}

	// Tout est normal, l'utilisateur arrive sur son tableau de bord.
	$alert_message = $Secrets->formatHistoryMessage( $L_Connection . ' ' .
	 $_SESSION[ 'cvl_first_name' ] . ' ' . $_SESSION[ 'cvl_last_name' ] .
	 ' (' . $_SESSION[ 'idn_login' ] . ')' );

	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
	header( 'Location: ' . URL_BASE . '/SM-home.php?last_login' );
   
	break;
}
?>
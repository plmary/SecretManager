<?php

/**
* Ce script gère les paramètres internes à l'application.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.2
* @date 2012-11-19
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
$Logout_button = 1;
$Javascript = array( 'SecretManager.js', 'Ajax_preferences.js' );

include( DIR_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );

$Authentication = new IICA_Authentications();

if ( ! $Authentication->is_connect() ) {
   header( 'Location: SM-login.php' );
	exit();
}

// Charge les libellés.
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

// Charge les objets
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
		

$PageHTML = new HTML();

$Security = new Security();

$Secrets = new IICA_Secrets();


$F_Verbosity_Alert = $PageHTML->getParameter( 'verbosity_alert' );


if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}


if ( array_key_exists( 'Expired', $_SESSION ) ) {
	// Contrôle si la session n'a pas expirée.
	if ( ! $Authentication->validTimeSession() ) {
		header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX&expired' );
	} else {
		$Authentication->saveTimeSession();
	}
} else {
	header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX' );
}


if ( ! preg_match("/X$/i", $Action ) ) {
    print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $Javascript ) .
     "   <!-- debut : zoneTitre -->\n" .
     "   <div id=\"zoneTitre\">\n" .
     "    <div id=\"icon-options\" class=\"icon36\"></div>\n" .
     "    <span id=\"titre\">" . $L_Title . "</span>\n" .
     $PageHTML->afficherActions( $Authentication->is_administrator() ) .
     "   </div> <!-- fin : zoneTitre -->\n" .
     "\n" .
     "\n" .
     "   <!-- debut : zoneMilieuComplet -->\n" .
     "   <div id=\"zoneMilieuComplet\">\n" .
     "\n" );

    if ( isset( $_POST[ 'iMessage']) ) {
        print( "<script>\n" .
         "     var myVar=setInterval(function(){cacherInfo()},3000);\n" .
         "     function cacherInfo() {\n" .
         "        document.getElementById(\"success\").style.display = \"none\";\n" .
         "        clearInterval(myVar);\n" .
         "     }\n" .
         "</script>\n" .
         "    <div id=\"success\">\n" .
         stripslashes( $_POST[ 'iMessage' ] ) .
         "    </div>\n" );
    }

    print( "    <!-- debut : dashboard -->\n" .
     "    <div id=\"dashboard\">\n" );
}

if ( $Authentication->is_administrator() ) {
    if ( ! preg_match("/X$/i", $Action ) ) {
        print( "     <style type=\"text/css\" media=\"all\">\n" .
         "      @import url(Libraries/tabs.css);\n" .
         "     </style>\n" .
         "     <!-- debut : tabs -->\n" .
         "     <div id=\"tabs\">\n" .
         "      <ul>\n" );

        switch( $Action ) {
         default:
            print( "       <li class=\"active\">" . $L_Welcome . "</li>\n" .
             "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection .
             "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
             "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=SCR\">" . $L_Secrets .
             "</a></li>\n"
            );
            break;

         case 'A':
         case 'AX':
            print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
             "       <li class=\"active\">" . $L_Alerts . "</li>\n" .
             "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection .
             "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
             "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=SCR\">" . $L_Secrets .
             "</a></li>\n"
            );
            break;

         case 'C':
         case 'CX':
            print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
             "       <li class=\"active\">" . $L_Connection . "</li>\n" .
             "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
             "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=SCR\">" . $L_Secrets .
             "</a></li>\n"
            );
            break;

         case 'S':
         case 'LK':
            print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection . "</a></li>\n" .
             "       <li class=\"active\">" . $L_SecretServer . "</li>\n" .
             "       <li><a href=\"" . $Script . "?action=SCR\">" . $L_Secrets .
             "</a></li>\n"
            );
            break;

         case 'SCR':
         case 'SCRX':
            print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection . "</a></li>\n" .
             "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
             "</a></li>\n" .
             "       <li class=\"active\">" . $L_Secrets . "</li>\n"
            );
            break;
        }

        print( "      </ul>\n" .
         "      <!-- debut : pagelet -->\n" .
         "      <div class=\"pagelet\">\n" );
    }

	switch( $Action ) {
	 default:
		print( $L_Welcome_Text . "\n" );
		break;


	 // ====================
	 // Gestion des Alertes
	 case 'A':
		print(
		 "     <form method=\"post\" name=\"alert_form\" action=\"" . $Script . "?action=AX\">\n" .
		 "      <table class=\"table-bordered table-center\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Alert_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" . $L_Language_Alerts .
		 "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"language_alert\">\n" );

		$FR_Selected = '' ;
		$EN_Selected = '';
		$DE_Selected = '';

		switch ( $PageHTML->getParameter( 'language_alert' ) ) {
		 default:
		 case 'fr':
			$FR_Selected = ' selected ';
			break;

		 case 'en':
			$EN_Selected = ' selected ' ;
			break;
			
		 case 'de':
			$DE_Selected = ' selected ' ;
			break;
		}
			
		print( "          <option value=\"fr\"" . $FR_Selected . ">" . 
		 $L_Langue_fr . "</option>\n" .
		 "          <option value=\"en\"" . $EN_Selected . ">" . 
		 $L_Langue_en . "</option>\n" .
		 "          <option value=\"de\"" . $DE_Selected . ">" . 
		 $L_Langue_de . "</option>\n" .
		 "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" . $L_Verbosity_Alert .
		 "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"verbosity_alert\">\n" );

		$Detailed_Selected = '' ;
		$Normal_Selected = '';

		switch ( $PageHTML->getParameter( 'verbosity_alert' ) ) {
		 case '2':
			$Detailed_Selected = ' selected ' ;
			break;
			
		 default:
		 case '1':
			$Normal_Selected = ' selected ';
			break;
		}
			
		print( "          <option value=\"1\"" . $Normal_Selected . ">" . 
		 $L_Normal_Verbosity . "</option>\n" .
		 "          <option value=\"2\"" . $Detailed_Selected . ">" . 
		 $L_Detailed_Verbosity . "</option>\n" );

		print( "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" . $L_Alert_Syslog .
		 "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"alert_syslog\">\n" );

		$Selected = '';

		if ( $PageHTML->getParameter( 'alert_syslog' ) == '1' )
			$Selected = ' selected ' ;
			
		print( "          <option value=\"0\">" . $L_No . "</option>\n" .
		 "          <option value=\"1\"" . $Selected . ">" . $L_Yes . "</option>\n" );

		print( "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\">" . $L_Alert_Mail . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"alert_mail\" onChange=\"javascript:activeMailFields();\">\n" );

		$Selected = '';

		if ( $PageHTML->getParameter( 'alert_mail' ) == '1' )
			$Selected = ' selected ' ;
			
		print( "          <option value=\"0\">" . $L_No . "</option>\n" .
		 "          <option value=\"1\"" . $Selected . ">" . $L_Yes . "</option>\n" );

		print( "         </select>\n" .
		 "         <table class=\"table-bordered\">\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_From . "</td>\n" .
		 "           <td><input type=\"text\" size=\"30\" name=\"mail_from\" value=\"".
		  $PageHTML->getParameter( 'mail_from' ) . "\" title=\"" . $L_Mail_From . 
		  "\" /></td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_To . "</td>\n" .
		 "           <td><textarea name=\"mail_to\" title=\"" . $L_Mail_To . "\">".
		  $PageHTML->getParameter( 'mail_to' ) . "</textarea></td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_Title_1 . "</td>\n" .
		 "           <td><input name=\"mail_title\" title=\"" . $L_Mail_Title . "\" " .
		 "value=\"" . $PageHTML->getParameter( 'mail_title' ) . "\" /></td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_Body_Type . "</td>\n" .
		 "           <td><select name=\"mail_body_type\">\n" );

		$TXT_Selected = '';
		$HTML_Selected = '';

		if ( $PageHTML->getParameter( 'mail_body_type' ) == 'TXT' 
			or $PageHTML->getParameter( 'mail_body_type' ) == '') $TXT_Selected = ' selected ' ;
		else $HTML_Selected = ' selected ' ;
			
		print( "          <option value=\"TXT\"" . $TXT_Selected . ">TXT</option>\n" .
		 "          <option value=\"HTML\"" . $HTML_Selected . ">HTML</option>\n" );

		print( "         </select></td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_Body . "</td>\n" .
		 "           <td><textarea name=\"mail_body\" title=\"" . $L_Mail_Body . "\">".
		  file_get_contents ( MAIL_BODY ) . "</textarea></td>\n" .
		 "          </tr>\n" .
		 "         </table>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Save .
		 "\" /></td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n" .
		 "     <script>\n" .
		 "function activeMailFields() {\n" .
		 " if ( document.alert_form.alert_mail.value != 1 ) {\n" .
		 "  document.alert_form.mail_from.disabled = 1;\n" .
		 "  document.alert_form.mail_to.disabled = 1;\n" .
		 "  document.alert_form.mail_title.disabled = 1;\n" .
		 "  document.alert_form.mail_body_type.disabled = 1;\n" .
		 "  document.alert_form.mail_body.disabled = 1;\n" .
		 " } else {\n" .
		 "  document.alert_form.mail_from.disabled = 0;\n" .
		 "  document.alert_form.mail_to.disabled = 0;\n" .
		 "  document.alert_form.mail_title.disabled = 0;\n" .
		 "  document.alert_form.mail_body_type.disabled = 0;\n" .
		 "  document.alert_form.mail_body.disabled = 0;\n" .
		 " }\n" .
		 "}\n" .
		 "activeMailFields();\n" .
		 "     </script>\n"
		);
		break;

	 case 'AX':
		if ( ($Language_Alert = $Security->valueControl( $_POST[ 'language_alert' ] )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (language_alert)</h1>" );
			break;
		}

		if ( ($Alert_Syslog = $Security->valueControl( $_POST[ 'alert_syslog' ],
		 'NUMERIC' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (alert_syslog)</h1>" );
			break;
		}

		if ( ($Verbosity_Alert = $Security->valueControl( $_POST[ 'verbosity_alert' ],
		 'NUMERIC' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (verbosity_alert)</h1>" );
			break;
		}

		if ( ($Alert_Mail = $Security->valueControl( $_POST[ 'alert_mail' ],
		 'NUMERIC' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (alert_mail)</h1>" );
			break;
		}

		if ( $Alert_Mail ) {
			if ( ($Mail_From = $Security->valueControl( $_POST[ 'mail_from' ] )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (mail_from)</h1>" );
				break;
			}

			if ( ($Mail_To = $Security->valueControl( $_POST[ 'mail_to' ] )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (mail_to)</h1>" );
				break;
			}

			if ( ($Mail_Title = $Security->valueControl( $_POST[ 'mail_title' ] )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (mail_title)</h1>" );
				break;
			}

			if ( ($Mail_Body = $Security->valueControl( $_POST[ 'mail_body' ] )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (mail_body)</h1>" );
				break;
			}

			if ( ($Mail_Body_Type = $Security->valueControl( $_POST[ 'mail_body_type' ] )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (mail_body_type)</h1>" );
				break;
			}
		}

		try {
			$PageHTML->setParameter( 'language_alert', $Language_Alert );
			$PageHTML->setParameter( 'verbosity_alert', $Verbosity_Alert );
			$PageHTML->setParameter( 'alert_syslog', $Alert_Syslog );
			$PageHTML->setParameter( 'alert_mail', $Alert_Mail );
			if ( $Alert_Mail ) {
				$PageHTML->setParameter( 'mail_from', $Mail_From );
				$PageHTML->setParameter( 'mail_to', $Mail_To );
				$PageHTML->setParameter( 'mail_title', $Mail_Title );
				$PageHTML->setParameter( 'mail_body_type', strtoupper( $Mail_Body_Type ) );
				file_put_contents( MAIL_BODY, $Mail_Body );
			}
		} catch( PDOException $e ) {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_MAJ_Alert, $Script .
			 "?action=P&id=" . $scr_id, 1 ) );
			exit();
		}

		$alert_message = $PageHTML->getTextCode( 'L_Parameters_Updated', $PageHTML->getParameter( 'language_alert' ) );

		if ( $F_Verbosity_Alert == 1 ) {
			$alert_message .= ' (language_alert, verbosity_alert, alert_syslog, alert_mail, mail_from, mail_to, mail_title, mail_body, mail_body_type)';
		} else {
			$alert_message .= ' (language_alert="' . $Language_Alert . '", verbosity_alert="' . $Verbosity_Alert . '", alert_syslog="' . $Alert_Syslog .
			'", alert_mail="' . $Alert_Mail . '"';

			if ( $Alert_Mail ) {
				$alert_message .= ', mail_from="' . $Mail_From . '", mail_to="' . $Mail_To . '", mail_title="' . $Mail_Title . 
					'", mail_body="...", mail_body_type="' . $Mail_Body_Type . '"';
			}

			$alert_message .= ')';
		}

		$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Script . "?action=A\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Parameters_Updated . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );

		break;


	 // ================================================
	 // Gestion des modes d'authentification des utilisateurs
	 case 'C':
	 	if ( file_exists( DIR_LIBRARIES . '/Config_Authentication.inc.php' ) ) {
			include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
		}
		
	 	if ( file_exists( DIR_LIBRARIES . '/Config_Radius.inc.php' ) ) {
			include( DIR_LIBRARIES . '/Config_Radius.inc.php' );
		}
		
	 	if ( file_exists( DIR_LIBRARIES . '/Config_LDAP.inc.php' ) ) {
			include( DIR_LIBRARIES . '/Config_LDAP.inc.php' );
		}
		
		if ( ! isset( $_LDAP_Port ) ) $_LDAP_Port = 389;
	 	
		switch( $PageHTML->getParameter( 'authentication_type' ) ) {
		 case 'D':
			$Password_Selected = 'checked ';
			$Radius_Selected = '';
			$LDAP_Selected = '';
			break;

		 case 'R':
			$Password_Selected = '';
			$Radius_Selected = 'checked ';
			$LDAP_Selected = '';
			break;
			
		 case 'L':
			$Password_Selected = '';
			$Radius_Selected = '';
			$LDAP_Selected = 'checked ';
			break;
		}

		print( "     <script>\n" .
		 "function activeFields( authentification_type ) {" .
		 " if ( authentification_type == 'D' ) {\n" .
		 "  document.getElementById('id_Min_Size_Password').disabled=false;\n" .
		 "  document.getElementById('id_Password_Complexity').disabled=false;\n" .
		 "  document.getElementById('id_Default_User_Lifetime').disabled=false;\n" .
		 "  document.getElementById('id_Max_Attempt').disabled=false;\n" .
		 "  document.getElementById('id_Default_Password').disabled=false;\n" .

		 "  document.getElementById('id_Radius_Server').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Authentication_Port').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Accounting_Port').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Secret').disabled=true;\n" .

		 "  document.getElementById('id_LDAP_Server').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Port').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Protocol_Version').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Organization').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_RDN_Prefix').disabled=true;\n" .
		 " }\n" .
		 " else if ( authentification_type == 'R' ) {\n" .
		 "  document.getElementById('id_Min_Size_Password').disabled=true;\n" .
		 "  document.getElementById('id_Password_Complexity').disabled=true;\n" .
		 "  document.getElementById('id_Default_User_Lifetime').disabled=true;\n" .
		 "  document.getElementById('id_Max_Attempt').disabled=true;\n" .
		 "  document.getElementById('id_Default_Password').disabled=true;\n" .

		 "  document.getElementById('id_Radius_Server').disabled=false;\n" .
		 "  document.getElementById('id_Radius_Authentication_Port').disabled=false;\n" .
		 "  document.getElementById('id_Radius_Accounting_Port').disabled=false;\n" .
		 "  document.getElementById('id_Radius_Secret').disabled=false;\n" .

		 "  document.getElementById('id_LDAP_Server').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Port').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Protocol_Version').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_Organization').disabled=true;\n" .
		 "  document.getElementById('id_LDAP_RDN_Prefix').disabled=true;\n" .
		 " }\n" .
		 " else if ( authentification_type == 'L' ) {\n" .
		 "  document.getElementById('id_Min_Size_Password').disabled=true;\n" .
		 "  document.getElementById('id_Password_Complexity').disabled=true;\n" .
		 "  document.getElementById('id_Default_User_Lifetime').disabled=true;\n" .
		 "  document.getElementById('id_Max_Attempt').disabled=true;\n" .
		 "  document.getElementById('id_Default_Password').disabled=true;\n" .

		 "  document.getElementById('id_Radius_Server').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Authentication_Port').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Accounting_Port').disabled=true;\n" .
		 "  document.getElementById('id_Radius_Secret').disabled=true;\n" .

		 "  document.getElementById('id_LDAP_Server').disabled=false;\n" .
		 "  document.getElementById('id_LDAP_Port').disabled=false;\n" .
		 "  document.getElementById('id_LDAP_Protocol_Version').disabled=false;\n" .
		 "  document.getElementById('id_LDAP_Organization').disabled=false;\n" .
		 "  document.getElementById('id_LDAP_RDN_Prefix').disabled=false;\n" .
		 " }\n" .
		 "}" .
		 "     </script>\n" .
		 "     <form method=\"post\" name=\"PageHTML\" action=\"" . $Script . "?action=CX\">\n" .
		 "      <table class=\"table-bordered\" style=\"margin:10px auto;width:90%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Connection_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" .
		 "<label for=\"id_expiration\">" . $L_Expiration_Time . "</label></td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "         <input id=\"id_expiration\" type=\"text\" name=\"Expiration_Time\" value=\"" .
		 $PageHTML->getParameter( 'expiration_time' ) . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" rowspan=\"6\">" .
		 "<label for=\"id_pwd\">" . $L_Use_Password . "</label>" .
		 "</td>\n" .
		 "        <td class=\"pair\"  colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"D\" name=\"authentication_type\" " .
		 $Password_Selected . " id=\"id_pwd\" onClick=\"activeFields('D');\" />&nbsp;" .
		 "<a class=\"button\" href=\"javascript:prepareTestConnection('" . addslashes($L_Testing_Connection) . "', '" . addslashes($L_Use_Password) . "', '" .
		 addslashes($L_Username) . "', '" . addslashes($L_Password) . "', '" . addslashes($L_Connection) . "', '" . $L_Cancel . "', 'D');\">" . $L_Testing_Connection . "</a>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td width=\"30%\">" . $L_Min_Size_Password . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Min_Size_Password\" " .
		 "name=\"Min_Size_Password\" value=\"" . $_Min_Size_Password . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Password_Complexity . "</td>\n" .
		 "        <td>\n" .
		 "         <select id=\"id_Password_Complexity\" name=\"Password_Complexity\" class=\"input-xxlarge\">\n" );
		
		$Active_1 = '';
		$Active_2 = '';
		$Active_3 = '';
		$Active_4 = '';
		
		switch( $_Password_Complexity ) {
		 case 1:
			$Active_1 = ' selected';
			break;

		 case 2:
			$Active_2 = ' selected';
			break;

		 case 3:
			$Active_3 = ' selected';
			break;

		 case 4:
			$Active_4 = ' selected';
			break;
		}
		
		if ( ! isset( $_Radius_Authentication_Port ) ) {
			$_Radius_Authentication_Port = 1812;
		}
		
		if ( ! isset( $_Radius_Accounting_Port ) ) {
			$_Radius_Accounting_Port = 1813;
		}
		
		print(
		 "          <option value=\"1\"" . $Active_1 . ">" . $_Password_Complexity_1 .
		 "</option>\n" .
		 "          <option value=\"2\"" . $Active_2 . ">" . $_Password_Complexity_2 .
		 "</option>\n" .
		 "          <option value=\"3\"" . $Active_3 . ">" . $_Password_Complexity_3 .
		 "</option>\n" .
		 "          <option value=\"4\"" . $Active_4 . ">" . $_Password_Complexity_4 .
		 "</option>\n" .
		 "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Default_User_Lifetime . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Default_User_Lifetime\" " .
		 "name=\"Default_User_Lifetime\" value=\"" . $_Default_User_Lifetime . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Max_Attempt . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Max_Attempt\" " .
		 "name=\"Max_Attempt\" value=\"" . $_Max_Attempt . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Default_Password . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Default_Password\" " .
		 "name=\"Default_Password\"  value=\"" . $_Default_Password . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\" rowspan=\"5\">" .
		 "<label for=\"id_rds\">" . $L_Use_Radius . "</label>" .
		 "</td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"R\" name=\"authentication_type\" " .
		 $Radius_Selected . " id=\"id_rds\" onClick=\"activeFields('R');\" />&nbsp;" .
		 "<a class=\"button\" href=\"javascript:prepareTestConnection('" . addslashes($L_Testing_Connection) . "', '" . addslashes($L_Use_Radius) . "', '" .
		 addslashes($L_Username) . "', '" . addslashes($L_Password) . "', '" . addslashes($L_Connection) . "', '" . $L_Cancel . "', 'R');\">" .
		 addslashes($L_Testing_Connection) . "</a>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Radius_Server . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Radius_Server\" name=\"Radius_Server\" " .
		 "value=\"" . $_Radius_Server . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Radius_Authentication_Port . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Radius_Authentication_Port\" " .
		 "name=\"Radius_Authentication_Port\" " .
		 "value=\"" . $_Radius_Authentication_Port . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Radius_Accounting_Port . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Radius_Accounting_Port\" " .
		 "name=\"Radius_Accounting_Port\" " .
		 "value=\"" . $_Radius_Accounting_Port . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Radius_Secret_Common . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_Radius_Secret\" name=\"Radius_Secret\" " .
		 "value=\"" . $_Radius_Secret_Common . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\" rowspan=\"6\">" .
		 "<label for=\"id_rds\">" . $L_Use_LDAP . "</label>" .
		 "</td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"L\" name=\"authentication_type\" " .
		 $LDAP_Selected . " id=\"id_rds\" onClick=\"activeFields('L');\" />&nbsp;" .
		 "<a class=\"button\" href=\"javascript:prepareTestConnection('" . addslashes($L_Testing_Connection) . "', '" . addslashes($L_Use_LDAP) . "', '" .
		 addslashes($L_Username) . "', '" . addslashes($L_Password) . "', '" . addslashes($L_Connection) . "', '" . $L_Cancel . "', 'L');\">" . $L_Testing_Connection . "</a>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_LDAP_Server . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_LDAP_Server\" name=\"LDAP_Server\" " .
		 "value=\"" . $_LDAP_Server . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_LDAP_Port . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_LDAP_Port\" name=\"LDAP_Port\" " .
		 "value=\"" . $_LDAP_Port . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_LDAP_Protocol_Version . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_LDAP_Protocol_Version\" " .
		 "name=\"LDAP_Protocol_Version\" value=\"" . $_LDAP_Protocol_Version . "\"/>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_LDAP_Organization . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_LDAP_Organization\" name=\"LDAP_Organization\" " .
		 "value=\"" . $_LDAP_Organization . "\" />&nbsp;(Ex1: uid=user,<b>dc=orasys,dc=fr</b>)<br/>(Ex2: uid=user,<b>o=orasys,ou=production</b>)\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_LDAP_RDN_Prefix . "</td>\n" .
		 "        <td>\n" .
		 "         <input type=\"text\" id=\"id_LDAP_RDN_Prefix\" name=\"LDAP_RDN_Prefix\" " .
		 "value=\"" . $_LDAP_RDN_Prefix . "\" />&nbsp;(Ex1: <b>sn</b>=user,dc=orasys,dc=fr)<br/>(Ex2: <b>uid</b>=user,dc=orasys,dc=fr)\n" .
		 "        </td>\n" .
		 "       </tr>\n" .

		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Save .
		 "\" /></td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n" .
		 "     <script>\n" .
		 "activeFields('" . $PageHTML->getParameter( 'authentication_type' ) . "');\n" .
		 "     </script>\n"
		);
		break;

	 case 'CX':
		if ( ($authentication_type = $Security->valueControl( 
		 $_POST[ 'authentication_type' ], 'ALPHA' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (authentication_type)</h1>" );
			break;
		}

		switch( $authentication_type ) {
		 case 'D':
			if ( ($Min_Size_Password = $Security->valueControl( 
			 $_POST[ 'Min_Size_Password' ], 'NUMERIC' )) == -1
			 and $_POST[ 'Min_Size_Password' ] != '' ) {
				print( "     <h1>" . $L_Invalid_Value . " (Min_Size_Password)</h1>" );
				break 2;
			}

			if ( ($Password_Complexity = $Security->valueControl( 
			 $_POST[ 'Password_Complexity' ], 'NUMERIC' )) == -1
			 and $_POST[ 'Password_Complexity' ] != '' ) {
				print( "     <h1>" . $L_Invalid_Value . " (Password_Complexity)</h1>" );
				break 2;
			}

			if ( ($Default_User_Lifetime = $Security->valueControl( 
			 $_POST[ 'Default_User_Lifetime' ], 'NUMERIC' )) == -1
			 and $_POST[ 'Default_User_Lifetime' ] != '' ) {
				print( "     <h1>" . $L_Invalid_Value . " (Default_User_Lifetime)</h1>" );
				break 2;
			}

			if ( ($Max_Attempt = $Security->valueControl( 
			 $_POST[ 'Max_Attempt' ], 'NUMERIC' )) == -1
			 and $_POST[ 'Max_Attempt' ] != '' ) {
				print( "     <h1>" . $L_Invalid_Value . " (Max_Attempt)</h1>" );
				break 2;
			}

			if ( ! ($Default_Password = $Security->valueControl( 
			 $_POST[ 'Default_Password' ], 'ASCII' ))
			 and $_POST[ 'Default_Password' ] != '' ) {
				print( "     <h1>" . $L_Invalid_Value . " (Default_Password)</h1>" );
				break 2;
			}
			
			break;
		
		 case 'R':
			if ( ($Radius_Server = $Security->valueControl( 
			 $_POST[ 'Radius_Server' ], 'ASCII' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Radius_Server)</h1>" );
				break 2;
			}

			if ( ($Radius_Authentication_Port = $Security->valueControl( 
			 $_POST[ 'Radius_Authentication_Port' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Radius_Authentication_Port)</h1>" );
				break 2;
			}

			if ( ($Radius_Accounting_Port = $Security->valueControl( 
			 $_POST[ 'Radius_Accounting_Port' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Radius_Accounting_Port)</h1>" );
				break 2;
			}

			if ( ($Radius_Secret = $Security->valueControl( 
			 $_POST[ 'Radius_Secret' ], 'ASCII' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Radius_Secret)</h1>" );
				break 2;
			}
			
			break;

		 case 'L':
			if ( ($LDAP_Server = $Security->valueControl( 
			 $_POST[ 'LDAP_Server' ], 'ASCII' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (LDAP_Server)</h1>" );
				break 2;
			}

			if ( ($LDAP_Port = $Security->valueControl( 
			 $_POST[ 'LDAP_Port' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (LDAP_Port)</h1>" );
				break 2;
			}

			if ( ($LDAP_Protocol_Version = $Security->valueControl( 
			 $_POST[ 'LDAP_Protocol_Version' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (LDAP_Protocol_Version)</h1>" );
				break 2;
			}

			if ( ($LDAP_Organization = $Security->valueControl( 
			 $_POST[ 'LDAP_Organization' ], 'ASCII' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (LDAP_Organization)</h1>" );
				break 2;
			}

			if ( ($LDAP_RDN_Prefix = $Security->valueControl( 
			 $_POST[ 'LDAP_RDN_Prefix' ], 'ASCII' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (LDAP_RDN_Prefix)</h1>" );
				break 2;
			}
			
			break;
		}

		if ( ($Expiration_Time = $Security->valueControl( 
		 $_POST[ 'Expiration_Time' ], 'NUMERIC' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (Expiration_Time)</h1>" );
			break;
		}

		try {
			$PageHTML->setParameter( 'authentication_type', $authentication_type );
			$PageHTML->setParameter( 'expiration_time', $_POST[ 'Expiration_Time' ] );

			$alert_message = $PageHTML->getTextCode( 'L_Parameters_Updated', $PageHTML->getParameter( 'language_alert' ) );

			if ( $F_Verbosity_Alert == 1 ) {
				$alert_message .= ' (authentication_type, expiration_time)';
			} else {
				$alert_message .= ' (authentication_type="' . $authentication_type . '", expiration_time="' . $_POST[ 'Expiration_Time' ] . '")';
			}

			$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );
		} catch( PDOException $e ) {
			print( "   <div id=\"alert\">\n" .
			 $L_ERR_MAJ_Connection .
			 "      <a class=\"button\" href=\"" . $Script . "?action=P&id=" . $scr_id . "\">" .
			 $L_Return . "</a>\n" .
			 "   </div>\n" );
			break;
		}


		switch( $authentication_type ) {
		 case 'D':
			$Output = @fopen( DIR_LIBRARIES . '/Config_Authentication.inc.php', 'w+' );
			if ( fwrite( $Output,
"<?php\n" .
"\n" .
"/**\n" .
"* Définit les variables permettant de gérer les authentifications en base locale.\n" .
"*\n" .
"* PHP version 5\n" .
"* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3\n" .
"* @author Pierre-Luc MARY\n" .
"* @version 1.0\n" .
"* @Modified " . date( 'd/m/Y' ) . "\n" .
"*\n" .
"*/\n" .
"\n" .
"\$_Min_Size_Password = " . $Min_Size_Password . "; // Size in characters\n" .
"\$_Password_Complexity = " . $Password_Complexity . "; // lowercase, uppercase, numbers and specials\n" .
"\$_Default_User_Lifetime = " . $Default_User_Lifetime . "; // Lifetime in month\n" .
"\$_Max_Attempt = " . $Max_Attempt . "; // 0 = infinite\n" .
"\$_Default_Password = '" . $Default_Password . "';\n" .
"\n" .
"?>\n" ) === false ) {
				print( "   <div id=\"alert\">\n" .
				 $L_ERR_MAJ_Connection .
				 "      <a class=\"button\" href=\"" . $Script . "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}
		
			fclose( $Output );
			
			$Labels = $PageHTML->getTextCode( array( 'L_Parameters_Updated', 'L_Use_Password' ), $PageHTML->getParameter( 'language_alert' ) );
			$alert_message = $Labels['L_Parameters_Updated'] . ' [' . $Labels['L_Use_Password'] . ']';

			if ( $F_Verbosity_Alert == 1 ) {
				$alert_message .= ' (Min_Size_Password, Password_Complexity, Default_User_Lifetime, Max_Attempt, Default_Password)';
			} else {
				$alert_message .= ' (Min_Size_Password="' . $Min_Size_Password . '", Password_Complexity="' . $Password_Complexity . 
					'", Default_User_Lifetime="' . $Default_User_Lifetime . '", Max_Attempt="' . $Max_Attempt . 
					'", Default_Password="' . $Default_Password . '")';
			}

			$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

			break;
			
		 case 'R':
			$Output = @fopen( DIR_LIBRARIES . '/Config_Radius.inc.php', 'w+' );
			if ( fwrite( $Output,
"<?php\n" .
"\n" .
"/**\n" .
"* Définit les variables permettant de gérer les authentifications Radius.\n" .
"*\n" .
"* PHP version 5\n" .
"* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3\n" .
"* @author Pierre-Luc MARY\n" .
"* @version 1.0\n" .
"* @Modified " . date( 'd/m/Y' ) . "\n" .
"*\n" .
"*/\n" .
"\n" .
"\$_Radius_Server = '" . $Radius_Server . "'; // IP address or server name\n" .
"\$_Radius_Authentication_Port = '" . $Radius_Authentication_Port . "';\n" .
"\$_Radius_Accounting_Port = '" . $Radius_Accounting_Port . "';\n" .
"\$_Radius_Secret_Common = '" . $Radius_Secret . "'; // Shared secret\n" .
"\n" .
"?>\n" ) === false ) {
				print( "   <div id=\"alert\">\n" .
				 $L_ERR_MAJ_Connection .
				 "      <a class=\"button\" href=\"" . $Script . "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}
		
			fclose( $Output );
			
			$Labels = $PageHTML->getTextCode( array( 'L_Parameters_Updated', 'L_Use_Radius' ), $PageHTML->getParameter( 'language_alert' ) );
			$alert_message = $Labels['L_Parameters_Updated'] . ' [' . $Labels['L_Use_Radius'] . ']';

			if ( $F_Verbosity_Alert == 1 ) {
				$alert_message .= ' (Radius_Server, Radius_Authentication_Port, Default_User_Lifetime, Max_Attempt, Default_Password)';
			} else {
				$alert_message .= ' (Radius_Server="' . $Radius_Server . '", Radius_Authentication_Port="' . $Radius_Authentication_Port . 
					'", Radius_Accounting_Port="' . $Radius_Accounting_Port . '", Radius_Secret_Common="' . $Radius_Secret . '")';
			}

			$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

			break;

		 case 'L':
			$Output = @fopen( DIR_LIBRARIES . '/Config_LDAP.inc.php', 'w+' );
			if ( fwrite( $Output,
"<?php\n" .
"\n" .
"/**\n" .
"* Définit les variables permettant de gérer les authentifications Radius.\n" .
"*\n" .
"* PHP version 5\n" .
"* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3\n" .
"* @author Pierre-Luc MARY\n" .
"* @version 1.0\n" .
"* @Modified " . date( 'd/m/Y' ) . "\n" .
"*\n" .
"*/\n" .
"\n" .
"\$_LDAP_Server = '" . $LDAP_Server . "'; // IP address server or server name\n" .
"\$_LDAP_Port = '" . $LDAP_Port . "'; // IP port server\n" .
"\$_LDAP_Protocol_Version = '" . $LDAP_Protocol_Version . "'; // Protocol version\n" .
"\$_LDAP_Organization = '" . $LDAP_Organization . "'; // Organization tree\n" .
"\$_LDAP_RDN_Prefix = '" . $LDAP_RDN_Prefix . "'; // RDN prefix\n" .
"\n" .
"?>\n" ) === false ) {
				print( "   <div id=\"alert\">\n" .
				 $L_ERR_MAJ_Connection .
				 "      <a class=\"button\" href=\"" . $Script . "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}

			$Labels = $PageHTML->getTextCode( array( 'L_Parameters_Updated', 'L_Use_LDAP' ), $PageHTML->getParameter( 'language_alert' ) );
			$alert_message = $Labels['L_Parameters_Updated'] . ' [' . $Labels['L_Use_LDAP'] . ']';

			if ( $F_Verbosity_Alert == 1 ) {
				$alert_message .= ' (LDAP_Server, LDAP_Port, LDAP_Protocol_Version, Max_Attempt, Default_Password)';
			} else {
				$alert_message .= ' (LDAP_Server="' . $LDAP_Server . '", LDAP_Port="' . $LDAP_Port . '", LDAP_Protocol_Version="' . $LDAP_Protocol_Version .
					'", LDAP_Organization="' . $LDAP_Organization . '", LDAP_RDN_Prefix="' . $LDAP_RDN_Prefix . '")';
			}
		
			$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

			fclose( $Output );
			
			break;
		}

		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Script . "?action=C\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Parameters_Updated . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );

		break;


	 case 'S':
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		if ( $PageHTML->getParameter( 'use_SecretServer' ) == '1' ) {
			$Select_Yes = 'selected';
			$Select_No = '';
		} else {
			$Select_Yes = '';
			$Select_No = 'selected';
		}

		if ( $PageHTML->getParameter( 'stop_SecretServer_on_alert' ) == '1' ) {
			$Select_Stop_Yes = 'selected';
			$Select_Stop_No = '';
		} else {
			$Select_Stop_Yes = '';
			$Select_Stop_No = 'selected';
		}


        $Operator_Key_Size = $PageHTML->getParameter( 'Operator_Key_Size' );
        $Operator_Key_Complexity = $PageHTML->getParameter( 'Operator_Key_Complexity' );

        $Mother_Key_Size = $PageHTML->getParameter( 'Mother_Key_Size' );
        $Mother_Key_Complexity = $PageHTML->getParameter( 'Mother_Key_Complexity' );

		$Operator_Active_1 = '';
		$Operator_Active_2 = '';
		$Operator_Active_3 = '';
		$Operator_Active_4 = '';

		switch( $Operator_Key_Complexity ) {
		 case 1:
			$Operator_Active_1 = ' selected';
			break;

		 case 2:
			$Operator_Active_2 = ' selected';
			break;

         default:
		 case 3:
			$Operator_Active_3 = ' selected';
			break;

		 case 4:
			$Operator_Active_4 = ' selected';
			break;
		}


		$Mother_Active_1 = '';
		$Mother_Active_2 = '';
		$Mother_Active_3 = '';
		$Mother_Active_4 = '';
		
		switch( $Mother_Key_Complexity ) {
		 case 1:
			$Mother_Active_1 = ' selected';
			break;

		 case 2:
			$Mother_Active_2 = ' selected';
			break;

		 case 3:
			$Mother_Active_3 = ' selected';
			break;

		 case 4:
			$Mother_Active_4 = ' selected';
			break;
		}


		print(
		 "      <table class=\"table-bordered\" style=\"margin:10px auto;width:95%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_SecretServer_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\" width=\"30%\">" . $L_Use_SecretServer . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <table>\n" .
		 "          <tbody>\n" .
		 "          <tr>\n" .
		 "           <th width=\"40%\">" . $L_Use_SecretServer . "</th>\n" .
		 "           <td class=\"align-middle align-center pair\" width=\"30%\">\n" .
		 "            <select id=\"Use_SecretServer\">\n" .
		 "             <option value=\"0\" " . $Select_No . ">" .  $L_No . "</option>\n" .
		 "             <option value=\"1\" " . $Select_Yes . ">" .  $L_Yes . "</option>\n" .
		 "            </select>\n" .
		 "           </td>\n" .
		 "           <td rowspan=\"2\" class=\"align-middle\">\n" .
		 "            <a href=\"#\" class=\"button\" id=\"iSaveUseServer\">" . $L_Save . "</a>\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <th>" . $L_Stop_SecretServer_On_Alert . "</th>\n" .
		 "           <td class=\"align-middle align-center impair\">\n" .
		 "            <select id=\"Stop_SecretServer\">\n" .
		 "             <option value=\"0\" " . $Select_Stop_No . ">" .  $L_No . "</option>\n" .
		 "             <option value=\"1\" " . $Select_Stop_Yes . ">" .  $L_Yes . "</option>\n" .
		 "            </select>\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          </tbody>\n" .
		 "         </table>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\" width=\"30%\">" . $L_SecretServer_Keys . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <table>\n" .
		 "          <tr>\n" .
		 "           <th width=\"15%\">" . $L_Operator_Key . "</th>\n" .
		 "           <td width=\"25%\" class=\"impair\">" . $L_Min_Key_Size . "</td>\n" .
		 "           <td width=\"60%\" class=\"impair\">\n" .
		 "            <input type=\"text\" class=\"input-mini\" id=\"Operator_Key_Size\" value=\"" .
		 $Operator_Key_Size . "\" />\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <th>&nbsp;</th>\n" .
		 "           <td class=\"pair\">" . $L_Key_Complexity . "</td>\n" .
		 "           <td class=\"pair\">\n" .
		 "            <select class=\"input-xxlarge\" id=\"Operator_Key_Complexity\">\n" .
		 "             <option value=\"1\"" . $Operator_Active_1 . ">" . $_Password_Complexity_1 . "</option>\n" .
		 "             <option value=\"2\"" . $Operator_Active_2 . ">" . $_Password_Complexity_2 . "</option>\n" .
		 "             <option value=\"3\"" . $Operator_Active_3 . ">" . $_Password_Complexity_3 . "</option>\n" .
		 "             <option value=\"4\"" . $Operator_Active_4 . ">" . $_Password_Complexity_4 . "</option>\n" .
		 "            </select>\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <th>" . $L_Mother_Key . "</th>\n" .
		 "           <td class=\"impair\">" . $L_Min_Key_Size . "</td>\n" .
		 "           <td class=\"impair\">\n" .
		 "            <input type=\"text\" class=\"input-mini\" id=\"Mother_Key_Size\" value=\"" .
		 $Mother_Key_Size . "\" />\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <th>&nbsp;</th>\n" .
		 "           <td class=\"pair\">" . $L_Key_Complexity . "</td>\n" .
		 "           <td class=\"pair\">\n" .
		 "            <select class=\"input-xxlarge\" id=\"Mother_Key_Complexity\">\n" .
		 "             <option value=\"1\"" . $Mother_Active_1 . ">" . $_Password_Complexity_1 . "</option>\n" .
		 "             <option value=\"2\"" . $Mother_Active_2 . ">" . $_Password_Complexity_2 . "</option>\n" .
		 "             <option value=\"3\"" . $Mother_Active_3 . ">" . $_Password_Complexity_3 . "</option>\n" .
		 "             <option value=\"4\"" . $Mother_Active_4 . ">" . $_Password_Complexity_4 . "</option>\n" .
		 "            </select>\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <th>&nbsp;</th>\n" .
		 "           <td>&nbsp;</td>\n" .
		 "           <td>\n" .
		 "            <a href=\"#\" class=\"button\" id=\"iSaveKeysProperties\">" . $L_Save . "</a>\n" .
		 "           </td>\n" .
		 "          </tr>\n" .
		 "         </table>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" 
		);
		
		break;


	 case 'SUX':
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		try {
		    $PageHTML->setParameter( 'use_SecretServer', $_POST[ 'UseSecretServer' ] );
		    $PageHTML->setParameter( 'stop_SecretServer_on_alert', $_POST[ 'StopSecretServer' ] );
		} catch( PDOException $e ) {
		    $Ajax_Result = array(
                'Status' => 'error',
                'Message' => $e->getMessage()
            );
        
            echo json_encode( $Ajax_Result );
            exit();
		}

//		if ( $_POST[ 'UseSecretServer' ] == 0 ) $Value = $L_No;
//		else $Value = $L_Yes;
		

		$Labels = $PageHTML->getTextCode( 'L_Parameters_Updated', $PageHTML->getParameter( 'language_alert' ) );
		$alert_message = $Labels;

		if ( $F_Verbosity_Alert == 1 ) {
			$alert_message .= ' (UseSecretServer, StopSecretServer)';
		} else {
			$alert_message .= ' (UseSecretServer="' . $_POST[ 'UseSecretServer' ] . '",StopSecretServer="' . $_POST[ 'StopSecretServer' ] . '")';
		}
	
		$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

			
        $Ajax_Result = array(
            'Status' => 'success',
            'Message' => $L_Parameters_Updated
        );
        
        echo json_encode( $Ajax_Result );
        exit();
        
		break;


	 case 'SKX':
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		try {
		    $PageHTML->setParameter( 'Operator_Key_Size', $_POST[ 'Operator_Key_Size' ] );
		    $PageHTML->setParameter( 'Operator_Key_Complexity', $_POST[ 'Operator_Key_Complexity' ] );
		    $PageHTML->setParameter( 'Mother_Key_Size', $_POST[ 'Mother_Key_Size' ] );
		    $PageHTML->setParameter( 'Mother_Key_Complexity', $_POST[ 'Mother_Key_Complexity' ] );
		} catch( PDOException $e ) {
		    $Ajax_Result = array(
                'Status' => 'error',
                'Message' => $e->getMessage()
            );
        
            echo json_encode( $Ajax_Result );
            exit();
		}

		$Result = $L_Parameters_Updated;

		$Labels = $PageHTML->getTextCode( 'L_Parameters_Updated', $PageHTML->getParameter( 'language_alert' ) );
		$alert_message = $Labels;

		if ( $F_Verbosity_Alert == 1 ) {
			$alert_message .= ' (Operator_Key_Size, Operator_Key_Complexity, Mother_Key_Size, Mother_Key_Complexity)';
		} else {
			$alert_message .= ' (Operator_Key_Size="' . $_POST[ 'Operator_Key_Size' ] . '", Operator_Key_Complexity="' . $_POST[ 'Operator_Key_Complexity' ] .
				'", Mother_Key_Size="' . $_POST[ 'Mother_Key_Size' ] . '", Mother_Key_Complexity="' . $_POST[ 'Mother_Key_Complexity' ] . '")';
		}
	
		$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

			
        $Ajax_Result = array(
            'Status' => 'success',
            'Message' => $Result
        );
        
        echo json_encode( $Ajax_Result );
        exit();


	 case 'SCR':
		$Active_1 = '';
		$Active_2 = '';
		$Active_3 = '';
		$Active_4 = '';

		switch ( $PageHTML->getParameter( 'secrets_complexity' ) ) {
		 case '1':
			$Active_1 = ' selected';
			break;

		 case '2':
			$Active_2 = ' selected';
			break;

		 default:
		 case '3':
			$Active_3 = ' selected';
			break;

		 case '4':
			$Active_4 = ' selected';
			break;
		}

		print( "     <form method=\"post\" name=\"PageHTML\" action=\"" . $Script . "?action=SCRX\">\n" .
		 "      <table class=\"table-bordered\" style=\"margin:10px auto;width:90%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Secrets_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" .
		 "<label for=\"id_secrets_complexity\">" . $L_Secrets_Complexity . "</label></td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "            <select class=\"input-xxlarge\" id=\"id_secrets_complexity\" name=\"Secrets_Complexity\">\n" .
		 "             <option value=\"1\"" . $Active_1 . ">" . $_Password_Complexity_1 . "</option>\n" .
		 "             <option value=\"2\"" . $Active_2 . ">" . $_Password_Complexity_2 . "</option>\n" .
		 "             <option value=\"3\"" . $Active_3 . ">" . $_Password_Complexity_3 . "</option>\n" .
		 "             <option value=\"4\"" . $Active_4 . ">" . $_Password_Complexity_4 . "</option>\n" .
		 "            </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" .
		 "<label for=\"id_secrets_size\">" . $L_Secrets_Size . "</label></td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "         <input id=\"id_secrets_size\" type=\"text\" name=\"Secrets_Size\" class=\"input-small\" value=\"" .
		 $PageHTML->getParameter( 'secrets_size' ) . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"impair align-right\" width=\"50%\">" .
		 "<label for=\"id_secrets_size\">" . $L_Secrets_Lifetime . "</label></td>\n" .
		 "        <td class=\"pair\" colspan=\"2\">\n" .
		 "         <input id=\"id_secrets_lifetime\" type=\"text\" name=\"Secrets_Lifetime\" class=\"input-small\" value=\"" .
		 $PageHTML->getParameter( 'secrets_lifetime' ) . "\" />\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>" .
		 "        <td colspan=\"2\"><input type=\"submit\" class=\"button\" value=\"". $L_Save .
		 "\" /></td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n"
		);

		break;


	 case 'SCRX':
		if ( ($Secrets_Complexity = $Security->valueControl( $_POST[ 'Secrets_Complexity' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Secrets_Complexity)</h1>" );
				break;
		}
		
		if ( ($Secrets_Size = $Security->valueControl( $_POST[ 'Secrets_Size' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Secrets_Size)</h1>" );
				break;
		}

		if ( ($Secrets_Lifetime = $Security->valueControl( $_POST[ 'Secrets_Lifetime' ], 'NUMERIC' )) == -1 ) {
				print( "     <h1>" . $L_Invalid_Value . " (Secrets_Lifetime)</h1>" );
				break;
		}

		try {
			$PageHTML->setParameter( 'secrets_complexity', $Secrets_Complexity );
			$PageHTML->setParameter( 'secrets_size', $Secrets_Size );
			$PageHTML->setParameter( 'secrets_lifetime', $Secrets_Lifetime );
		} catch( Exception $e ) {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_MAJ_Alert, $Script .
			 "?action=SCR" ) );
			exit();
		}

		$alert_message = $PageHTML->getTextCode( 'L_Parameters_Updated', $PageHTML->getParameter( 'language_alert' ) );

		if ( $F_Verbosity_Alert == 1 ) {
			$alert_message .= ' (secrets_complexity, secrets_size, secrets_lifetime)';
		} else {
			$alert_message .= ' (secrets_complexity="' . $Secrets_Complexity . '", secrets_size="' . $Secrets_Size .
				'", secrets_lifetime="' . $Secrets_Lifetime . '")';
		}

		$Security->updateHistory( 'L_ALERT_SPR', $alert_message, 3, LOG_INFO );

		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Script . "?action=SCR\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Parameters_Updated . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );

		break;


	 case 'AJAX_CTRL_AUTH_X':
		$Result = '';
		$Status = 'success';
		$ConnectionTest = TRUE;

		// Test des variables reçues.
		if ( ($Login = $Security->valueControl( $_POST[ 'Login' ], 'ASCII' )) == -1 ) {
			if ( $Result != '' ) $Result .= ', ';
			$Result .= $L_Invalid_Value . " (Login)";
			$Status = 'error';
		}

		if ( ($Authenticator = $Security->valueControl( $_POST[ 'Authenticator' ], 'ASCII' )) == -1 ) {
			if ( $Result != '' ) $Result .= ', ';
			$Result .= $L_Invalid_Value . " (Authenticator)";
			$Status = 'error';
		}

		if ( ($ConnectionType = $Security->valueControl( $_POST[ 'ConnectionType' ], 'ASCII' )) == -1 ) {
			if ( $Result != '' ) $Result .= ', ';
			$Result .= $L_Invalid_Value . " (ConnectionType)";
			$Status = 'error';
		}

		if ( $Status == 'success' ) {
			$Result = $L_Success;

			// Contrôle l'ensemble de l'authentification avec les informations reçues.
			try {
				$Authentication->authentication( $Login, $Authenticator, $ConnectionType, '', $ConnectionTest );
			} catch( Exception $e ) {
				if ( $Result != '' ) $Result .= ', ';
				$Result = $e->getMessage();
				$Status = 'error';
			}
		}

			
        $Ajax_Result = array(
            'Status' => $Status,
            'Message' => $Result
        );
        
        echo json_encode( $Ajax_Result );

        exit();
	}
} else {
	print( "<h1>" . $L_No_Authorize . "</h1>" );
}

print( "      </div> <!-- fin : pagelet -->\n" .
 "     </div> <!-- fin : tabs -->\n" .
 "    </div> <!-- fin : dashboard -->\n" .
 "   </div> <!-- fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( $Logout_button ) .
 $PageHTML->piedPageHTML() );

?>
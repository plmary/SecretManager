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

session_start();

if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
	$_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}	

$Script = $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];
$IP_Source = $_SERVER[ 'REMOTE_ADDR' ];

if ( ! isset( $_SESSION[ 'idn_id' ] ) )
	header( 'Location: https://' . $Server . dirname( $Script ) . '/SM-login.php' );

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: https://' . $Server . $URI );

$Action = '';
$Choose_Language = 0;
$Logout_button = 1;

include( 'Libraries/Config_Access_DB.inc.php' );
include( 'Libraries/Class_IICA_Authentications_PDO.inc.php' );

$Authentication = new IICA_Authentications( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

if ( ! $Authentication->is_connect() ) {
   header( 'Location: SM-login.php' );
	exit();
}

// Charge les libellés.
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

// Charge les objets
include( 'Libraries/Class_HTML.inc.php' );
include( 'Libraries/Config_Hash.inc.php' );
include( 'Libraries/Class_IICA_Parameters_PDO.inc.php' );
include( 'Libraries/Class_Security.inc.php' );


$PageHTML = new HTML();

$Parameters = new IICA_Parameters( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

$Security = new Security();


if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}


if ( array_key_exists( 'Expired', $_SESSION ) ) {
	// Contrôle si la session n'a pas expirée.
	if ( ! $Authentication->validTimeSession() ) {
		header( 'Location: SM-login.php?action=DCNX&expired' );
	} else {
		$Authentication->saveTimeSession();
	}
} else {
	header( 'Location: SM-login.php?action=DCNX' );
}


print( $PageHTML->enteteHTML( $L_Title, $Choose_Language ) .
 "   <!-- debut : zoneTitre -->\n" .
 "   <div id=\"zoneTitre\">\n" .
 "    <div id=\"icon-options\" class=\"icon36\"></div>\n" .
 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
 "   </div> <!-- fin : zoneTitre -->\n" .
 "\n" .
 "   <!-- debut : zoneGauche -->\n" .
 "   <div id=\"zoneGauche\" >&nbsp;</div> <!-- fin : zoneGauche -->\n" .
 "\n" .
 "   <!-- debut : zoneMilieuComplet -->\n" .
 "   <div id=\"zoneMilieuComplet\">\n" .
 "\n" .
 "    <!-- debut : dashboard -->\n" .
 "    <div id=\"dashboard\">\n" );

if ( $Authentication->is_administrator() ) {
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
		 "       <li><a href=\"" . $Script . "?action=H\">" . $L_Historical .
		 "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
		 "</a></li>\n"
		);
		break;

	 case 'A':
	 case 'AX':
		print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
		 "       <li class=\"active\">" . $L_Alerts . "</li>\n" .
		 "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection .
		 "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=H\">" . $L_Historical .
		 "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
		 "</a></li>\n"
		);
		break;

	 case 'C':
	 case 'CX':
		print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
		 "       <li class=\"active\">" . $L_Connection . "</li>\n" .
		 "       <li><a href=\"" . $Script . "?action=H\">" . $L_Historical .
		 "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
		 "</a></li>\n"
		);
		break;

	 case 'H':
	 case 'HX':
	 case 'PH':
		print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection . "</a></li>\n" .
		 "       <li class=\"active\">" . $L_Historical . "</li>\n" .
		 "       <li><a href=\"" . $Script . "?action=S\">" . $L_SecretServer .
		 "</a></li>\n"
		);
		break;

	 case 'S':
	 case 'LK':
		print( "       <li><a href=\"" . $Script . "\">" . $L_Welcome . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=A\">" . $L_Alerts . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=C\">" . $L_Connection . "</a></li>\n" .
		 "       <li><a href=\"" . $Script . "?action=H\">" . $L_Historical .
		 "</a></li>\n" .
		 "       <li class=\"active\">" . $L_SecretServer . "</li>\n"
		);
		break;
	}

	print( "      </ul>\n" .
	 "      <!-- debut : pagelet -->\n" .
	 "      <div class=\"pagelet\">\n" );


	switch( $Action ) {
	 default:
		print( $L_Welcome_Text . "\n" );
		break;

	 case 'A':
		print(
		 "     <script>\n" .
		 "function activeMailFields() {\n" .
		 " if ( document.alert_form.alert_mail.value != 1 ) {\n" .
		 "  document.alert_form.mail_from.disabled = 1;\n" .
		 "  document.alert_form.mail_to.disabled = 1;\n" .
		 " } else {\n" .
		 "  document.alert_form.mail_from.disabled = 0;\n" .
		 "  document.alert_form.mail_to.disabled = 0;\n" .
		 " }\n" .
		 "}\n" .
		 "activeMailFields();\n" .
		 "     </script>\n" .
		 "     <form method=\"post\" name=\"alert_form\" action=\"" . $Script . "?action=AX\">\n" .
		 "      <table style=\"margin:10px auto;width:60%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Alert_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right\" width=\"50%\">" . $L_Verbosity_Alert .
		 "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"verbosity_alert\">\n" );

		$Detailed_Selected = '' ;
		$Normal_Selected = '';

		switch ( $Parameters->get( 'verbosity_alert' ) ) {
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
		 "        <td class=\"pair align-right\" width=\"50%\">" . $L_Alert_Syslog .
		 "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"alert_syslog\">\n" );

		$Selected = '';

		if ( $Parameters->get( 'alert_syslog' ) == '1' )
			$Selected = ' selected ' ;
			
		print( "          <option value=\"0\">" . $L_No . "</option>\n" .
		 "          <option value=\"1\"" . $Selected . ">" . $L_Yes . "</option>\n" );

		print( "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right\">" . $L_Alert_Mail . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <select name=\"alert_mail\" onChange=\"javascript:activeMailFields();\">\n" );

		$Selected = '';

		if ( $Parameters->get( 'alert_mail' ) == '1' )
			$Selected = ' selected ' ;
			
		print( "          <option value=\"0\">" . $L_No . "</option>\n" .
		 "          <option value=\"1\"" . $Selected . ">" . $L_Yes . "</option>\n" );

		print( "         </select>\n" .
		 "         <table>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_From . "</td>\n" .
		 "           <td><input type=\"text\" size=\"30\" name=\"mail_from\" value=\"".
		  $Parameters->get( 'mail_from' ) . "\" title=\"" . $L_Mail_From . 
		  "\" /></td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td>" . $L_To . "</td>\n" .
		 "           <td><textarea name=\"mail_to\" title=\"" . $L_Mail_To . "\">".
		  $Parameters->get( 'mail_to' ) . "</textarea></td>\n" .
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
		 "     </form>\n"
		);
		break;

	 case 'AX':
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

		if ( ($Mail_From = $Security->valueControl( $_POST[ 'mail_from' ],
		 'PRINTABLE' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (mail_from)</h1>" );
			break;
		}

		if ( ($Mail_To = $Security->valueControl( $_POST[ 'mail_to' ],
		 'PRINTABLE' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (mail_to)</h1>" );
			break;
		}

		try {
			$Parameters->set( 'verbosity_alert', $Verbosity_Alert );
			$Parameters->set( 'alert_syslog', $Alert_Syslog );
			$Parameters->set( 'alert_mail', $Alert_Mail );
			$Parameters->set( 'mail_from', $Mail_From );
			$Parameters->set( 'mail_to', $Mail_To );
		} catch( PDOException $e ) {
			print( "   <div id=\"alert\">\n" .
			 $L_ERR_MAJ_Alert .
			 "      <a class=\"button\" href=\"https://" . $Server . $Script .
			 "?action=P&id=" . $scr_id . "\">" .
			 $L_Return . "</a>\n" .
			 "   </div>\n" );
			break;
		}

		print( "     <div id=\"success\">\n" .
		 "      <img class=\"no-border\" src=\"Pictures/s_success.png\" alt=\"Success\" />\n" .
		 $L_Parameters_Updated .
		 "      <a class=\"button\" href=\"https://" . $Server . $Script .
		 "?action=SCR\">" . $L_Return . "</a>\n" .
		 "     </div>\n" );

		break;

	 case 'C':
	 	if ( file_exists( 'Libraries/Config_Authentication.inc.php' ) ) {
			include( 'Libraries/Config_Authentication.inc.php' );
		}
		
	 	if ( file_exists( 'Libraries/Config_Radius.inc.php' ) ) {
			include( 'Libraries/Config_Radius.inc.php' );
		}
		
	 	if ( file_exists( 'Libraries/Config_LDAP.inc.php' ) ) {
			include( 'Libraries/Config_LDAP.inc.php' );
		}
		
		if ( ! isset( $_LDAP_Port ) ) $_LDAP_Port = 389;
	 	
		switch( $Parameters->get( 'authentication_type' ) ) {
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
		 " }\n" .
		 "}" .
		 "     </script>\n" .
		 "     <form method=\"post\" name=\"Parameters\" action=\"" . $Script . "?action=CX\">\n" .
		 "      <table style=\"margin:10px auto;width:90%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Connection_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td class=\"align-right\" rowspan=\"6\">" .
		 "<label for=\"id_pwd\">" . $L_Use_Password . "</label>" .
		 "</td>\n" .
		 "        <td colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"D\" name=\"authentication_type\" " .
		 $Password_Selected . " id=\"id_pwd\" onClick=\"activeFields('D');\" />\n" .
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
		 "         <select id=\"id_Password_Complexity\" name=\"Password_Complexity\">\n" );
		
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
		 
		 "       <tr class=\"pair\">\n" .
		 "        <td class=\"align-right\" width=\"50%\" rowspan=\"5\">" .
		 "<label for=\"id_rds\">" . $L_Use_Radius . "</label>" .
		 "</td>\n" .
		 "        <td colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"R\" name=\"authentication_type\" " .
		 $Radius_Selected . " id=\"id_rds\" onClick=\"activeFields('R');\" />\n" .
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
		 
		 "       <tr class=\"pair\">\n" .
		 "        <td class=\"align-right\" width=\"50%\" rowspan=\"3\">" .
		 "<label for=\"id_rds\">" . $L_Use_LDAP . "</label>" .
		 "</td>\n" .
		 "        <td colspan=\"2\">\n" .
		 "         <input type=\"radio\" value=\"L\" name=\"authentication_type\" " .
		 $LDAP_Selected . " id=\"id_rds\" onClick=\"activeFields('L');\" />\n" .
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
		 "        <td class=\"align-right\" width=\"50%\">" .
		 $L_Expiration_Time . "</td>\n" .
		 "        <td colspan=\"2\">\n" .
		 "         <input type=\"text\" name=\"Expiration_Time\" value=\"" .
		 $Parameters->get( 'expiration_time' ) . "\" />\n" .
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
		 "activeFields('" . $Parameters->get( 'authentication_type' ) . "');\n" .
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
			
			break;
		}

		if ( ($Expiration_Time = $Security->valueControl( 
		 $_POST[ 'Expiration_Time' ], 'NUMERIC' )) == -1 ) {
			print( "     <h1>" . $L_Invalid_Value . " (Expiration_Time)</h1>" );
			break;
		}

		try {
			$Parameters->set( 'authentication_type', $authentication_type );
			$Parameters->set( 'expiration_time', $_POST[ 'Expiration_Time' ] );
		} catch( PDOException $e ) {
			print( "   <div id=\"alert\">\n" .
			 $L_ERR_MAJ_Connection .
			 "      <a class=\"button\" href=\"https://" . $Server . $Script .
			 "?action=P&id=" . $scr_id . "\">" .
			 $L_Return . "</a>\n" .
			 "   </div>\n" );
			break;
		}


		switch( $authentication_type ) {
		 case 'D':
			$Output = @fopen( 'Libraries/Config_Authentication.inc.php', 'w+' );
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
				 "      <a class=\"button\" href=\"https://" . $Server . $Script .
				 "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}
		
			fclose( $Output );
			
			break;
			
		 case 'R':
			$Output = @fopen( 'Libraries/Config_Radius.inc.php', 'w+' );
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
				 "      <a class=\"button\" href=\"https://" . $Server . $Script .
				 "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}
		
			fclose( $Output );
			
			break;

		 case 'L':
			$Output = @fopen( 'Libraries/Config_LDAP.inc.php', 'w+' );
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
"\n" .
"?>\n" ) === false ) {
				print( "   <div id=\"alert\">\n" .
				 $L_ERR_MAJ_Connection .
				 "      <a class=\"button\" href=\"https://" . $Server . $Script .
				 "?action=P&id=" . $scr_id . "\">" .
				 $L_Return . "</a>\n" .
				 "   </div>\n" );
				break 2;
			}
		
			fclose( $Output );
			
			break;
		}

		print( "     <div id=\"success\">\n" .
		 "      <img class=\"no-border\" src=\"Pictures/s_success.png\" alt=\"Success\" />\n" .
		 $L_Parameters_Updated .
		 "      <a class=\"button\" href=\"https://" . $Server . $Script .
		 "?action=SCR\">" . $L_Return . "</a>\n" .
		 "     </div>\n" );

		break;

	 case 'H':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Identities_PDO.inc.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Identities = new IICA_Identities(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$List_Identities = $Identities->listIdentities( 1 );
		
		if ( array_key_exists( 'scr_id', $_POST ) ) {
			$scr_id = $_POST[ 'scr_id' ];
		} else {
			$scr_id = '';
		}

		if ( array_key_exists( 'idn_id', $_POST ) ) {
			$idn_id = $_POST[ 'idn_id' ];
		} else {
			$idn_id = '';
		}
		
		if ( array_key_exists( 'h_date', $_POST ) ) {
			$h_date = $_POST[ 'h_date' ];
		} else {
			$h_date = '';
		}
		
		if ( array_key_exists( 'ip_source', $_POST ) ) {
			$ip_source = $_POST[ 'ip_source' ];
		} else {
			$ip_source = '';
		}

		if ( array_key_exists( 'message', $_POST ) ) {
			$message = $_POST[ 'message' ];
		} else {
			$message = '';
		}

		print( "     <form method=\"post\" name=\"f_historical\" id=\"i_historical\" action=\"" . $Script .
		 "?action=H\">\n" .
		 "      <script>\n" .
		 "function hiddeRow() {\n" .
		 " var displaySelection;\n" .
		 " if ( document.getElementById( 'search_icon' ).className == 'simple' ) {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple-selected';\n" .
		 "  displaySelection = 'none';\n" .
		 " } else {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple';\n" .
		 "  displaySelection = '';\n" .
		 " }\n" .
		 " if ( document.getElementById( 'r_search' ) ) {\n" .
		 "  document.getElementById( 'r_search' ).style.display = displaySelection;\n" .
		 " }\n" .
		 "}\n" .
		 "      </script>\n" .
		 "      <table style=\"margin:10px auto;width:98%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"6\">" . $L_Historical_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .

		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_Secret . "</th>\n" .
		 "        <th>" . $L_Identity . "</th>\n" .
		 "        <th>" . $L_Date . "</th>\n" .
		 "        <th>" . $L_IP_Source . "</th>\n" .
		 "        <th>" . $L_Message . "</th>\n" .
		 "        <th class=\"align-right\"><a id=\"search_icon\" class=\"simple-selected\" style=\"cursor: pointer;\" onclick=\"javascript:hiddeRow();\"><img class=\"no-border\" src=\"Pictures/b_search.png\" alt=\"" . $L_Search . "\" title=\"" . $L_Search . "\"></a></th>\n" .
		 "       </tr>\n" .
		 "       <tr style=\"display: none;\" id=\"r_search\" class=\"pair\">\n" .
		 "        <td><input type=\"text\" name=\"scr_id\" size=\"6\" maxlength=\"6\" " .
		 "value=\"" .  $scr_id . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" );
		 
		print( "        <td>\n" .
		 "         <select name=\"idn_id\" onChange=\"document.getElementById( 'i_historical' ).submit();\">\n" .
		 "          <option value=\"\">&nbsp;</option>\n" );

		foreach( $List_Identities as $Occurrence ) {
			if ( $Occurrence->idn_id == $idn_id ) {
				$Selected = ' selected';
			} else {
				$Selected = '';
			}
			
			print( "          <option value=\"" . $Occurrence->idn_id . "\"" . 
			 $Selected . ">" . $Occurrence->idn_login . "</option>\n" );
		}
		
		print( "         </select>\n" .
		 "        </td>\n" );
		
		print( "        <td><input type=\"text\" name=\"h_date\" size=\"10\" " .
		 "maxlength=\"10\" value=\"" . $h_date . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
		 "        <td><input type=\"text\" name=\"ip_source\" size=\"15\" " .
		 "maxlength=\"40\" value=\"" . $ip_source . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
		 "        <td class=\"align-middle\"><input type=\"text\" name=\"message\" " .
		 "size=\"80\" maxlength=\"100\" value=\"" . $message . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Search . "\" /></td>\n" .
		 "       </tr>\n" );

		$Tmp = $Secrets->totalHistoryEvents( $scr_id, $idn_id, $h_date, $message,
		 $ip_source );
		
		$Total = $Tmp->total ;
		$First_Date = $Tmp->first_date;

		if ( array_key_exists( 'size', $_GET ) ) {
			$size = $_GET[ 'size' ];
		} else {
			$size = 10;
		}
		
		if ( array_key_exists( 'start', $_GET ) ) {
			$start = $_GET[ 'start' ];
			
			$previous = $start - $size;
			if ( $previous < 0 ) $previous = 0;
			
			$next = $start + $size;
			if ( $next > ($Total - $size) ) $next = $Total - $size;

		} else {
			$start = 0;
			$previous = 0;
			$next = 10;
		}
		
		$Occurrences = $Secrets->listHistoryEvents( $scr_id, $idn_id, $h_date, $message,
		 $ip_source, $start, $size );
				
		$BG_Color = 'pair';
		 
		foreach( $Occurrences as $Occurrence ) {
			if ( $BG_Color == 'pair' ) {
				$BG_Color = 'impair';
			} else {
				$BG_Color = 'pair';
			}
			
			print( "       <tr class=\"" . $BG_Color . "\">\n" .
			 "        <td>" . $Occurrence->scr_id . "</td>\n" .
			 "        <td>" . $Occurrence->idn_login . "</td>\n" .
			 "        <td>" . $Occurrence->ach_date . "</td>\n" .
			 "        <td>" . $Occurrence->ach_ip . "</td>\n" .
			 "        <td colspan=\"2\">" . $Occurrence->ach_access . "</td>\n" .
			 "       </tr>\n" );
		}
		
		$default_date  = strftime( "%Y-%m-%d",
		 mktime( 0, 0, 0, date("m") - 6, date("d"), date("Y") ) );

		
		print( "       </tbody>\n" .
		 "       <tfoot>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">Total : <span class=\"green\">" . $Total . "</span></th>\n" .
		 "        <th colspan=\"4\" class=\"align-center\">\n" .
		 "<a href=\"" . $Script . "?action=H&start=0&size=" . $size . "\"><img class=\"no-border\" src=\"Pictures/bouton_premier.gif\" alt=\"First\" /></a>" .
		 "<a href=\"?action=H&start=" . $previous . "&size=" . $size . "\"><img class=\"no-border\" src=\"Pictures/bouton_precedent.gif\" alt=\"Previous\" /></a>" .
		 "&nbsp;" . ($start + 1) . "&nbsp;/&nbsp;" . ($start + $size) . "&nbsp;" .
		 "<a href=\"?action=H&start=" . $next . "&size=" . $size . "\"><img class=\"no-border\" src=\"Pictures/bouton_suivant.gif\" alt=\"Next\" /></a>" .
		 "<a href=\"?action=H&start=" . ( $Total - $size ) . "&size=" . $size . "\"><img class=\"no-border\" src=\"Pictures/bouton_dernier.gif\" alt=\"Last\" /></a>" .
		 "        </th>\n" .
		 "       </tr>\n" .
		 "       </tfoot>\n" .
		 "      </table>\n" .
		 "     </form>\n" .
		 "     <form method=\"post\" name=\"fPurge\" action=\"" . $Script .
		 "?action=PH\">\n" .
		 "      <p>" . $L_Specify_Purge_Date_History . " :" .
		 "       <input type=\"text\" size=\"10\" maxlength=\"10\" name=\"purge_date\" value=\"" . $default_date . "\" />\n" .
		 "       (" . $L_Oldest_Date_History . " : <span class=\"green\">" .
		 substr( $First_Date, 0, 10 ) . "</span>)\n" .
		 "       <input type=\"submit\" class=\"button\" value=\"Purge\" /></p>\n" .
		 "     </form>\n" );
		
		break;

	 case 'HX':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Identities_PDO.inc.php' );
		
		$Identities = new IICA_Identities(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );
		
		$List_Identities = $Identities->listIdentities( 1 );

		print( "     <form method=\"post\" name=\"f_historical\" action=\"" . $Script .
		 "?action=HX\">\n" .
		 "      <script>\n" .
		 "function hiddeRow() {\n" .
		 " var displaySelection;\n" .
		 " if ( document.getElementById( 'search_icon' ).className == 'simple' ) {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple-selected';\n" .
		 "  displaySelection = 'none';\n" .
		 " } else {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple';\n" .
		 "  displaySelection = '';\n" .
		 " }\n" .
		 " if ( document.getElementById( 'r_search' ) ) {\n" .
		 "  document.getElementById( 'r_search' ).style.display = displaySelection;\n" .
		 " }\n" .
		 "}\n" .
		 "      </script>\n" .
		 "      <table style=\"margin:10px auto;width:98%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"5\">" . $L_Historical_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .

		 "       <tbody>\n" .
		 "       <tr id=\"r_search\" class=\"pair\">\n" .
		 "        <td><input type=\"text\" name=\"scr_id\" size=\"6\" maxlength=\"6\" " .
		 " /></td>\n" );
		 
		print( "        <td>\n" .
		 "         <select name=\"idn_id\">\n" );

		foreach( $List_Identities as $Occurrence ) {
			print( "          <option value=\"" . $Occurrence->idn_id . "\">" .
			 $Occurrence->idn_login .
			 "</option>\n" );
		}
		
		print( "         </select>\n" .
		 "        </td>\n" );
		
		print( "        <td><input type=\"text\" name=\"date\" size=\"10\" " .
		 "maxlength=\"10\" /></td>\n" .
		 "        <td><input type=\"text\" name=\"ip_source\" size=\"15\" " .
		 "maxlength=\"40\" /></td>\n" .
		 "        <td class=\"align-middle\"><input type=\"text\" name=\"message\" size=\"30\" " .
		 "maxlength=\"100\" /></td>\n" .
		 "       </tr>\n" .

		 "       <tr>\n" .
		 "        <th>" . $L_Secret . "</th>\n" .
		 "        <th>" . $L_Identity . "</th>\n" .
		 "        <th>" . $L_Date . "</th>\n" .
		 "        <th>" . $L_IP_Source . "</th>\n" .
		 "        <th>" . $L_Message . "<span style=\"float: right\">" .
		 "<a id=\"search_icon\" class=\"simple-selected\" style=\"cursor: pointer;\" onclick=\"javascript:hiddeRow();\"><img class=\"no-border\" src=\"Pictures/b_search.png\" alt=\"" . $L_Search . "\" title=\"" . $L_Search . "\"></a></span></th>\n" .
		 "       </tr>\n" );
		 

		print( "       <tr>\n" .
		 "        <td colspan=\"5\" class=\"align-center\"><input type=\"submit\" class=\"button\" value=\"". $L_Search .
		 "\" /></td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n"
		);
		break;


	 case 'PH':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		if ( array_key_exists( 'purge_date', $_POST ) ) {
			$purge_date = $_POST[ 'purge_date' ];
		} else {
			$purge_date = '';
		}

		print( "     <form method=\"post\" name=\"f_purgeHistorical\" action=\"" .
		 $Script . "?action=PHX\">\n" .
		 "      <input type=\"hidden\" name=\"purge_date\" value=\"" . $purge_date . 
		 "\" />\n" .
		 "      <table style=\"margin:10px auto;\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Purge_Historical . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .

		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <th width=\"50%\">" . $L_Specify_Purge_Date_History . "</th>\n" .
		 "        <td class=\"bg-green\">" . $purge_date . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"" . $L_Purge .
		 "\" /><a class=\"button\" href=\"" . $Script . "\">" . $L_Cancel .
		 "</a></td>\n" .
		 "       </tr>\n" .
		 "      </table>\n" .
		 "     </form>\n" );
		
		break;


	 case 'PHX':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		if ( array_key_exists( 'purge_date', $_POST ) ) {
			$purge_date = $_POST[ 'purge_date' ];
		} else {
			print( $PageHTML->infoBox( $L_No_Purge_Date, $Script ) );

			exit();
		}
		
		try {
			$Secrets->purgeHistoryEvents( $purge_date );
		} catch( Exception $e ) {
			$alert_message = $Secrets->formatHistoryMessage( $e->getMessage() );

			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message,
			 $IP_Source );
			
			print( $PageHTML->infoBox( $e->getMessage(), $Script, 1 ) );

			exit();
		}

		$Message = sprintf( $L_Success_Purge, $purge_date );
		
		$alert_message = $Secrets->formatHistoryMessage( $Message );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		print( $PageHTML->infoBox( $Message, $Script . '?action=H', 2 ) );
		
		break;

	 case 'S':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( 'Libraries/Class_Secrets_Server.inc.php' );
		include_once( 'Libraries/Constants.php' );

		$Secret_Server = new Secret_Server();

		try {
			list( $Status, $Operator, $Creating_Date ) = $Secret_Server->SS_statusMotherKey();
		} catch( Exception $e ) {
			$Status = $e->getMessage();
		}

		if ( $Parameters->get( 'use_SecretServer' ) == '1' ) {
			$Select_Yes = 'selected';
			$Select_No = '';
		} else {
			$Select_Yes = '';
			$Select_No = 'selected';
		}

		print(
		 "      <table style=\"margin:10px auto;width:70%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_SecretServer_Management . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\" width=\"30%\">" . $L_Use_SecretServer . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <form name=\"f_use_server\" method=\"post\" action=\"" . $Script . "?action=US\">\n" .
		 "          <table>\n" .
		 "           <tr>\n" .
		 "            <td>\n" .
		 "             <select name=\"Use_SecretServer\">\n" .
		 "              <option value=\"0\" " . $Select_No . ">" .  $L_No . "</option>\n" .
		 "              <option value=\"1\" " . $Select_Yes . ">" .  $L_Yes . "</option>\n" .
		 "             </select>\n" .
		 "            </td>\n" .
		 "            <td>\n" .
		 "             <input type=\"submit\" class=\"button\" value=\"" . $L_Save . "\" />\n" .
		 "            </td>\n" .
		 "           </tr>\n" .
		 "          </table>\n" .
		 "         </form>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\">" . $L_Status . "</td>\n" .
		 "        <td class=\"pair\">\n" );
		
		if ( $Status == 'OK' ) {
			print( "         <table>\n" .
			 "          <tr>\n" .
			 "           <td class=\"bold green\" colspan=\"2\">" . $L_MOTHER_KEY_LOADED . "</td>\n" .
			 "          </tr>\n" .
			 "          <tr>\n" .
			 "           <td class=\"pair\">" . $L_Operator . "</td>\n" .
			 "           <td class=\"pair bold\">" . $Operator . "</td>\n" .
			 "          </tr>\n" .
			 "          <tr>\n" .
			 "           <td class=\"pair\">" . $L_Creation_Date . "</td>\n" .
			 "           <td class=\"pair bold\">" . $Creating_Date . "</td>\n" .
			 "          </tr>\n" .
			 "         </table>\n" );
		} else {
			print( '<span class="bold bg-orange">&nbsp;' . ${$Status} . "&nbsp;</span>\n" );
		}

		print( "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\">" .
		 $L_Load_Mother_Key . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <form name=\"f_load_key\" method=\"post\" action=\"" . $Script . "?action=LK\">\n" .
		 "          <table>\n" .
		 "           <tr>\n" .
		 "            <td class=\"pair\">" . $L_Insert_Operator_Key . "</td>\n" .
		 "            <td><input type=\"text\" name=\"Load_Operator_Key\" /></td>\n" .
		 "            <td><input type=\"submit\" class=\"button\" value=\"" . $L_Load . "\" /></td>\n" .
		 "           </tr>\n" .
		 "          </table>\n" .
		 "         </form>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\">" .
		 $L_Create_New_Keys . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <form name=\"f_create_key\" method=\"post\" action=\"" . $Script . "?action=NK\">\n" .
		 "          <table>\n" .
		 "           <tr>\n" .
		 "            <td class=\"pair\">" . $L_Insert_Operator_Key . "</td>\n" .
		 "            <td><input type=\"text\" name=\"New_Operator_Key\" /></td>\n" .
		 "            <td class=\"pair\">" . $L_Insert_Mother_Key . "</td>\n" .
		 "            <td><input type=\"text\" name=\"New_Mother_Key\" /></td>\n" .
		 "            <td><input type=\"submit\" class=\"button\" value=\"" . $L_Create . "\" /></td>\n" .
		 "           </tr>\n" .
		 "          </table>\n" .
		 "         </form>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"pair align-right align-middle\" width=\"30%\">" . $L_Shutdown_SecretServer . "</td>\n" .
		 "        <td class=\"pair\">\n" .
		 "         <form name=\"f_use_server\" method=\"post\" action=\"" . $Script . "?action=SHUT\">\n" .
		 "          <p><input type=\"submit\" class=\"button\" value=\"" . $L_Execute . "\" /></p>\n" .
		 "         </form>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" 
		);
		
		break;

	 case 'LK':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( 'Libraries/Class_Secrets_Server.inc.php' );
		include_once( 'Libraries/Constants.php' );
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$Secret_Server = new Secret_Server();

		try {
			$Result = $Secret_Server->SS_loadMotherKey( $_POST[ 'Load_Operator_Key' ] );
			
			if ( isset( ${$Result} ) ) $Result = ${$Result};
			
			$Flag_Error = 2;
		} catch( Exception $e ) {
			$Result = ${$e->getMessage()};
			$Flag_Error = 1;
		}

		$alert_message = $Secrets->formatHistoryMessage( $Result );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		print( $PageHTML->infoBox( $Result, $Script . '?action=S', $Flag_Error ) );
		
		break;

	 case 'NK':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( 'Libraries/Class_Secrets_Server.inc.php' );
		include_once( 'Libraries/Constants.php' );
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$Secret_Server = new Secret_Server();

		try {
			list( $Status, $O_Key, $M_Key, $C_Date ) = $Secret_Server->SS_initMotherKey(
			 $_POST[ 'New_Operator_Key' ], $_POST[ 'New_Mother_Key' ] );
			
			// Faire une page imprimable qui récapitule les informations créées.
			$Result = $L_Success_Page .
			 "<table>\n" .
			 " <thead>\n" .
			 " <tr>\n" .
			 "  <th colspan=\"2\">" . $L_New_Keys_Created . "</td>\n" .
			 " </tr>\n" .
			 " </thead>\n" .
			 " <tbody>\n" .
			 " <tr>\n" .
			 "  <td class=\"align-right normal\" width=\"50%\">" . $L_Type . "</td>\n" .
			 "  <td class=\"align-left pair\">" . ${$Status} . "</td>\n" .
			 " </tr>\n" .
			 " <tr>\n" .
			 "  <td class=\"align-right normal\">" . $L_Operator_Key . "</td>\n" .
			 "  <td class=\"align-left pair\">" . $O_Key . "</td>\n" .
			 " </tr>\n" .
			 " <tr>\n" .
			 "  <td class=\"align-right normal\">" . $L_Mother_Key . "</td>\n" .
			 "  <td class=\"align-left pair\">" . $M_Key . "</td>\n" .
			 " </tr>\n" .
			 " <tr>\n" .
			 "  <td class=\"align-right normal\">" . $L_Creation_Date . "</td>\n" .
			 "  <td class=\"align-left pair\">" . date( 'Y-m-d H:i:s', $C_Date ) . "</td>\n" .
			 " </tr>\n" .
			 "</table>\n";
			
			$Flag_Error = 2;
		} catch( Exception $e ) {
			$Result = ${$e->getMessage()};
			$Flag_Error = 1;
		}

		$alert_message = $Secrets->formatHistoryMessage( $Result );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		print( $PageHTML->infoBox( $Result, $Script . '?action=S', $Flag_Error ) );
		
		break;


	 case 'US':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		$Parameters->set( 'use_SecretServer', $_POST[ 'Use_SecretServer' ] );

		if ( $_POST[ 'Use_SecretServer' ] == 0 ) $Value = $L_No;
		else $Value = $L_Yes;
		
		$Result = $L_Use_SecretServer . ' : ' . $Value;

		$alert_message = $Secrets->formatHistoryMessage( $Result );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		print( $PageHTML->infoBox( $Result, $Script . '?action=S', 2 ) );
		
		break;


	 case 'SHUT':
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( 'Libraries/Class_Secrets_Server.inc.php' );
		include_once( 'Libraries/Constants.php' );
		include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
		include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
		
		$Secrets = new IICA_Secrets(
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$Secret_Server = new Secret_Server();

		try {
			$Result = $Secret_Server->SS_Shutdown();
			$Result = 'SecretServer ' . $Result;
			$Flag_Error = 2;
		} catch( Exception $e ) {
			$Result = ${$e->getMessage()};
			$Flag_Error = 1;
		}

		$alert_message = $Secrets->formatHistoryMessage( $Result );

		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
			
		print( $PageHTML->infoBox( $Result, $Script . '?action=S', $Flag_Error ) );
		
		break;
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
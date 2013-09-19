<?php

/**
* Ce script gère les secrets.
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

include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );

$Authentication = new IICA_Authentications();

if ( ! $Authentication->is_connect() ) {
   header( 'Location: '. URL_BASE . '/SM-login.php' );
	exit();
}

// Charge les libellés.
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );


$PageHTML = new HTML();

$Groups = new IICA_Groups();

$Secrets = new IICA_Secrets();


$Alert_Syslog = $PageHTML->getParameter( 'alert_syslog' );
$Alert_Mail = $PageHTML->getParameter( 'alert_mail' );

$groupsRights = $Authentication->getGroups( $_SESSION[ 'idn_id' ] );
//print_r( $groupsRights );

$Security = new Security();


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


if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}

$Verbosity_Alert = $PageHTML->getParameter( 'verbosity_alert' );
	
if ( $Action != 'SCR_V' ) {
	 // Cas de l'import des fonctions JS gérant les mots de passe.
	if ( $Action == 'SCR_A' or $Action == 'SCR_M' ) include( DIR_LIBRARIES . '/password_js.php' );
	else $innerJS = '';

	if ( ! preg_match("/X$/i", $Action ) ) {
		print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, '', $innerJS ) .
		 "   <!-- debut : zoneTitre -->\n" .
		 "   <div id=\"zoneTitre\">\n" .
		 "    <div id=\"icon-access\" class=\"icon36\"></div>\n" .
		 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
		 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
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
}


switch( $Action ) {
 default:
	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'label';
	}

	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	
	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right"><a class="button" href="' . $Script . '?action=add">' . $L_Create . '</a></span>' ;

		if ( array_key_exists( 'rp', $_GET ) ) {
			switch( $_GET[ 'rp' ] ) {
			 case 'home':
				$returnButton = "<span style=\"float: right\">" .
				 "<a class=\"button\" href=\"" . URL_BASE .
				 "/SM-home.php\">" . $L_Return . "</a></span>";
				break;

			 case 'users-prf_g':
				$returnButton = "<span style=\"float: right\">" .
				 "<a class=\"button\" href=\"" . URL_BASE .
				 "/SM-users.php?action=PRF_G&prf_id=" . $_GET[ 'prf_id' ] . "\">" .
				 $L_Return . "</a></span>";
				break;

			 case 'home-r2':
				$returnButton = "<span style=\"float: right\">" .
				 "<a class=\"button\" href=\"" . URL_BASE .
				 "/SM-users.php?action=R2\">" .
				 $L_Return . "</a></span>";
				break;
			}
			
			$Buttons = $addButton . $returnButton;
		} else {
			$Buttons = $addButton ;
		}
		
		print( "     <table class=\"table-bordered\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"4\">" . $L_List_Groups . $Buttons . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		 
		$List_Groups = $Groups->listGroups( '', $orderBy );
		
		print( "       <tr class=\"pair\">\n" );

		 
		if ( $orderBy == 'label' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'label-desc';
		} else {
			if ( $orderBy == 'label-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'label';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Label . "</th>\n" );

		 
		if ( $orderBy == 'alert' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'alert-desc';
		} else {
			if ( $orderBy == 'alert-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'alert';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Alert . "</th>\n" );

		print( "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );
		
		$BackGround = "pair";
		
		foreach( $List_Groups as $Group ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";
				
	
			if ( $Group->sgr_alert == 1 )
				$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
			else
				$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";


			print( "       <tr class=\"" . $BackGround . " surline\">\n" .
			 "        <td class=\"align-middle\">" . stripslashes( $Group->sgr_label ) . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Flag_Alert . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=M&sgr_id=" . $Group->sgr_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=D&sgr_id=" . $Group->sgr_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . 
			 $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF&sgr_id=" . $Group->sgr_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrscr_2.png\" alt=\"" .
			 $L_Profiles_Associate . "\" title=\"" . $L_Profiles_Associate . 
			 "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=SCR&sgr_id=" . $Group->sgr_id .
			 "&store\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_scredit_1.png\" alt=\"" .
			 $L_Secret_Management . "\" title=\"" . $L_Secret_Management . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"4\">Total : <span class=\"green\">" . 
		 count( $List_Groups ) . "</span>" . $Buttons . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'ADD':
	print( "     <form name=\"a_group\" method=\"post\" action=\"" . $Script .
	 "?action=ADDX\">\n" .
	 "      <table class=\"table-center table-min\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Group_Create . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\"><label for=\"iLabel\">" . $L_Label . "</label></td>\n" .
	 "        <td><input type=\"text\" id=\"iLabel\" name=\"Label\" size=\"60\" maxlength=\"60\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\"><label for=\"iAlert\">" . $L_Alert . "</label></td>\n" .
	 "        <td><input type=\"checkbox\" id=\"iAlert\" name=\"Alert\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Create . "\" /><a class=\"button\" href=\"" . $Script . "\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.a_group.Label.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'ADDX':
	$Return_Page = $Script;
 
	if ( isset( $_POST[ 'Alert' ] ) ) {
		if ( $_POST[ 'Alert' ] == 'on' )
			$Alert = 1;
	} else {
		$Alert = 0;
	}
	
	try {
		if ( $Verbosity_Alert == 2 ) {
			$alert_message = $Secrets->formatHistoryMessage( 'Groups->set( \'\', Label=\'' . $_POST[ 'Label' ] . '\', Alert=' . $Alert . ')' );
		
			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		}
		
		$Groups->set( '', $Security->valueControl( $_POST[ 'Label' ] ), $Alert );
	} catch( PDOException $e ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_ERR_CREA_Group );
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		
		print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Group, $Return_Page, 1 ) );
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_DUPL_Group, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Group, $Return_Page, 1 ) );
		}
		break;
	}


	$alert_message = $Secrets->formatHistoryMessage( '[' . addslashes( $_POST[ 'Label' ] ) . '] ' . $L_Group_Created );
		
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

			
	print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Group_Created . "\" />\n" .
		"</form>\n" .
		"<script>document.fMessage.submit();</script>" );

	break;


 case 'D':
	$Return_Page = $Script;
 
	if ( ($sgr_id = $Security->valueControl( $_GET[ 'sgr_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 ) );
		break;
	}

	$Group = $Groups->get( $sgr_id );
	
	if ( $Group->sgr_alert == 1 )
		$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Alert = "<img class=\"no-border\" src=\"". URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";

	print( "     <form method=\"post\" action=\"" . $Script . 
	 "?action=DX\">\n" .
	 "      <input type=\"hidden\" name=\"origin_alert\" value=\"" . $Group->sgr_alert .
	 "\" />\n" .
	 "      <input type=\"hidden\" name=\"sgr_id\" value=\"" . $sgr_id . "\" />\n" .
	 "      <table class=\"table-center table-min\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Group_Delete . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Label . "</td>\n" .
	 "        <td class=\"bg-light-grey td-aere\">\n" . stripslashes( $Group->sgr_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Alert . "</td>\n" .
	 "        <td class=\"bg-light-grey td-aere\">" . $Flag_Alert . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"td-aere\">&nbsp;</td>\n" .
	 "        <td class=\"td-aere\"><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a  class=\"button\" href=\"". $Script . "\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" 
	);
	
	break;


 case 'DX':
	$Return_Page = $Script;
 
	if ( ! $sgr_id = $Security->valueControl( $_POST[ 'sgr_id' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		if ( $Verbosity_Alert == 2 ) {
			$alert_message = $Secrets->formatHistoryMessage( 'Groups->delete( IdGroup=\'' . $sgr_id . '\' )' );
		
			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		}

		$Groups->delete( $sgr_id );
	} catch( PDOException $e ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_ERR_DELE_Group, $sgr_id );
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_DELE_Group, $Return_Page, 1 ) );
		break;
	}

	$alert_message = $Secrets->formatHistoryMessage( $L_Group_Deleted, $sgr_id );
		
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		
			
	print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Group_Deleted . "\" />\n" .
		"</form>\n" .
		"<script>document.fMessage.submit();</script>" );

	break;


 case 'M':
	$Return_Page = $Script;
 
	if ( ! $sgr_id = $Security->valueControl( $_GET[ 'sgr_id' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 ) );
		break;
	}

	$Group = $Groups->get( $sgr_id );
	
	if ( $Group->sgr_alert == 1 )
		$Flag_Check_Alert = ' checked';
	else
		$Flag_Check_Alert = '';

	
	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=MX\">\n" .
	 "      <input type=\"hidden\" name=\"origin_alert\" value=\"" .
	 $Group->sgr_alert . "\" />\n" .
	 "      <input type=\"hidden\" name=\"sgr_id\" value=\"" . $sgr_id . "\" />\n" .
	 "      <table class=\"table-center table-min\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Group_Modify . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\"><label for=\"iLabel\">" . $L_Label . "<label></td>\n" .
	 "        <td><input type=\"text\" id=\"iLabel\" name=\"Label\" class=\"input-xxlarge\" size=\"60\" maxlength=\"60\" value=\"" . 
	 htmlentities( stripslashes( $Group->sgr_label ), ENT_COMPAT, "UTF-8" ) . "\" /></td>\n" .
 	"       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\"><label for=\"iAlert\">" . $L_Alert . "</label></td>\n" .
	 "        <td><input id=\"iAlert\" name=\"Alert\" type=\"checkbox\" " .
	 $Flag_Check_Alert . " /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Modify . "\" /><a class=\"button\" href=\"" . $Script . "\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'MX':
	$Return_Page = $Script;
 
	if ( isset( $_POST[ 'Alert' ] ) ) {
		if ( $_POST[ 'Alert' ] == 'on' )
			$Alert = 1;
	} else {
		$Alert = 0;
	}
	
	try {
		if ( ($sgr_id = $Security->valueControl( $_POST[ 'sgr_id' ], 'NUMERIC' )) == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 )
			 );
			break;
		}

		if ( $Verbosity_Alert == 2 ) {
			$alert_message = $Secrets->formatHistoryMessage( 'Groups->set( IdGroup=\'' . $sgr_id . '\', Label=\'' .
			 addslashes( $_POST[ 'Label' ] ) . '\', Alert=\'' . $Alert . '\' )', $sgr_id );
		
			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		}
		
		$Groups->set( $sgr_id, addslashes( $_POST[ 'Label' ] ), $Alert );
	} catch( PDOException $e ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_ERR_MODI_Group, $sgr_id );
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_MODI_Group, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_DUPL_Group, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Group, $Return_Page, 1 ) );
		}
		break;
	}


	$alert_message = $Secrets->formatHistoryMessage( $L_Group_Modified, $sgr_id );
		
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

			
	print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Group_Modified . "\" />\n" .
		"</form>\n" .
		"<script>document.fMessage.submit();</script>" );

	break;


 case 'PRF':
	$Return_Page = $Script;
 
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	

	if ( ! $sgr_id = $Security->valueControl( $_GET[ 'sgr_id' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 ) );
		break;
	}
	
	
	$Profiles = new IICA_Profiles();

	$Rights = new IICA_Referentials();

	$List_Profiles = $Profiles->listProfiles();

	$List_Profiles_Associated = $Groups->listProfiles( $sgr_id, 1 );
	
	$List_Rights = $Rights->listRights();

	$Group = $Groups->get( $sgr_id );

	if ( $Group->sgr_alert == 1 )
		$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";

	print( "    <form method=\"post\" action=\"" . $Script . "?action=PRFX&sgr_id=" .
	 $sgr_id . "\" >\n" );

	if ( $Authentication->is_administrator() ) {
		print( "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 60%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Group_Profiles . "</th>\n" .
		 "       </tr>\n" .
		 
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Group . "</td>\n" .
		 "        <td class=\"align-left\">\n" .
		 "         <table class=\"table-bordered table-max\">\n" .
		 "          <tr>\n" .
		 "           <td class=\"align-right\" width=\"20%\">" . $L_Label . "</td>\n" .
		 "           <td class=\"pair blue1 bold\" width=\"80%\">" . stripslashes( $Group->sgr_label ) .
		 "</td>\n" .
		 "          </tr>\n" .
		 "          <tr>\n" .
		 "           <td class=\"align-right\">" . $L_Alert . "</td>\n" .
		 "           <td class=\"pair\">" . $Flag_Alert . "</td>\n" .
		 "          </tr>\n" .
		 "         </table>\n" .
		 "        </td>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" );
		 
//		$List_Profiles = $Profiles->listProfiles();
		
		$Action_Button = "<a class=\"button\" href=\"SM-users.php?action=PRF_V" .
		 "&sgr_id=" . $sgr_id . "&store\">" . $L_Profiles_Management . "</a>" ;
	

		
		print( "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Profiles_Associate . "</td>\n" .
		 "        <td>\n" .
//		 $Action_Button .
		 "         <table class=\"table-bordered table-max\" style=\"border: 1px solid grey;\">\n" .
		 "          <tr>\n" .
		 "           <th>" . $L_Label . "</th>\n" .
		 "           <th>" . $L_Rights . "</th>\n" .
		 "          </tr>\n" );
		
		$BackGround = "pair";
		
		foreach( $List_Profiles as $Profile ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";
			
			if ( array_key_exists( $Profile->prf_id, $List_Profiles_Associated ) ) $Status = ' checked ';
			else $Status = '';

			print( 
			 "          <tr class=\"" . $BackGround . " \">\n" .
			 "           <td class=\"align-middle\">" . stripslashes( $Profile->prf_label ) . "</td>\n" .
			 "           <td>\n" .
			 "            <select name=\"r_" . $Profile->prf_id . "[]\" size=\"4\" " .
			 "multiple>\n" );

			foreach( $List_Rights as $Right ) {
				$Selected = '';
				
				foreach( $List_Profiles_Associated as $Profile_Associated ) {
					if ( $_GET[ 'sgr_id' ] == $Profile_Associated->sgr_id
					 and $Profile->prf_id == $Profile_Associated->prf_id
					 and $Right->rgh_id == $Profile_Associated->rgh_id ) {
						$Selected = ' selected ';
						break;
					} 
				}
				
				print( "             <option value=\"" . $Right->rgh_id . "\"" . $Selected .">" .
				 ${$Right->rgh_name} . "</option>\n" );
			}
			
			print( "            </select>\n" .
			 "           </td>\n" .
			 "          </tr>\n" );
		}
		
		print( "         </table>\n" .
//		 $Action_Button .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td>" .
		 "<input type=\"submit\" class=\"button\" value=\"" . $L_Associate . "\" />" .
		 "<a class=\"button\" href=\"" . $Script . "\">" . $L_Cancel . "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	print( "    </form>\n" );
	break;


 case 'PRFX':
	$Return_Page = $Script;
 
	if ( ! $sgr_id = $Security->valueControl( $_GET[ 'sgr_id' ] ) ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		if ( $Verbosity_Alert == 2 ) {
			$alert_message = $Secrets->formatHistoryMessage( 'Groups->deleteProfiles( IdGroup=\'' . $sgr_id . '\' )', $sgr_id );
		
			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		}

		$Groups->deleteProfiles( $sgr_id );
		
		if ( $_POST != array() ) {
			foreach( $_POST as $Key => $Values ) {
				$prf_id = explode( '_', $Key );
				$prf_id = $prf_id[ 1 ];

				foreach( $Values as $rgh_id ) {
					if ( $Verbosity_Alert == 2 ) {
						$alert_message = $Secrets->formatHistoryMessage( 'Groups->addProfile( IdGroup=\'' . $sgr_id . '\', IdProfile=\'' .
						 $prf_id . '\', IdRight=\'' . $rgh_id . '\' )' );
		
						$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message,
						 $IP_Source );
					}

					$Groups->addProfile( $sgr_id, $prf_id, $rgh_id );
				}

			}
		}
	} catch( PDOException $e ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_ERR_ASSO_Identity );
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		break;
	}

	$alert_message = $Secrets->formatHistoryMessage( $L_Association_Complited );
		
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

	print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Association_Complited . "\" />\n" .
		"</form>\n" .
		"<script>document.fMessage.submit();</script>" );

	break;


 case 'SCR':
	$Return_Page = $Script;
 
	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'type';
	}

	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	
	$Secrets = new IICA_Secrets( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );
	 
	if ( array_key_exists( 'store', $_GET ) ) {
		if ( ! $sgr_id = $Security->valueControl( $_GET[ 'sgr_id' ] ) ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 )
			 );
			break;
		}
		
		$_SESSION[ 'sgr_id' ] = $sgr_id;
	}
	
	if ( isset( $_SESSION[ 'sgr_id' ] ) ) $sgr_id = $_SESSION[ 'sgr_id' ];
	else $sgr_id = '';

	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right"><a class="button" href="' . $Script . '?action=SCR_A&sgr_id=' . $sgr_id . '">' . $L_Create . '</a></span>';
		$returnButton = '<span style="float: right"><a class="button" href="' . $Script . '">' . $L_Return . '</a></span>' ;
		
		$Buttons = $addButton . $returnButton; // . $listButtons ;
		
		$Group = $Groups->get( $sgr_id );

		
		print( "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"8\">" . $L_List_Secrets . "</th>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"8\">\n" .
		 $L_Group . " : " . "<span class=\"green bold\">" . stripslashes( $Group->sgr_label ) . "</span>" . $Buttons . "\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		 
		$List_Secrets = $Secrets->listSecrets( $sgr_id, '', '', '', '', '', '', '',
		 false, $orderBy );
		
		print( "       <tr class=\"pair\">\n" );
	 
		if ( $orderBy == 'type' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'type-desc';
		} else {
			if ( $orderBy == 'type-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'type';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" .
		 $tmpClass . "\">" . $L_Type . "</th>\n" );
	 
		if ( $orderBy == 'environment' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'environment-desc';
		} else {
			if ( $orderBy == 'environment-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'environment';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Environment .
		 "</th>\n" );
	 
		if ( $orderBy == 'application' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'application-desc';
		} else {
			if ( $orderBy == 'application-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'application';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Application .
		 "</th>\n" );
	 
		if ( $orderBy == 'host' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'host-desc';
		} else {
			if ( $orderBy == 'host-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'host';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Host . "</th>\n"
		 );
	 
		if ( $orderBy == 'user' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'user-desc';
		} else {
			if ( $orderBy == 'user-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'user';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_User . "</th>\n"
		 );
	 
		if ( $orderBy == 'alert' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'alert-desc';
		} else {
			if ( $orderBy == 'alert-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'alert';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Alert .
		 "</th>\n" );
	 
		if ( $orderBy == 'comment' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'comment-desc';
		} else {
			if ( $orderBy == 'comment-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'comment';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=SCR&sgr_id=" . $sgr_id . "&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Comment .
		 "</th>\n" );

		print( "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );
		
		$BackGround = "pair";
		
		foreach( $List_Secrets as $Secret ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";

			if ( $Secret->scr_alert == 0 ) {
				$Img_Src = URL_PICTURES . '/bouton_non_coche.gif';
				$Img_Title = $L_No ;
			} else {
				$Img_Src = URL_PICTURES . '/bouton_coche.gif';
				$Img_Title = $L_Yes ;
			}
			$Alert_Image = '<img class="no-border" src="' . $Img_Src . '" title="' . $Img_Title .
			 '" alt="' . $Img_Title . '" />';

			print( "       <tr class=\"" . $BackGround . " surline\">\n" .
			 "        <td class=\"align-middle\">" . ${$Secret->stp_name} . "</td>\n" .
			 "        <td class=\"align-middle\">" . ${$Secret->env_name} . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Secret->scr_application . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Secret->scr_host . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Secret->scr_user . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Alert_Image . "</td>\n" .
			 "        <td class=\"align-middle\">" . $Secret->scr_comment . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=SCR_M&scr_id=" . $Secret->scr_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=SCR_D&scr_id=" . $Secret->scr_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"8\">Total : <span class=\"green\">" . 
		 count( $List_Secrets ) . "</span>" . $Buttons . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'SCR_A':
	if ( $Authentication->is_administrator() or $groupsRights[ 'W' ] ) {
		$Referentials = new IICA_Referentials();

		$List_Rights = $Referentials->listRights();
		$List_Types = $Referentials->listSecretTypes();
		$List_Environments = $Referentials->listEnvironments();
	
		if ( $Authentication->is_administrator() ) {
			$List_Groups = $Groups->listGroups();
		} else {
			$List_Groups = $Groups->listGroups( $_SESSION[ 'idn_id' ], '', 2 );
		}
	

		if ( array_key_exists( 'rp', $_GET ) ) {
			switch( $_GET[ 'rp' ] ) {
			 default:
				$Prev_Page = 'SM-home.php';
				$Continuous = '&rp=home';
				break;
			}
			
			$cancelButton = '<a class="button" href="' . URL_BASE . '/' . $Prev_Page .'">' . $L_Cancel . '</a>';
		} else {
			$cancelButton = '<a class="button" href="' . $Script .
			 '?action=SCR">' . $L_Cancel . '</a>';
			$Continuous = '';
		}
	
		print( "     <form name=\"a_group\" method=\"post\" action=\"" . $Script . "?action=SCR_AX". $Continuous . "\">\n" .
		 "      <table style=\"margin:10px auto;width:60%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Secret_Create . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Group . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"sgr_id\">\n" );

		foreach( $List_Groups as $Group ) {
			$Status = '';
			if ( array_key_exists( 'sgr_id', $_SESSION ) ) {
				if ( $Group->sgr_id == $_SESSION[ 'sgr_id' ] ) $Status = ' selected ';
			}
		
			print( "          <option value=\"" . $Group->sgr_id . "\"" . $Status . ">" .
			 $Security->XSS_Protection( $Group->sgr_label ) . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Type . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"stp_id\">\n" );
			
		foreach( $List_Types as $Type ) {
			print( "          <option value=\"" . $Type->stp_id . "\">" .
			 ${$Type->stp_name} . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
	 	 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Environment . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"env_id\">\n" );
		
		foreach( $List_Environments as $Environment ) {
			print( "          <option value=\"" . $Environment->env_id . "\">" .
			 ${$Environment->env_name} . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
 		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Application . "</td>\n" .
		 "        <td><input name=\"Application\" type=\"text\" size=\"60\" maxlength=\"60\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Host . "</td>\n" .
		 "        <td><input name=\"Host\" type=\"text\" size=\"100\" maxlength=\"255\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_User . "</td>\n" .
		 "        <td><input name=\"User\" type=\"text\" size=\"100\" maxlength=\"100\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Password . "</td>\n" .
		 "        <td><input name=\"Password\" id=\"iPassword\" type=\"text\" size=\"64\" maxlength=\"64\" onkeyup=\"checkPassword('iPassword', 'Result', 3, 8);\" onfocus=\"checkPassword('iPassword', 'Result', 3, 8);\"/><a class=\"button\" onclick=\"generatePassword( 'iPassword', 3, 8 )\">" . $L_Generate . "</a><img id=\"Result\" class=\"no-border\" width=\"16\" height=\"16\" alt=\"Ok\" src=\"" . URL_PICTURES . "/blank.gif\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Comment . "</td>\n" .
		 "        <td><input name=\"Comment\" type=\"text\" size=\"100\" maxlength=\"100\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Alert . "</td>\n" .
		 "        <td><input name=\"Alert\" type=\"checkbox\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Create . "\" />" .
		 $cancelButton . "</td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n" .
		 "     <script>\n" .
		 "document.a_group.sgr_id.focus();\n" .
		 "     </script>\n"
		);
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	break;


 case 'SCR_AX':
	$Return_Page = $Script . '?action=SCR';

	if ( array_key_exists( 'rp', $_GET ) ) {
		switch( $_GET[ 'rp' ] ) {
		 case 'home':
			$Return_Page = URL_BASE . '/SM-home.php';
			break;
		}
	}
 
	if ( $Authentication->is_administrator() or $groupsRights[ 'W' ] ) {
		$Secrets = new IICA_Secrets();
	 
		if ( isset( $_POST[ 'Alert' ] ) ) $Alert = 1;
		else $Alert = 0;

		if ( ($sgr_id = $Security->valueControl( $_POST[ 'sgr_id' ], 'NUMERIC' )) ==
		 -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (sgr_id)', $Return_Page, 1 )
			 );
			exit();
		}
		
		$Group = $Groups->get( $sgr_id );
		
		if ( ($stp_id = $Security->valueControl( $_POST[ 'stp_id' ], 'NUMERIC' )) ==
		 -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (stp_id)', $Return_Page, 1 )
			 );
			exit();
		}
		
		if ( ($env_id = $Security->valueControl( $_POST[ 'env_id' ], 'NUMERIC' )) ==
		 -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (env_id)', $Return_Page, 1 )
			 );
			exit();
		}
		

		try {
			if ( $Verbosity_Alert == 2 ) {
				$alert_message = $Secrets->formatHistoryMessage( 'Secrets->set( IdSecret=\'\', IdGroup=' . $sgr_id . ', IdType=' .
				 $stp_id . ', Host=\'' . $Security->valueControl( $_POST[ 'Host' ] ) .
				 '\', User=\'' . $Security->valueControl( $_POST[ 'User' ] ) . 
				 '\', Password=\'*********\', \'' .
				 $Security->valueControl( $_POST[ 'Comment' ] ) . '\', ' . $Alert . ', ' .
				 $env_id . ', ' . 
				 '\'' . $Security->valueControl( $_POST[ 'Application' ] ) . '\' )' );
		
				$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message,
				 $IP_Source );
			}
			
			$Secrets->set( '', $sgr_id, $stp_id, 
			 $Security->valueControl( $_POST[ 'Host' ] ),
			 $Security->valueControl( $_POST[ 'User' ] ),
			 $Security->valueControl( $_POST[ 'Password' ] ),
			 $Security->valueControl( $_POST[ 'Comment' ] ), $Alert, 
			 $env_id, $Security->valueControl( $_POST[ 'Application' ] ) );
		} catch( PDOException $e ) {
			$alert_message = $Secrets->formatHistoryMessage( $L_ERR_CREA_Secret );
		
			$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message,
			 $IP_Source );

			print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Secret, URL_BASE .
			 $Script . "?action=P&sgr_id=" . $_GET[ 'sgr_id' ], 1 )
			 );
			exit();
		} catch( Exception $e ) {
			if ( $PageHTML->getParameter( 'use_SecretServer' ) == '1' ) {
				$Error = $e->getMessage();
				
				if ( isset( ${$Error} ) ) $Error = ${$Error};
				
				print( $PageHTML->returnPage( $L_Title, $Error, $Return_Page, 1 ) );
			} else {
				if ( $e->getCode() == 1062 ) {
					print( $PageHTML->returnPage( $L_Title, $L_ERR_DUPL_Secret, $Return_Page, 1 ) );
				} else {
					print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Secret, $Return_Page, 1 ) );
				}
			}
			exit();
		}


		$alert_message = $Secrets->formatHistoryMessage( $L_Secret_Created, '', $stp_id, $env_id,
		 $_POST[ 'Application' ], $_POST[ 'Host' ], $_POST[ 'User' ] );
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
		
		if ( $Group->sgr_alert == 1 ) {
			if ( $Alert_Syslog == 1 ) {
				$Security->writeLog( $alert_message );
			}
			 
			if ( $Alert_Mail == 1 ) {
				$Security->writeMail( $alert_message, $PageHTML->getParameter( 'mail_from' ),
				 $PageHTML->getParameter( 'mail_to' ) );
			}
		}

		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Secret_Created . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );

	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->returnPage( $L_Title, $L_No_Authorize, $Return_Page, 1 ) );
		exit();
	}

	break;


 case 'SCR_V': // Réponse à la requête AJAX
	$Secrets = new IICA_Secrets();
	
	try {
		$Secret = $Secrets->get( $_POST[ 'scr_id' ] );
	} catch( Exception $e ) {
		$Resultat = array( 'Statut' => 'erreur',
			'Message' => $e->getMessage() );

		echo json_encode( $Resultat );
		
		break;
	}

	$Group = $Groups->get( $Secret->sgr_id );

	if ( $Secret->scr_alert == 1 or $Group->sgr_alert == 1 ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_Secret_View, $_POST[ 'scr_id' ], ${$Secret->stp_name},
		 ${$Secret->env_name}, $Secret->scr_application, $Secret->scr_host, $Secret->scr_user );

		$Secrets->updateHistory( $_POST[ 'scr_id' ], $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

		if ( $Alert_Syslog == 1 ) {
			$Security->writeLog( $alert_message );
		}
			 
		if ( $Alert_Mail == 1 ) {
			$Security->writeMail( $alert_message, $PageHTML->getParameter( 'mail_from' ),
			 $PageHTML->getParameter( 'mail_to' ) );
		}
	}

	if ( isset( $groupsRights[ $Secret->sgr_id ] ) ) {
		$accessControl = in_array( 1, $groupsRights[ $Secret->sgr_id ] );
	} else {
		$accessControl = false;
	}

	if ( $Authentication->is_administrator()
	 or $accessControl ) {
		$Resultat = array( 'Statut' => 'succes',
		 'group' => $Secret->sgr_label,
		 'l_group' => $L_Group,
		 'type' => ${$Secret->stp_name},
		 'l_type' => $L_Type,
		 'environment' => ${$Secret->env_name},
		 'l_environment' => $L_Environment,
		 'application' => $Secret->scr_application,
		 'l_application' => $L_Application,
		 'host' => $Secret->scr_host,
		 'l_host' => $L_Host,
		 'user' => $Secret->scr_user,
		 'l_user' => $L_User,
		 'l_nothing' => $L_Nothing,
		 'password' => stripslashes( $Secret->scr_password ),
		 'l_password' => $L_Password );
	}

	echo json_encode( $Resultat );

	break;

 case 'SCR_M':
	$Secrets = new IICA_Secrets( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	$Return_Script = $Script;
	
	if ( array_key_exists( 'rp', $_GET ) ) {
		$Return_Script = URL_BASE . "/SM-home.php";

		switch( $_GET[ 'rp' ] ) {
		 case 'home':
			$home = '&rp=home';
			$cancelButton = "<a class=\"button\" href=\"" . $Return_Script . "\">" . $L_Cancel . "</a>";
			break;

		 case 'home-r2':
		 	$Return_Script .= "?Action=R2";
			$home = '&rp=home-r2';
			$cancelButton = "<a class=\"button\" href=\"" . $Return_Script . "\">" . $L_Cancel . "</a>";
			break;
		}
	} else {
		$home = '';
		$cancelButton = "<a class=\"button\" href=\"" . $Script . "?action=SCR\">" .
		 $L_Cancel . "</a>";
	}

	try {
		$Secret = $Secrets->get( $_GET[ 'scr_id' ] );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $e->getMessage(), $Return_Script, 1 ) );

		break;
	} catch( Exception $e ) {
		print( $PageHTML->infoBox( $e->getMessage(), $Return_Script, 1 ) );

		break;
	}

	if ( isset( $groupsRights[ $Secret->sgr_id ] ) ) {
		$accessControl = in_array( 3, $groupsRights[ $Secret->sgr_id ] );
	} else {
		$accessControl = false;
	}

	if ( $Authentication->is_administrator()
	 or $accessControl ) {
		$Referentials = new IICA_Referentials();

		$List_Rights = $Referentials->listRights();
		$List_Types = $Referentials->listSecretTypes();
		$List_Environments = $Referentials->listEnvironments();

		if ( $Authentication->is_administrator() ) {
			$List_Groups = $Groups->listGroups();
		} else {
			$List_Groups = $Groups->listGroups( $_SESSION[ 'idn_id' ], '', 2 );
		}
	
		if ( $Secret->scr_alert == 1 ) $Flag_Alert = ' checked';
		else $Flag_Alert = '';
	
		print( "     <form name=\"m_group\" method=\"post\" action=\"" . $Script . "?action=SCR_MX&scr_id=" .
		 $_GET[ 'scr_id' ] . $home . "\">\n" .
		 "		<input type=\"hidden\" name=\"origin_alert\" value=\"" .
		  $Secret->scr_alert . "\" />\n" .
		 "      <table style=\"margin:10px auto;width:60%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Secret_Modify . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Group . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"sgr_id\">\n" );

		foreach( $List_Groups as $Group ) {
			if ( $Group->sgr_id == $Secret->sgr_id ) $Status = ' selected ';
			else $Status = '';
		
			print( "          <option value=\"" . $Group->sgr_id . '"' . $Status . ">" .
			 $Security->XSS_Protection( $Group->sgr_label ) . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Type . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"stp_id\">\n" );
			
		foreach( $List_Types as $Type ) {
			if ( $Type->stp_id == $Secret->stp_id ) $Status = ' selected ';
			else $Status = '';

			print( "          <option value=\"" . $Type->stp_id . '"' . $Status . ">" .
			 ${$Type->stp_name} . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
 		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Environment . "</td>\n" .
		 "        <td>\n" .
		 "         <select name=\"env_id\">\n" );
			
		foreach( $List_Environments as $Environment ) {
			if ( $Environment->env_id == $Secret->env_id ) $Status = ' selected ';
			else $Status = '';
		
			print( "          <option value=\"" . $Environment->env_id . "\"" . $Status .
			 ">" . ${$Environment->env_name} . "</option>\n" );
		}
			
		print( "         </select>\n" .
		 "        </td>\n" .
 		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Application . "</td>\n" .
		 "        <td><input name=\"Application\" type=\"text\" size=\"60\" maxlength=\"60\"  value=\"" . htmlentities( stripslashes( $Secret->scr_application ), ENT_COMPAT, "UTF-8" ) . "\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Host . "</td>\n" .
		 "        <td><input name=\"Host\" type=\"text\" size=\"100\" maxlength=\"255\" " .
		 "value=\"" . htmlentities( stripslashes( $Secret->scr_host ), ENT_COMPAT, "UTF-8" ) . "\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_User . "</td>\n" .
		 "        <td><input name=\"User\" type=\"text\" size=\"100\" maxlength=\"100\" " .
		 "value=\"" . htmlentities( stripslashes( $Secret->scr_user ), ENT_COMPAT, "UTF-8" ) . "\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Password . "</td>\n" .
		 "        <td><input name=\"Password\" id=\"iPassword\" type=\"text\" size=\"64\" maxlength=\"64\" " .
		 "onkeyup=\"checkPassword('iPassword', 'Result', 3, 8);\" onfocus=\"checkPassword('iPassword', 'Result', 3, 8);\"" .
		 "value=\"" . htmlentities( stripslashes( $Secret->scr_password ), ENT_COMPAT, "UTF-8" ) . "\"/>" .
		 "<a class=\"button\" onclick=\"generatePassword( 'iPassword', 3, 8 )\">" . $L_Generate . "</a>" .
		 "<img id=\"Result\" class=\"no-border\" width=\"16\" height=\"16\" alt=\"Ok\" src=\"" . URL_PICTURES . "/blank.gif\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Comment . "</td>\n" .
		 "        <td><input name=\"Comment\" type=\"text\" size=\"100\" maxlength=\"100\" " .
		 "value=\"" . htmlentities( stripslashes( $Secret->scr_comment ), ENT_COMPAT, "UTF-8" ) . "\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Alert . "</td>\n" .
		 "        <td><input name=\"Alert\" type=\"checkbox\"" . $Flag_Alert . " /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Modify . "\" />" .
		 $cancelButton . "</td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n" .
		 "     <script>\n" .
		 "document.m_group.sgr_id.focus();\n" .
		 "     </script>\n"
		);
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	break;


 case 'SCR_MX':
	$accessControl = false;

	if ( ! $Authentication->is_administrator() ) {
		// Vérifie si l'utilisateur à un droit sur le groupe de secret.
		if ( isset( $groupsRights[ $_POST[ 'sgr_id' ] ] ) ) {
			$accessControl = in_array( 3, $groupsRights[ $_POST[ 'sgr_id' ] ] );
		}
	}

	if ( $Authentication->is_administrator()
	 or $accessControl ) {
		if ( array_key_exists( 'rp', $_GET ) ) {
			switch( $_GET[ 'rp' ] ) {
			 case 'home':
				$home = '&rp=home';
				$Return_Page = URL_BASE . "/SM-home.php";
				break;

			 case 'home-r2':
				$home = '&rp=home-r2';
				$Return_Page = URL_BASE . "/SM-home.php?Action=R2\">";
				break;
			}
		} else {
			$home = '';
			$Return_Page = $Script . "?action=P&scr_id=" .
			 $_GET[ 'scr_id' ];
		}
	
		$Secrets = new IICA_Secrets();
	 
		if ( isset( $_POST[ 'Alert' ] ) ) $Alert = 1;
		else $Alert = 0;
		
		if ( ($scr_id = $Security->valueControl( $_GET[ 'scr_id' ], 'NUMERIC' )) == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (scr_id)', $Return_Page,
			 1 ) );
			exit();
		}
		
		if ( ($sgr_id = $Security->valueControl( $_POST[ 'sgr_id' ], 'NUMERIC' ))
		 == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (sgr_id)', $Return_Page,
			 1 ) );
			exit();
		}
		
		if ( ($stp_id = $Security->valueControl( $_POST[ 'stp_id' ], 'NUMERIC' ))
		 == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (stp_id)', $Return_Page,
			 1 ) );
			exit();
		}
		
		if ( ($env_id = $Security->valueControl( $_POST[ 'env_id' ], 'NUMERIC' ))
		 == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (env_id)', $Return_Page,
			 1 ) );
			exit();
		}
		

		try {
			if ( $Verbosity_Alert == 2 ) {
				$alert_message = $Secrets->formatHistoryMessage( 'Secrets->set( IdSecret=' . $scr_id . ', IdGroup=' . $sgr_id . ', IdType=' .
				 $stp_id . ', Host=' . 
				 '\'' . $Security->valueControl( $_POST[ 'Host' ] ) . '\', User=' .
				 '\'' . $Security->valueControl( $_POST[ 'User' ] ) . '\', Password=' .
				 '\'*********\', Comment=' .
				 '\'' . $Security->valueControl( $_POST[ 'Comment' ] ) . '\', Alert=' .
				 $Alert . ', IdEnvironment=' . $env_id . ', Application=' . 
				 '\'' . $Security->valueControl( $_POST[ 'Application' ] ) . '\' )' );
		
				$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
				 $IP_Source );
			}

			$Secrets->set( $scr_id, $sgr_id, $stp_id,
			 $Security->valueControl( $_POST[ 'Host' ] ), 
			 $Security->valueControl( $_POST[ 'User' ] ), 
			 $Security->valueControl( $_POST[ 'Password' ] ), 
			 $Security->valueControl( $_POST[ 'Comment' ] ), $Alert, $env_id,
			 $Security->valueControl( $_POST[ 'Application' ] ) );
		} catch( PDOException $e ) {
			$alert_message = $L_ERR_MODI_Secret ;
		
			$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
			 $IP_Source );

			print( $PageHTML->returnPage( $L_Title, $L_ERR_MODI_Secret, $Return_Page, 1 ) );
			exit();
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				print( $PageHTML->returnPage( $L_Title, $L_ERR_DUPL_Secret, $Return_Page, 1 ) );
			} else {
				print( $PageHTML->returnPage( $L_Title, $L_ERR_MODI_Secret, $Return_Page, 1 ) );
			}
			exit();
		}


		$alert_message = $Secrets->formatHistoryMessage( $L_Secret_Modified, $scr_id, $stp_id,
		 $env_id, $_POST[ 'Application' ], $_POST[ 'Host' ], $_POST[ 'User' ] );
		
		$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
		 $IP_Source );

		$Group = $Groups->get( $sgr_id );

		if ( $Group->sgr_alert == 1 or $_POST[ 'origin_alert' ] == 1 ) {
			if ( $Alert_Syslog == 1 ) {
				$Security->writeLog( $alert_message );
			}
			 
			if ( $Alert_Mail == 1 ) {
				$Security->writeMail( $alert_message, $PageHTML->getParameter( 'mail_from' ),
				 $PageHTML->getParameter( 'mail_to' ) );
			}
		}
			
		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Secret_Modified . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->returnPage( $L_Title, $L_No_Authorize, $Return_Page, 1 ) );
		exit();
	}

	break;


 case 'SCR_D':
	$Return_Page = $Script . '?action=SCR';
	$Continuous = '';

	if ( array_key_exists( 'rp', $_GET ) ) {
		if ( $_GET[ 'rp' ] == 'home' ) {
			$Return_Page = URL_BASE . '/SM-home.php';
			$Continuous = '&rp=home';
		}
	}
	
	$Secrets = new IICA_Secrets();

	if ( ($scr_id = $Security->valueControl( $_GET[ 'scr_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (scr_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Secret = $Secrets->get( $scr_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $e->getMessage(), $Return_Page, 1 ) );

		break;
	} catch( Exception $e ) {
		print( $PageHTML->infoBox( $e->getMessage(), $Return_Page, 1 ) );

		break;
	}
	 
	if ( isset( $groupsRights[ $Secret->sgr_id ] ) ) {
		$accessControl = in_array( 4, $groupsRights[ $Secret->sgr_id ] );
	} else {
		$accessControl = false;
	}

	if ( $Authentication->is_administrator()
	 or $accessControl ) {
		$Referentials = new IICA_Referentials();

		$List_Rights = $Referentials->listRights();
		$List_Types = $Referentials->listSecretTypes();
	
		$List_Groups = $Groups->listGroups();

		if ( $Secret->scr_alert == 1 )
			$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
		else
			$Flag_Alert = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";
	
		print( "     <form method=\"post\" action=\"" . $Script .
		 "?action=SCR_DX&scr_id=" . $_GET[ 'scr_id' ] . $Continuous . "\">\n" .
		 "      <input type=\"hidden\" name=\"sgr_id\" value=\"" . 
		 $Secret->sgr_id . "\"/>\n" .
		 "      <table style=\"margin:10px auto;width:60%\">\n" .
		 "       <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Secret_Delete . "</th>\n" .
		 "       </tr>\n" .
		 "       </thead>\n" .
		 "       <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Group . "</td>\n" .
		 "        <td class=\"pair\">" . $Security->XSS_Protection( $Secret->sgr_label ) . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Type . "</td>\n" .
		 "        <td class=\"pair\">" . $Secret->stp_id . "</td>\n" .
 		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Host . "</td>\n" .
		 "        <td class=\"pair\">" . htmlentities( stripslashes( $Secret->scr_host ), ENT_COMPAT, "UTF-8" )  . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_User . "</td>\n" .
		 "        <td class=\"pair\">" . htmlentities( stripslashes( $Secret->scr_user ), ENT_COMPAT, "UTF-8" ) . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Password . "</td>\n" .
		 "        <td class=\"pair\">*********</td>\n" .
//		 "        <td class=\"pair\">" . $Secret->scr_password . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Comment . "</td>\n" .
		 "        <td class=\"pair\">" . htmlentities( stripslashes( $Secret->scr_comment ), ENT_COMPAT, "UTF-8" ) . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Alert . "</td>\n" .
		 "        <td class=\"pair\">" . $Flag_Alert . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a class=\"button\" href=\"" . $Return_Page . "\">" . $L_Cancel . "</a></td>\n" .
		 "       </tr>\n" .
		 "       </tbody>\n" .
		 "      </table>\n" .
		 "     </form>\n"
		);
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	break;


 case 'SCR_DX':
	$Return_Page = $Script . '?action=SCR';
	$Continuous = '';

	if ( array_key_exists( 'rp', $_GET ) ) {
		if ( $_GET[ 'rp' ] == 'home' ) {
			$Return_Page = URL_BASE . '/SM-home.php';
			$Continuous = '&rp=home';
		}
	}

	if ( isset( $groupsRights[ $_POST[ 'sgr_id' ] ] ) ) {
		$accessControl = in_array( 4, $groupsRights[ $_POST[ 'sgr_id' ] ] );
	} else {
		$accessControl = false;
	}

	if ( $Authentication->is_administrator()
	 or $accessControl ) {
		$Secrets = new IICA_Secrets();
	 
		if ( isset( $_POST[ 'Alert' ] ) ) $Alert = 1;
		else $Alert = 0;

		if ( ($scr_id = $Security->valueControl( $_GET[ 'scr_id' ], 'NUMERIC' )) == -1 ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (scr_id)', $Return_Page, 1 )
			 );
			exit();
		}

		try {
			if ( $Verbosity_Alert == 2 ) {
				$alert_message = $Secrets->formatHistoryMessage( 'Secrets->delete( IdSecret=' . $scr_id . ' )', $scr_id );
		
				$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
				 $IP_Source );
			}

			$Secrets->delete( $scr_id );
		} catch( PDOException $e ) {
			$alert_message = $Secrets->formatHistoryMessage( $L_ERR_DELE_Secret, $scr_id ) ;
		
			$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
			 $IP_Source );

			$Return_Page = "https://" . $Server . $Script . "?action=P&id=" . $scr_id;
			print( $PageHTML->returnPage( $L_Title, $L_ERR_DELE_Secret, $Return_Page, 1 ) );
			exit();
		}

		$alert_message = $Secrets->formatHistoryMessage( $L_Secret_Deleted, $scr_id );
		
		$Secrets->updateHistory( $scr_id, $_SESSION[ 'idn_id' ], $alert_message,
		 $IP_Source );

		$Group = $Groups->get( $_POST[ 'sgr_id' ] );
		
		if ( ! isset( $_POST[ 'origin_alert' ] ) ) $_POST[ 'origin_alert' ] = 0;
		
		 if ( $Group->sgr_alert == 1 or $_POST[ 'origin_alert' ] == 1 ) {
			$alert_message = $Secrets->formatHistoryMessage( $L_Secret_Deleted, $scr_id );

			if ( $Alert_Syslog == 1 ) {
				$Security->writeLog( $alert_message );
			}
			 
			if ( $Alert_Mail == 1 ) {
				$Security->writeMail( $alert_message, $PageHTML->getParameter( 'mail_from' ),
				 $PageHTML->getParameter( 'mail_to' ) );
			}
		}
			
		print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Return_Page . "\">\n" .
			" <input type=\"hidden\" name=\"iMessage\" value=\"" . $L_Secret_Deleted . "\" />\n" .
			"</form>\n" .
			"<script>document.fMessage.submit();</script>" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';
 
		print( $PageHTML->returnPage( $L_Title, $L_No_Authorize, $Return_Page, 1 ) );
		exit();
	}

	break;
}

if ( $Action != 'SCR_V' ) {
	$Logout_button = 1;

	print(  "   </div> <!-- fin : zoneMilieuComplet -->\n" .
	 $PageHTML->construireFooter( $Logout_button ) .
	 $PageHTML->piedPageHTML() );
}

?>
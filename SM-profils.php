<?php

/**
* Ce script gère les utilisateurs.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.7
* @date 2013-07-10
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();


// Force la langue par défaut à Français.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
	$_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}	

$Script = URL_BASE . $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];

if ( ! isset( $_SESSION[ 'idn_id' ] ) )
	header( 'Location: ' . URL_BASE . '/SM-login.php' );

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );

$Action = '';
$Choose_Language = 0;

include( DIR_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );

$Authentication = new IICA_Authentications();

if ( ! $Authentication->is_connect() ) {
   header( 'Location: '. URL_BASE . 'SM-login.php' );
	exit();
}

// Charge les libellés.
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Identities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Civilities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Entities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );


$PageHTML = new HTML();

$Identities = new IICA_Identities();

$Civilities = new IICA_Civilities();

$Entities = new IICA_Entities();

$Security = new Security();


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


if ( array_key_exists( 'action', $_GET ) ) {
	$Action = strtoupper( $_GET[ 'action' ] );
}

if ( ! preg_match("/X$/i", $Action ) ) {
	$JS_Scripts = array( 'Ajax_users.js', 'jquery.notif.js', 'mustache.js', 'Ajax_profiles.js' );

	print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $JS_Scripts ) .
	 "   <!-- debut : zoneTitre -->\n" .
	 "   <div id=\"zoneTitre\">\n" .
	 "    <div id=\"icon-users\" class=\"icon36\"></div>\n" .
	 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
	 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
	 "   </div> <!-- fin : zoneTitre -->\n" .
	 "\n" .
	 "   <!-- debut : zoneMilieuComplet -->\n" .
	 "   <div id=\"zoneMilieuComplet\">\n" .
	 "\n" );

	if ( isset( $_POST[ 'infoMessage']) ) {
		print( "<script>\n" .
		 "     var myVar=setInterval(function(){cacherInfo()},3000);\n" .
		 "     function cacherInfo() {\n" .
		 "        document.getElementById(\"success\").style.display = \"none\";\n" .
		 "        clearInterval(myVar);\n" .
		 "     }\n" .
		 "</script>\n" .
		 "    <div id=\"success\">\n" .
		 $_POST[ 'infoMessage' ] .
		 "    </div>\n" );
	}
}


if ( array_key_exists( 'orderby', $_GET ) ) {
	$orderBy = $_GET[ 'orderby' ];
} else {
	$orderBy = 'entity';
}


switch( $Action ) {
 default:
 case 'PRF_V':
	$Return_Page = $Script;
 
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	
	if ( array_key_exists( 'idn_id', $_GET ) ) {
		if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page,
			 1 ) );
			break;
		}
	} else {
		$idn_id ='';
	}

	if ( array_key_exists( 'store', $_GET ) ) {
		if ( array_key_exists( 'idn_id', $_GET ) ) {
			$_SESSION[ 'p_action' ] = $Script . '?action=P&idn_id=' . $idn_id;
		} else {
			$_SESSION[ 'p_action' ] = $_SERVER[ 'HTTP_REFERER' ];
		}
	}

	if ( array_key_exists( 'home', $_GET ) ) {
		$_SESSION[ 'p_action' ] = 'SM-home.php';
	}

	if ( array_key_exists( 'rp', $_GET ) ) {
		if ( $_GET[ 'rp'] = 'home' ) $_SESSION[ 'p_action' ] = 'SM-home.php';
	}

	if ( ! isset( $_SESSION[ 'p_action' ] ) ) {
		$_SESSION[ 'p_action' ] = 'SM-home.php';
	}

	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'label';
	}

	$Profiles = new IICA_Profiles();

	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right"><a class="button" href="javascript:putAddProfile();">' . $L_Create . '</a></span>';
		$returnButton = '<span style="float: right"><a class="button" href="' . $_SESSION[ 'p_action' ] . '">' .
		 $L_Return . '</a></span>' ;
		
		$Buttons = $addButton . $returnButton;
		
		if ( $orderBy == 'label' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'label-desc';
		} else {
			if ( $orderBy == 'label-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'label';
		}

		print( "     <table class=\"table-bordered\" cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_List_Profiles . $Buttons . "</th>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td onclick=\"javascript:document.location='" . $Script . 
		 "?action=PRF_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Label . "</td>\n" .
		 "        <td width=\"30%\">" . $L_Actions . "</td>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody id=\"iListProfiles\">\n" );
				 
		$List_Profiles = $Profiles->listProfiles( $orderBy );

		$BackGround = "pair";
		
		foreach( $List_Profiles as $Profile ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";

			print( "       <tr id=\"profil_" . $Profile->prf_id . "\" class=\"" . $BackGround . " surline\">\n" .
			 "        <td id=\"label_" . $Profile->prf_id . "\" class=\"align-middle\"><span id=\"field_" . $Profile->prf_id . "\">" . 
			 $Security->XSS_Protection( $Profile->prf_label ) . "</span></td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"#\" onclick=\"modifyProfile(event," . $Profile->prf_id . ");\">" . //$Script . "?action=PRF_M&prf_id=" . $Profile->prf_id . "\">" .
			 "<img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"javascript:deleteProfile(" . $Profile->prf_id . ");\">" . //$Script . "?action=PRF_D&prf_id=" . $Profile->prf_id . "\">" .
			 "<img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF_G&prf_id=" . $Profile->prf_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrscr_2.png\" alt=\"" . $L_Groups_Associate . "\" title=\"" . $L_Groups_Associate . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"2\">Total : <span class=\"green\">" . 
		 count( $List_Profiles ) . "</span>" . $Buttons . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	print( 
	 "     <div id=\"addProfile\" class=\"tableau_synthese hide modal\" style=\"top:50%;left:40%;\">\n".
	 "      <button type=\"button\" class=\"close\">×</button>\n".
	 "      <p class=\"titre\">".$L_Profile_Create."</p>\n".
	 "      <div id=\"detailProfile\" style=\"margin:6px;padding:6px;width:400px;\" class=\"corps align-center\">\n" .
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" . $L_Label . "</span><span  class=\"td-aere\"><input id=\"iProfileLabel\" type=\"text\" class=\"obligatoire\" name=\"Label\" size=\"35\" maxlength=\"35\" /></span></p>\n" .
	 "       <p class=\"align-center\"><input id=\"iButtonCreateProfile\" type=\"submit\" class=\"button\" value=\"". $L_Create . "\" /></p>\n" .
	 "      </div> <!-- Fin : detailProfil -->\n" .
	 "     </div> <!-- Fin : addProfile -->\n" .
	 "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'PRF_AX':
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION['Language'] . '_SM-Secrets.php');
	
	$Profiles = new IICA_Profiles();

	if ( $Authentication->is_administrator() ) {
		if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_Invalid_Value . ' (Label)'
				);

			print( json_encode( $Resultat ) );

			exit();
		}

		try {
			$Profiles->set( '', $Label );

			$alert_message = $Secrets->formatHistoryMessage( $L_Profile_Created . ' (' . $Profiles->LastInsertId . ')' );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 2 );
		} catch( PDOException $e ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_ERR_CREA_Profile
				);

			print( json_encode( $Resultat ) );

			$alert_message = $Secrets->formatHistoryMessage( $L_ERR_CREA_Profile );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 2 );

			exit();
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				$Message = $L_ERR_DUPL_Profile;

				$Resultat = array(
					'Status' => 'error',
					'Title' => $L_Error,
					'Message' => $Message
					);
			} else {
				$Message = $L_ERR_CREA_Profile;

				$Resultat = array(
					'Status' => 'error',
					'Title' => $L_Error,
					'Message' => $Message
					);
			}

			print( json_encode( $Resultat ) );

			$alert_message = $Secrets->formatHistoryMessage( $Message );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 2 );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Created,
			'idProfile' => $Profiles->LastInsertId,
			'Label' => $Label,
			'URL_PICTURES' => URL_PICTURES,
			'L_Groups_Associate' => $L_Groups_Associate,
			'Script' => $Script,
			'L_Modify' => $L_Modify,
			'L_Delete' => $L_Delete
			);

	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);
	}

	print( json_encode( $Resultat ) );

	exit();
	
	break;


 case 'PRF_M':
	$Return_Page = $Script . '?action=PRF_V';
 
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	
	if ( ($prf_id = $Security->valueControl( $_GET[ 'prf_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	if ( array_key_exists( 'store', $_GET ) ) {
		$_SESSION[ 'p_action' ] = '?action=P&prf_id=' . $prf_id;
	}

	$Profiles = new IICA_Profiles();


	try {
		$Profile = $Profiles->get( $prf_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_Profile_Not_Found, $Return_Page, 1 ) );
		break;
	}
	
	
	if ( $Authentication->is_administrator() ) {
		print( "    <form name=\"m_profil\" method=\"post\" action=\"" . $Script . 
		 "?action=PRF_MX\" />\n" .
		 "     <input type=\"hidden\" name=\"prf_id\" value=\"" . $prf_id . "\" />\n" .
		 "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 60%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Profile_Modify . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Label . "</th>\n" .
		 "        <td><input type=\"text\" name=\"Label\" size=\"60\" maxlength=\"60\"  value=\"" . $Security->XSS_Protection( $Profile->prf_label ) . "\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"3\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Modify . "\" /><a class=\"button\" href=\"" . $Script . "?action=PRF_V\">" . $L_Cancel . "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "    </form>\n" .
		 "    <script>\n" .
		 "document.m_profil.Label.focus();\n" .
		 "    </script>\n" .
		 "\n" );
	} else {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	break;


 case 'PRF_MX':
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles();

	if ( $Authentication->is_administrator() ) {
		if ( ($prf_id = $Security->valueControl( $_POST[ 'prf_id' ], 'NUMERIC' )) == -1
		 ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_Invalid_Value . ' (prf_id)'
				);

			print( json_encode( $Resultat ) );

			exit();
		}

		if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_Invalid_Value . ' (prf_id)'
				);

			print( json_encode( $Resultat ) );

			exit();
		}

		try {
			$Profiles->set( $prf_id, $Label );

			$alert_message = $Secrets->formatHistoryMessage( $L_Profile_Modified . ' (' . $prf_id . ')' );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 3 );
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				$Message = $L_ERR_DUPL_Profile;
			} else {
				$Message = $L_ERR_MODI_Profile;
			}

			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $Message
				);

			print( json_encode( $Resultat ) );

			$alert_message = $Secrets->formatHistoryMessage( $Message . ' (' . $prf_id . ')' );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 3 );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Modified
			);

		print( json_encode( $Resultat ) );

		exit();
	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);

		print( json_encode( $Resultat ) );

		exit();
	}
	
	break;


 case 'PRF_D':
	$Return_Page = $Script . '?action=PRF_V';
 
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	
	if ( ($prf_id = $Security->valueControl( $_GET[ 'prf_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	$Profiles = new IICA_Profiles();


	try {
		$Profile = $Profiles->get( $prf_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_Profile_Not_Found, $Return_Page, 1 ) );
		break;
	}
	
	
	if ( $Authentication->is_administrator() ) {
		print( "    <form method=\"post\" action=\"" . $Script . "?action=PRF_DX\" />\n" .
		 "     <input type=\"hidden\" name=\"prf_id\" value=\"" . $prf_id . "\" />\n" .
		 "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 60%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Profile_Delete . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Label . "</th>\n" .
		 "        <td class=\"bg-light-grey\">" . 
		 $Security->XSS_Protection( $Profile->prf_label ) . "</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"3\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a class=\"button\" href=\"" . $Script . "?action=PRF_V\">" . $L_Cancel . "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "    </form>\n" .
		 "\n" );
	} else {
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	break;


 case 'PRF_DX':
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles();

	if ( $Authentication->is_administrator() ) {
		if ( ($prf_id = $Security->valueControl( $_POST[ 'prf_id' ], 'NUMERIC' )) == -1 ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_Invalid_Value . ' (prf_id)'
				);

			print( json_encode( $Resultat ) );

			exit();
		}

		try {
			$Profiles->delete( $prf_id );

			$alert_message = $Secrets->formatHistoryMessage( $L_Profile_Deleted . ' (' . $prf_id . ')' );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 4 );
		} catch( PDOException $e ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_ERR_DELE_Profile
				);

			print( json_encode( $Resultat ) );

			$alert_message = $Secrets->formatHistoryMessage( $L_ERR_DELE_Profile . ' (' . $prf_id . ')' );
		
			$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 4 );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Deleted
			);

		print( json_encode( $Resultat ) );

		exit();
	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);

		print( json_encode( $Resultat ) );

		exit();
	}
	
	break;


 case 'PRF_G':
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
	

	$Profiles = new IICA_Profiles();

	$Profile = $Profiles->get( $_GET[ 'prf_id' ] );

	$List_Groups_Associated = $Profiles->listGroups( $_GET[ 'prf_id' ] );
	

	$Groups = new IICA_Groups();

	$List_Groups = $Groups->listGroups();

	
	$Rights = new IICA_Referentials();

	$List_Rights = $Rights->listRights();


	if ( $Authentication->is_administrator() ) {
		print( "    <form method=\"post\" action=\"" . $Script .
		 "?action=PRF_GX&prf_id=" . $_GET[ 'prf_id' ] . "\">\n" .
		 "     <table style=\"margin: 10px auto;width: 60%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_Profile_Groups . "</th>\n" .
		 "       </tr>\n" .
		 
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Profil . "</td>\n" .
		 "        <td class=\"pair green bold\">\n" .
//		 "           <span style=\"border: 1px solid grey; padding: 3px;\" " .
//		 "class=\"pair green bold\">" . 
		 stripslashes( $Profile->prf_label ) . 
//		 "</span>\n" .
		 "        </td>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" 
		);
		
		$manageGroups = "         <a class=\"button\" href=\"" . URL_BASE . "/SM-secrets.php?rp=users-prf_g&prf_id=" .
		 $_GET[ 'prf_id' ] . "\">" . $L_Groups_Management . "</a>\n" ;
		
		print( "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Groups . "</td>\n" .
		 "        <td>\n" .
		 "         <table class=\"table-bordered\">\n" .
		 "          <thead>\n" .
		 "          <tr>\n" .
		 "           <th colspan=\"2\">\n" .
		 $manageGroups .
		 "           </th>\n" .
		 "          </tr>\n" .
		 "          </thead>\n" .
		 "          <tbody>\n" .
		 "          <tr>\n" .
		 "           <th>" . $L_Label . "</th>\n" .
		 "           <th>" . $L_Rights . "</th>\n" .
		 "          </tr>\n" );
		
		$BackGround = "pair";


		foreach( $List_Groups as $Group ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";
			
			if ( array_key_exists( $Group->sgr_id, $List_Groups_Associated ) )
				$Status = ' checked ';
			else $Status = '';

			print( 
			 "          <tr class=\"" . $BackGround . "\">\n" .
			 "           <td class=\"align-middle\">" . stripslashes( $Group->sgr_label ) . "</td>\n" .
			 "           <td>\n" .
			 "            <select name=\"r_" . $Group->sgr_id . "[]\" size=\"4\" " .
			 "multiple>\n" );

			foreach( $List_Rights as $Right ) {
				$Selected = '';
				
				foreach( $List_Groups_Associated as $Group_Associated ) {
					if ( $_GET[ 'prf_id' ] == $Group_Associated->prf_id
					 and $Group->sgr_id == $Group_Associated->sgr_id
					 and $Right->rgh_id == $Group_Associated->rgh_id ) {
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
		
		print( "          </tbody>\n" .
		 "          <tfoot>\n" .
		 "          <tr>\n" .
		 "           <th colspan=\"2\">\n" .
		 $manageGroups .
		 "           </th>\n" .
		 "          </tr>\n" .
		 "          </tfoot>\n" .
		 "         </table>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td>" .
		 "<input type=\"submit\" class=\"button\" value=\"" . $L_Associate . "\" />" .
		 "<a class=\"button\" href=\"" . $_SERVER[ 'HTTP_REFERER' ] . "\">" . $L_Cancel .
		 "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "\n" .
		 "    </form>\n" );
	} else {
		$Return_Page = 'https://' . $Server . '/SM-home.php';
 
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	break;


 case 'PRF_GX':
	$Return_Page = $Script . '?action=PRF_V';
 
	include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
	

	$Groups = new IICA_Groups();

	$Secrets = new IICA_Secrets();


	if ( ! $prf_id = $Security->valueControl( $_GET[ 'prf_id' ] ) ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	$Verbosity_Alert = $PageHTML->getParameter( 'verbosity_alert' );

	try {
		$alert_message = $Secrets->formatHistoryMessage( '[' . $prf_id . '] ' . $L_Profiles_Clean );

		$Secrets->updateHistory( 'L_ALERT_PRF', $alert_message, 4 );
		
		$Groups->deleteProfiles( '', $prf_id );
		
		$Store = '';
		
		if ( $_POST != array() ) {
			foreach( $_POST as $Key => $Values ) {
				$Store_Key = explode( '_', $Key );
				$Store_Key = $Store_Key[ 1 ];

				foreach( $Values as $Value ) {
					$alert_message = $Secrets->formatHistoryMessage( $L_Profiles_Associate . ' [' . $prf_id . ']' .
						 '[' . $Store_Key . ']' .
						 '[' . $Value . ']' );
		
					$Secrets->updateHistory( 'L_ALERT_PRSG', $alert_message, 2 );

					$Groups->addProfile( $Store_Key, $prf_id, $Value );
				}

			}
		}
	} catch( PDOException $e ) {
		$alert_message = $Secrets->formatHistoryMessage( $L_ERR_ASSO_Identity );
		
		$Secrets->updateHistory( 'L_ALERT_PRSG', $alert_message, 2 );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		break;
	}

	$alert_message = $Secrets->formatHistoryMessage( $L_Association_Complited );
		
	$Secrets->updateHistory( 'L_ALERT_PRSG', $alert_message, 2 );

	print( "<form method=\"post\" name=\"fInfoMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"infoMessage\" value=\"". $L_Association_Complited . "\" />\n" .
		"</form>\n" .
		"<script>document.fInfoMessage.submit();</script>\n" );

	break;
}


print(  "   </div> <!-- fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( 1 ) .
 $PageHTML->piedPageHTML() );

?>
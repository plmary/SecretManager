<?php

/**
* Ce script gère l'affichage des options auxquelles à droit l'utilisateur.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.3
* @date 2013-07-09
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();

$Search_Style = 2;

// Par défaut langue Française.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
   $_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}
	
$Script = URL_BASE . $_SERVER[ 'SCRIPT_NAME' ];
$Server = $_SERVER[ 'SERVER_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );

$Action = '';
$Choose_Language = 0;

include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );


$Authentication = new IICA_Authentications( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

if ( ! $Authentication->is_connect() ) {
   header( 'Location: ' . URL_BASE . '/SM-login.php' );
	exit();
}


include( DIR_LIBRARIES . '/Class_HTML.inc.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Identities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );

$PageHTML = new HTML();


// Charge les différents objets utiles à cet écran.
$Identities = new IICA_Identities();

$Groups = new IICA_Groups();

$Secrets = new IICA_Secrets();

$Referentials = new IICA_Referentials();

$Security = new Security();


// Récupère la liste des Droits, des Types et des Environnements.
$List_Rights = $Referentials->listRights();
$List_Types = $Referentials->listSecretTypes();
$List_Environments = $Referentials->listEnvironments();


// Récupère les Droits que cet utilisateur a sur les différents Groupes de Secrets.
$groupsRights = $Authentication->getGroups( $_SESSION[ 'idn_id' ] );


// Contrôle si la session n'a pas expirée.
if ( ! $Authentication->validTimeSession() ) {
	header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX&expired' );
} else {
	$Authentication->saveTimeSession();
}


// Si l'utilisateur n'est pas Administrateur alors il est bridé sur les Groupes de Secrets
// auxquels il a accès.
if ( ! $Authentication->is_administrator() )
	$List_Groups = $Groups->listGroups( $_SESSION[ 'idn_id' ] );
else
	$List_Groups = $Groups->listGroups();


if ( array_key_exists( 'action', $_GET ) ) {
   $Action = strtoupper( $_GET[ 'action' ] );
}

   
print( $PageHTML->enteteHTML( $L_Title, $Choose_Language ) .
 "  <script>\n" .
 "function viewPassword( scr_id ) {\n" .
 " var WindowHeight = 400;\n" .
 " var WindowWidth = 950;\n" .
 " window.open('SM-secrets.php?action=SCR_V&scr_id='+ scr_id,'Mot de passe',\n" .
 " 'width=' + WindowWidth + ',height=' + WindowHeight + ',' +\n" .
 " 'left=' + (screen.width - WindowWidth) / 2 + ',' +\n" .
 " 'top=' + (screen.height - WindowHeight) / 2 );\n" .
 "}\n" .
 "  </script>\n" .
 "   <!-- debut : zoneTitre -->\n" .
 "   <div id=\"zoneTitre\">\n" .
 "    <div id=\"icon-home\" class=\"icon36\"></div>\n" .
 "    <span id=\"titre\">". $L_Title . "</span>\n" .
 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
 "    </div> <!-- Fin : zoneTitre -->\n" .
 "\n" .
 "   <!-- debut : zoneGauche -->\n" .
 "   <div id=\"zoneGauche\" >&nbsp;</div> <!-- fin : zoneGauche -->\n" .
 "\n" .
 "   <!-- debut : zoneMilieuComplet -->\n" .
 "   <div id=\"zoneMilieuComplet\">\n" .
 "\n" );

$sgr_id = '';
$stp_id = '';
$env_id = '';
$scr_application = '';
$scr_host = '';
$scr_user = '';
$scr_comment = '';


if ( array_key_exists( 'orderby', $_GET ) ) {
	$orderBy = $_GET[ 'orderby' ];
} else {
	$orderBy = 'group';
}


switch( $Action ) {
 case 'R': // Fonction de Recherche.
	if ( $_POST[ 'sgr_id' ] != '' ) {
		if ( ($sgr_id = $Security->XSS_Protection( $_POST[ 'sgr_id' ], 'NUMERIC' )) == -1 ) {
			print( "    <div id=\"dashboard\">\n" .
			 "     <h1>" . $L_Invalid_Characters . " (Id)</h1>" .
			 "    </div> <!-- fin : dashboard -->\n" );
			break;
		}
	}

	if ( $_POST[ 'stp_id' ] != '' ) {
		if ( ($stp_id = $Security->XSS_Protection( $_POST[ 'stp_id' ], 'NUMERIC' )) == -1 ) {
			print( "    <div id=\"dashboard\">\n" .
			 "     <h1>" . $L_Invalid_Characters . " (Id)</h1>" .
			 "    </div> <!-- fin : dashboard -->\n" );
		}
	}

	if ( $_POST[ 'env_id' ] != '' ) {
		if ( ($env_id = $Security->XSS_Protection( $_POST[ 'env_id' ], 'NUMERIC' )) == -1 ) {
			print( "    <div id=\"dashboard\">\n" .
			 "     <h1>" . $L_Invalid_Characters . " (Id)</h1>" .
			 "    </div> <!-- fin : dashboard -->\n" );
		}
	}

	if ( $_POST[ 'scr_application' ] != '' ) {
		$scr_application = $Security->XSS_Protection( $_POST[ 'scr_application' ] );
	}

	if ( $_POST[ 'scr_host' ] != '' ) {
		$scr_host = $Security->XSS_Protection( $_POST[ 'scr_host' ] );
	}

	if ( $_POST[ 'scr_user' ] != '' ) {
		$scr_user = $Security->XSS_Protection( $_POST[ 'scr_user' ] );
	}

	if ( $_POST[ 'scr_comment' ] != '' ) {
		$scr_comment = $Security->XSS_Protection( $_POST[ 'scr_comment' ] );
	}


 default:
	include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
	
	print( "    <div id=\"dashboard\">\n\n" );

	if ( $Authentication->is_administrator() ) $idn_id = '';
	else $idn_id = $_SESSION[ 'idn_id' ];
	
	if ( array_key_exists( 'last_login', $_GET ) ) {
		print( 
		 "<script>\n" .
		 "     function cacherInfo() {\n" .
		 "        document.getElementById(\"info\").style.display = \"none\";\n" .
		 "     }\n" .
		 "</script>\n" .
		 "     <div id=\"info\" onclick=\"javascript:cacherInfo();\">" .
		 "     <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>\n" .
		 $L_Last_Connection . " : <b>" . $_SESSION[ 'idn_last_connection' ] . "</b><br/>" .
		 $L_Updated_Authentication . " : <b>" . $_SESSION[ 'idn_updated_authentication' ] .
		 "</b></div>\n" );
	}

	// ===========================================
	// Tableau d'affichage des Utilisateurs.
	if ( $Authentication->is_administrator() ) {
		include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
		include( DIR_LIBRARIES . '/Class_IICA_Entities_PDO.inc.php' );
		include( DIR_LIBRARIES . '/Class_IICA_Civilities_PDO.inc.php' );
		
		$Profiles = new IICA_Profiles();

		$Entities = new IICA_Entities();

		$Civilities = new IICA_Civilities();


		print( "\n" .
		 "     <!-- Début : affichage de la synthèse des utilisateurs -->\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_List_Users . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Total_Users_Base . 
		 " : <span class=\"bg-green bold\">&nbsp;" .
		 $Identities->total() . "&nbsp;</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><a class=\"surline\" href=\"SM-users.php?particular=disable\">" . $L_Total_Users_Disabled . " : <span class=\"green bold\">" .
		 $Identities->totalDisabled() . "</span></a></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td>" . $L_Total_Users_Expired . " : <span class=\"green bold\">" .
		 $Identities->totalExpired() . "</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td>" . $L_Total_Users_Attempted . " : <span class=\"green bold\">" .
		 $Identities->totalAttempted() . "</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td>" . $L_Total_Users_Super_Admin . " : <span class=\"green bold\">" .
		 $Identities->totalSuperAdmin() . "</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><p class=\"space\"><a class=\"button\" href=\"SM-users.php?rp=home\">" .
		 $L_Manage_Users . "</a></p></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des utilisateurs -->\n\n" .

		 // ===========================================
		 // Tableau d'affichage des Groupes de Secrets.
		 "     <!-- Début : affichage de la synthèse des groupes -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_List_Groups . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Total_Groups_Base . 
		 " : <span class=\"bg-green bold\">&nbsp;" . 
		 $Groups->total( $idn_id ) . "&nbsp;</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><p class=\"space\"><a class=\"button\" href=\"SM-secrets.php?rp=home\">" .
		 $L_Manage_Groups . "</a></p></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des groupes -->\n\n" .

		 // ===========================================
		 // Tableau d'affichage des Profils.
		 "     <!-- Début : affichage de la synthèse des profils -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_List_Profiles . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Total_Profiles_Base . 
		 " : <span class=\"bg-green bold\">&nbsp;" . 
		 $Profiles->total() . "&nbsp;</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><p class=\"space\"><a class=\"button\" href=\"SM-users.php?action=PRF_V&rp=home\">" .
		 $L_Manage_Profiles . "</a></p></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des groupes -->\n\n" .

		 // ===========================================
		 // Tableau d'affichage des Entités.
		 "     <!-- Début : affichage de la synthèse des entités -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_List_Entities . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Total_Entities_Base . 
		 " : <span class=\"bg-green bold\">&nbsp;" . 
		 $Entities->total() . "&nbsp;</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><p class=\"space\"><a class=\"button\" href=\"SM-users.php?action=ENT_V&rp=home\">" .
		 $L_Manage_Entities . "</a></p></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des entités -->\n\n" .

		 // ===========================================
		 // Tableau d'affichage des Civilités.
		 "     <!-- Début : affichage de la synthèse des civilités -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "     <table class=\"table-bordered\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th>" . $L_List_Civilities . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td>" . $L_Total_Entities_Base . 
		 " : <span class=\"bg-green bold\">&nbsp;" . 
		 $Civilities->total() . "&nbsp;</span></td>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"impair\">\n" .
		 "        <td><p class=\"space\"><a class=\"button\" href=\"SM-users.php?action=CVL_V&rp=home\">" .
		 $L_Manage_Civilities . "</a></p></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des civilités -->\n\n" .
		 "     <div style=\"clear: both;\"></div>\n" );
	}


	// ====================
	// Tableau de recherche
	if ( $Search_Style == 1 ) {
		$searchButton = '<span style="float: right">' .
		 '<a id="search_icon" class="simple" style="cursor: pointer;" ' .
		 'onclick="javascript:hiddeTableBody();">' .
		 '<img class="no-border" src="' . URL_PICTURES . '/b_search.png" alt="'. $L_Search . 
		 '" title="' . $L_Search . '" />' .
		 '</a></span>' ;

		print( 
		 "     <script>\n" .
		 "function hiddeTableBody() {\n" .
		 " var displaySelection;\n" .
		 " if ( document.getElementById( 'search_icon' ).className == 'simple' ) {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple-selected';\n" .
		 "  displaySelection = 'none';\n" .
		 " } else {\n" .
		 "  document.getElementById( 'search_icon' ).className = 'simple';\n" .
		 "  displaySelection = 'block';\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'group_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'type_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'environment_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'application_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'host_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'user_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'comment_criteria' ).style.display = displaySelection;\n" .
		 " }\n" .
		 " if ( document.getElementById( 'group_criteria' ) ) {\n" .
		 "  document.getElementById( 'search_buttons' ).style.display = displaySelection;\n" .
		 " }\n" .
		 "}\n" .
		 "     </script>\n" .
		 "     <!-- Début : search -->\n\n" .
		 "     <div class=\"search\">\n" .
		 "     <form class=\"simple\" method=\"post\" name=\"searchForm\" action=\"" .
		 $Script . "?action=R\" >\n" .
		 "      <div style=\"width: 100%;float: left;\">\n" .
		 "       <p class=\"title\">" . $L_Search_Secrets . $searchButton . "</p>\n" .
		 "      </div>\n" .
		 "     <script>\n" .
		 "hiddeTableBody();\n" .
		 "     </script>\n" .
	 
		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"group_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Group . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <select name=\"sgr_id\" " .
		 "onChange=\"javascript:document.searchForm.submit();\">\n" .
		 "         <option value=\"\">&nbsp;</option>\n" );

		foreach( $List_Groups as $Group ) {
			if ( $Group->sgr_id == $sgr_id ) $Status = ' selected ';
			else $Status = '';
		
			print( "         <option value=\"" . $Group->sgr_id . '"' . $Status . '>' .
			 $Group->sgr_label . "</option>\n" );
		}

		print( "        </select>\n" .
		 "       </p>\n" .
		 "      </div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"type_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Type . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <select name=\"stp_id\" " .
		 "onChange=\"javascript:document.searchForm.submit();\">\n" .
		 "         <option value=\"\">&nbsp;</option>\n" );
	 
		foreach( $List_Types as $Type ) {
			if ( $Type->stp_id == $stp_id ) $Status = ' selected ';
			else $Status = '';
		
			print( "         <option value=\"" . $Type->stp_id . '"' . $Status . '>' .
			 ${$Type->stp_name} . "</option>\n" );
		}

		print( "         </select>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
	 
		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"environment_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Environment . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <select name=\"env_id\" " .
		 "onChange=\"javascript:document.searchForm.submit();\">\n" .
		 "         <option value=\"\">&nbsp;</option>\n" );

		foreach( $List_Environments as $Environment ) {
			if ( $Environment->env_id == $env_id ) $Status = ' selected ';
			else $Status = '';
		
			print( "         <option value=\"" . $Environment->env_id . "\"" . $Status .
			 ">" . ${$Environment->env_name} . "</option>\n" );
		}

		print( "         </select>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <div style=\"clear:both;\"></div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"application_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Application . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <input type=\"text\" name=\"scr_application\" size=\"30\" maxlength=\"60\" " .
		 "value=\"" . $scr_application . "\" />\n" . 
		 "       </p>\n" .
		 "      </div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"host_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Host . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <input type=\"text\" name=\"scr_host\" size=\"30\" maxlength=\"255\" " .
		 "value=\"" . $scr_host . "\" />\n" . 
		 "       </p>\n" .
		 "      </div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"user_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_User . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <input type=\"text\" name=\"scr_user\" size=\"25\" maxlength=\"25\" " . 
		 "value=\"" . $scr_user . "\" />\n" . 
		 "       </p>\n" .
		 "      </div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"comment_criteria\">\n" .
		 "       <p class=\"subtitle pair\">" . $L_Comment . "</p>\n" .
		 "       <p class=\"impair\">\n" .
		 "        <input type=\"text\" name=\"scr_comment\" size=\"50\" maxlength=\"100\" " .
		 "value=\"" . $scr_comment . "\" />\n" . 
		 "       </p>\n" .
		 "      </div>\n" .

		 "      <div class=\"search_criteria\" style=\"display: none;\" id=\"search_buttons\">\n" .
		 "       <p class=\"subtitle space\">\n" .
		 "        <input type=\"submit\" class=\"button\" value=\"" . $L_Search . "\" />\n" . 
		 "        <a href=\"" . $Script . "\" class=\"button\">" . $L_Reset . "</a>\n" . 
		 "       </p>\n" .
		 "      </div>\n" .
	 
		 "      <div style=\"clear:both;height: 10px\"></div>\n" .
		 "    </form>\n" .
		 "    </div>\n" );
	}


	// =====================
	// Tableau des résultats
	if ( array_key_exists( 'searchSecret', $_POST ) ) {
		$searchSecret = $_POST[ 'searchSecret' ];
		$_SESSION[ 'searchSecret' ] = $searchSecret;
	} else {
		if ( array_key_exists( 'searchSecret', $_SESSION ) ) $searchSecret = $_SESSION[ 'searchSecret' ];
		else $searchSecret = '';
	}

	$myButtons = '';

	if ( $Authentication->is_administrator() or $groupsRights[ 'W' ] == 1 ) {
    	$addButton = '<a class="btn btn-small" href="' . URL_BASE . '/SM-secrets.php?action=SCR_A&rp=home" title="' . $L_Create . '"><i class="icon-plus"></i></a>';

		if ( $Search_Style == 2 ) {
		   	$addButton = '<form class="form-search simple" method="post" name="searchForm" action="' .
			 $Script . '?action=R2" >' .
		   	 '<div class="input-append">' .
			 '<input type="text" class="span2 search-query" name="searchSecret" value="' . $searchSecret . '" />' .
			 '<button type="submit" class="btn btn-small" title="' . $L_Search . '"><img class="no-border" src="' . URL_PICTURES . '/b_search.png" alt="'. $L_Search . 
			 '" /></button>' .
			 '</div>' .
			 $addButton .
			 '</form>';
		}
	} else {
		if ( $Search_Style == 2 ) {
		   	$addButton = '<form class="form-search simple" method="post" name="searchForm" action="' .
			 $Script . '?action=R2" >' .
		   	 '<div class="input-append">' .
			 '<input type="text" class="span2 search-query" name="searchSecret" value="' . $searchSecret . '" />' .
			 '<button type="submit" class="btn btn-small" title="' . $L_Search . '"><img class="no-border" src="' . URL_PICTURES . '/b_search.png" alt="'. $L_Search . 
			 '" /></button>' .
			 '</div>' .
			 '</form>';
		}
	}

	$myButtons = '<div style="float: right; display: inline;">' . $addButton . "</div>";

	print( "     <div id=\"scroller\"> <!-- Début : scroller -->\n" .
	 "     <table class=\"table-bordered\">\n" .
	 "      <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"8\">" . $L_List_Secrets . $myButtons . "</th>\n" .
	 "       </tr>\n" .
	 "      </thead>\n" .
	 "      <tbody>\n" );
		 
	if ( $Action != 'R2' && ! isset( $_SESSION[ 'searchSecret' ] ) ) {
		$List_Secrets = $Secrets->listSecrets( $sgr_id, $_SESSION[ 'idn_id' ], $stp_id,
		 $env_id, $scr_application, $scr_host, $scr_user, $scr_comment,
		 $Authentication->is_administrator(), $orderBy );
	} else {
		$List_Secrets = $Secrets->listSecrets2( $searchSecret, $_SESSION[ 'idn_id' ],
		 $Authentication->is_administrator(), $orderBy );
	}
	
	print( "       <tr class=\"pair\">\n" );
	
	if ( $orderBy == 'group' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'group-desc';
	} else {
		if ( $orderBy == 'group-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'group';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Group . "</th>\n" );
	 
	if ( $orderBy == 'type' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'type-desc';
	} else {
		if ( $orderBy == 'type-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'type';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Type . "</th>\n" );
	 
	if ( $orderBy == 'environment' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'environment-desc';
	} else {
		if ( $orderBy == 'environment-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'environment';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Environment . "</th>\n" );
	 
	if ( $orderBy == 'application' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'application-desc';
	} else {
		if ( $orderBy == 'application-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'application';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Application . "</th>\n" );
	 
	if ( $orderBy == 'host' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'host-desc';
	} else {
		if ( $orderBy == 'host-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'host';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Host . "</th>\n" );
	 
	if ( $orderBy == 'user' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'user-desc';
	} else {
		if ( $orderBy == 'user-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'user';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_User . "</th>\n" );
	 
	if ( $orderBy == 'comment' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'comment-desc';
	} else {
		if ( $orderBy == 'comment-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'comment';
	}
	print( "        <th onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Comment . "</th>\n" );


	print( "        <th>" . $L_Actions . "</th>\n" .
	 "       </tr>\n" );
		
	$BackGround = "pair";
		
	foreach( $List_Secrets as $Secret ) {
		if ( $BackGround == "pair" )
			$BackGround = "impair";
		else
			$BackGround = "pair";
			
		print( "       <tr class=\"" . $BackGround .
		 " surline\" style=\"cursor: pointer;\" >\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . $Security->XSS_Protection( $Secret->sgr_label ) . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . ${$Secret->stp_name} . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . ${$Secret->env_name} . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . $Security->XSS_Protection( $Secret->scr_application ) . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . $Security->XSS_Protection( $Secret->scr_host ) . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . $Security->XSS_Protection( $Secret->scr_user ) . "</td>\n" .
		 "        <td class=\"align-middle\" onclick=\"viewPassword( " . 
		 $Secret->scr_id . " );\">" . $Security->XSS_Protection( $Secret->scr_comment ) . "</td>\n" );
		
		print( "        <td>\n" );

		$Update_Right = 0;
		$Delete_Right = 0;

		if ( ! $Authentication->is_administrator() ) {
			if ( array_key_exists( $Secret->sgr_id, $groupsRights ) ) {
				$Update_Right = in_array( 3, $groupsRights[ $Secret->sgr_id ] );
				$Delete_Right = in_array( 4, $groupsRights[ $Secret->sgr_id ] );
			}
		}

		if ( $Action == 'R2' ) $Home = 'home-r2';
		else $Home = 'home';
		
		if ( $Authentication->is_administrator() or $Update_Right ) {
			print( "         <a class=\"simple\" href=\"" . URL_BASE .
			 "/SM-secrets.php?action=SCR_M&scr_id=" . $Secret->scr_id .
			 "&rp=" . $Home . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" .
			 $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" );
		}
		
		if ( $Authentication->is_administrator() or $Delete_Right ) {
			print( "         <a class=\"simple\" href=\"" . URL_BASE .
			 "/SM-secrets.php?action=SCR_D&scr_id=" . $Secret->scr_id .
			 "&rp=" . $Home . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" .
			 $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" );
		}

		print( "         <a class=\"simple\" href=\"javascript:viewPassword( " . 
		 $Secret->scr_id . " );\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_eye.png\" alt=\"" .
		 $L_Password_View . "\" title=\"" . $L_Password_View . "\" /></a>\n" );
		print( "        </td>\n" .
		 "       </tr>\n" );
	}
		
	print( "      </tbody>\n" .
	 "      <tfoot><tr><th colspan=\"8\">Total : <span class=\"green\">" . 
	 count( $List_Secrets ) . "</span>" . $myButtons . "</th></tr></tfoot>\n" .
	 "     </table>\n" .
	 "\n" .
	 "     </div> <!-- Fin : scroller -->\n" .
	 "    </div> <!-- fin : dashboard -->\n" );

   break;
}

print( "   </div> <!-- Fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( 1, 'home' ) .
 $PageHTML->piedPageHTML() );

?>
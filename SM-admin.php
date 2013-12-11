<?php

/**
* Ce script gère l'installation du SecretManager et du SecretServer.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.3
* @date 2013-11-05
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );
session_start();

// Par défaut langue Française.
if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'fr';

if ( array_key_exists( 'Lang', $_GET ) ) {
   $_SESSION[ 'Language' ] = $_GET[ 'Lang' ];
}
	
$Script = URL_BASE . $_SERVER[ 'SCRIPT_NAME' ];
$URI = $_SERVER[ 'REQUEST_URI' ];
$IP_Source = $_SERVER['REMOTE_ADDR'];

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );

$Action = '';
$Choose_Language = 0;


include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );

$PageHTML = new HTML();

if ( ! $PageHTML->is_connect() ) {
   header( 'Location: ' . URL_BASE . '/SM-login.php' );
	exit();
}


// Si l'utilisateur n'est pas Administrateur alors l'exécution du script est bloqué.
if ( ! $PageHTML->is_administrator() ) {
	$Javascripts = '';

	print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $Javascripts, $innerJS ) .
		"   <!-- debut : zoneTitre -->\n" .
		"   <div id=\"zoneTitre\">\n" .
		"    <div id=\"icon-home\" class=\"icon36\"></div>\n" .
		"    <span id=\"titre\">". $L_Title . "</span>\n" .
		$PageHTML->afficherActions( $PageHTML->is_administrator() ) .
		"    </div> <!-- Fin : zoneTitre -->\n" .
		"\n" .
		"   <!-- debut : zoneMilieuComplet -->\n" .
		"   <div id=\"zoneMilieuComplet\">\n" .
		$L_No_Authorize .
		"   </div> <!-- debut : zoneMilieuComplet -->\n" .
		$PageHTML->construireFooter( 1, 'home' ) .
		$PageHTML->piedPageHTML() );

	exit();
}


include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-backup.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

include( DIR_LIBRARIES . '/Class_IICA_Identities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Security.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Entities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Civilities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );


// Charge les différents objets utiles à cet écran.
$Identities = new IICA_Identities();

$Groups = new IICA_Groups();

$Secrets = new IICA_Secrets();

$Referentials = new IICA_Referentials();

$Security = new Security();

$Profiles = new IICA_Profiles();

$Entities = new IICA_Entities();

$Civilities = new IICA_Civilities();


// Récupère la liste des Droits, des Types et des Environnements.
$List_Rights = $Referentials->listRights();
$List_Types = $Referentials->listSecretTypes();
$List_Environments = $Referentials->listEnvironments();


// Récupère les Droits que cet utilisateur a sur les différents Groupes de Secrets.
$groupsRights = $PageHTML->getGroups( $_SESSION[ 'idn_id' ] );


// Contrôle si la session n'a pas expirée.
if ( ! $PageHTML->validTimeSession() ) {
	header( 'Location: ' . URL_BASE . '/SM-login.php?action=DCNX&expired' );
} else {
	$PageHTML->saveTimeSession();
}


// Si l'utilisateur n'est pas Administrateur alors il est bridé sur les Groupes de Secrets
// auxquels il a accès.
if ( ! $PageHTML->is_administrator() )
	$List_Groups = $Groups->listGroups( $_SESSION[ 'idn_id' ] );
else
	$List_Groups = $Groups->listGroups();


if ( array_key_exists( 'action', $_GET ) ) {
   $Action = strtoupper( $_GET[ 'action' ] );
}


// Informations sur l'historique.
$Tmp = $Secrets->totalHistoryEvents();
		
$Total_History = $Tmp->total ;
$First_Date_History = $Tmp->first_date;


$Javascripts = array( 'dashboard.js', 'Ajax_admin.js', 'SecretManager.js' );

// Cas de l'import des fonctions JS gérant les mots de passe.
if ( $Action == 'S' or $Action == 'SCR_M' )	include( DIR_LIBRARIES . '/password_js.php' );
else $innerJS = '';

if ( ! preg_match("/X$/i", $Action ) ) {
    print( $PageHTML->enteteHTML( $L_Title, $Choose_Language, $Javascripts, $innerJS ) .
     "   <!-- debut : zoneTitre -->\n" .
     "   <div id=\"zoneTitre\">\n" .
     "    <div id=\"icon-tools\" class=\"icon36\"></div>\n" .
     "    <span id=\"titre\">". $L_Title . "</span>\n" .
     $PageHTML->afficherActions( $PageHTML->is_administrator() ) .
     "    </div> <!-- Fin : zoneTitre -->\n" .
     "\n" .
     "   <div id=\"zoneMilieuComplet\"> <!-- debut : zoneMilieuComplet -->\n" .
     "   <div id=\"dashboard\"> <!-- debut : dashboard -->\n" .
     "\n" );
}

switch( $Action ) {
 default:
	print(
		 "     <!-- Début : affichage de la synthèse des utilisateurs -->\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"users\">" . $L_List_Users . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_users\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Users_Base . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Identities->total() . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Users_Disabled . " : </span>\n" .
		 "        <span class=\"green bold\">" . $Identities->totalDisabled() . "</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Users_Expired . " : </span>\n" .
		 "        <span class=\"green bold\">" . $Identities->totalExpired() . "</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Users_Attempted . " : </span>\n" .
		 "        <span class=\"green bold\">" . $Identities->totalAttempted() . "</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Users_Super_Admin . " : </span>\n" .
		 "        <span class=\"green bold\">" . $Identities->totalSuperAdmin() . "</span>\n" .
		 "       </p>\n" .
		 "      </div> <!-- Fin : corps -->\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"SM-users.php\">" . $L_Manage_Users . "</a></p>\n" .
		 "     </div> <!-- Fin : affichage de la synthèse des utilisateurs -->\n\n" );

	// ===========================================
	// Tableau d'affichage des Groupes de Secrets.
	print( "     <!-- Début : affichage de la synthèse des groupes -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"groups\">" . $L_List_Groups . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_groups\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Groups_Base . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Groups->total( '' ) . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"SM-secrets.php?rp=admin\">" . $L_Manage_Groups .
		 "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des groupes -->\n\n" );

	// ===========================================
	// Tableau d'affichage des Profils.
	print( "     <!-- Début : affichage de la synthèse des profils -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"profiles\">" . $L_List_Profiles . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_profiles\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Profiles_Base . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Profiles->total() . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"SM-users.php?action=PRF_V&rp=admin\">" . 
		 $L_Manage_Profiles . "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des groupes -->\n\n" );

	// ===========================================
	// Tableau d'affichage des Entités.
	print( "     <!-- Début : affichage de la synthèse des entités -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"entities\">" . $L_List_Entities . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_entities\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Entities_Base . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Entities->total() . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"SM-users.php?action=ENT_V&rp=admin\">" . 
		 $L_Manage_Entities . "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des entités -->\n\n" );

	// ===========================================
	// Tableau d'affichage des Civilités.
	print( "     <!-- Début : affichage de la synthèse des civilités -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"civilities\">" . $L_List_Civilities . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_civilities\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Entities_Base . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Civilities->total() . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"SM-users.php?action=CVL_V&rp=admin\">" . 
		 $L_Manage_Civilities . "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse des civilités -->\n\n" );

	// ===========================================
	// Tableau de synthèse de l'Historique.
	print( "     <!-- Début : affichage de la synthèse de l'historique -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"historique\">" . $L_Historical_Records_Management . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_historique\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Total_Historical_Records . " : </span>\n" .
		 "        <span class=\"bg-green bold\">&nbsp;" . $Total_History . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Oldest_Date_History . " : </span>\n" .
		 "        <span class=\"green bold\">&nbsp;" . $First_Date_History . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"" . $Script . "?action=H\">" .
		 $L_Manage_Historical . "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse de l'historique -->\n\n" );


    $_UseSecretServer = $PageHTML->getParameter( 'use_SecretServer' );
    
    if ( $_UseSecretServer == 1 ) {
        // Informations sur le SecretServer.
        $Secret_Server = new Secret_Server();

        try {
        	list( $Status, $Operator, $Creating_Date ) = $Secret_Server->SS_statusMotherKey();
        } catch( Exception $e ) {
        	$Status = $e->getMessage();
        	$Operator = '';
        	$Creating_Date = '';
        }

        if ( $Status == 'OK' ) {
        	$Status_Server = $L_MOTHER_KEY_LOADED;
        	$Color = 'green';
        } else {
        	$Status_Server = ${$Status};
        	$Color = 'orange';
        }
        
        $UseSecretServer = $L_Yes;
    } else {
        $UseSecretServer = $L_No;
        $Color = 'orange';
    }
    

	// ===========================================
	// Tableau de synthèse du SecretServer.
	print( "     <!-- Début : affichage de la synthèse du SecretServer -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"secretserver\">" . $L_SecretServer_Management . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_secretserver\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Use_SecretServer . " : </span>\n" .
		 "        <span class=\"bg-".$Color." bold\">&nbsp;" . $UseSecretServer . "&nbsp;</span>\n" .
		 "       </p>\n" );
		 
    if ( $_UseSecretServer == 1 ) {
    	print( "       <p>\n" . 
	         "        <span>" . $L_Status . " : </span>\n" .
		     "        <span class=\"bg-".$Color." bold\">&nbsp;" . $Status_Server . "&nbsp;</span>\n" .
    		 "       </p>\n" .
	    	 "       <p>\n" .
		     "        <span>" . $L_Operator . " : </span>\n" .
    		 "        <span class=\"green bold\">" . $Operator . "</span>\n" .
	    	 "       </p>\n" .
		     "       <p>\n" .
    		 "        <span>" . $L_Creation_Date . " : </span>\n" .
	    	 "        <span class=\"green bold\">" . $Creating_Date . "</span>\n" .
		     "       </p>\n" );
	}

    print( "      </div>\n" );
    
    if ( $_UseSecretServer == 1 ) {
    	print( "      <p class=\"align-center\"><a class=\"button\" href=\"" . $Script . "?action=S\">" .
		     $L_Manage_SecretServer . "</a></p>\n" );
	}
		 
	print( "     </div>\n" .
		 "     <!-- Fin : affichage de la synthèse du SecretServer -->\n\n" );

	// ===========================================
	// Tableau de gestion des sauvegardes du SecretManager.
    $Backup_Secrets_Date = $PageHTML->getParameter( 'Backup_Secrets_Date' );
    $Backup_Total_Date = $PageHTML->getParameter( 'Backup_Total_Date' );

    $Backup_Date_1 = date_create( $Backup_Secrets_Date );
    $Backup_Date_2 = date_create( $Backup_Total_Date );
    $Current_Date = date_create( date('Y-m-d') );
    
    $Interval = date_diff($Current_Date,$Backup_Date_1);

    if ( $Interval->format('%R%a') >= '-30' ) $BS_Color = 'green';
    else $BS_Color = 'orange';
    
    $Interval = date_diff($Current_Date,$Backup_Date_2);

    if ( $Interval->format('%R%a') >= '-30' ) $BT_Color = 'green';
    else $BT_Color = 'orange';
	
	print( "     <!-- Début : affichage de la sauvegarde du SecretManager -->\n\n" .
		 "     <div class=\"tableau_synthese\">\n" .
		 "      <p class=\"titre\" id=\"storage\">" . $L_Backup_Management . "</p>\n" .
		 "      <div class=\"corps\" id=\"c_secretserver\">\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Last_Secrets_Backup . " : </span>\n" .
		 "        <span class=\"bg-".$BS_Color." bold\">&nbsp;" . $Backup_Secrets_Date . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "       <p>\n" .
		 "        <span>" . $L_Last_Total_Backup . " : </span>\n" .
		 "        <span class=\"bg-".$BT_Color." bold\">&nbsp;" . $Backup_Total_Date . "&nbsp;</span>\n" .
		 "       </p>\n" .
		 "      </div>\n" .
		 "      <p class=\"align-center\"><a class=\"button\" href=\"" . $Script . "?action=STOR\">" .
		 $L_Manage_Backup . "</a></p>\n" .
		 "     </div>\n" .
		 "     <!-- Fin : affichage de la sauvegarde du SecretManager -->\n\n" .

		 "     <div style=\"clear: both;\"></div>\n" );

	break;


 // ========================
 // Gestion de l'Historique
 case 'H':
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
     "      <table class=\"table-bordered\" style=\"margin:10px auto;width:98%\">\n" .
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
     "        <th class=\"align-right\"><a id=\"search_icon\" class=\"simple-selected\" style=\"cursor: pointer;\" onclick=\"javascript:hiddeRow();\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_search.png\" alt=\"" . $L_Search . "\" title=\"" . $L_Search . "\"></a></th>\n" .
     "       </tr>\n" .
     "       <tr style=\"display: none;\" id=\"r_search\" class=\"pair\">\n" .
     "        <td><input type=\"text\" name=\"scr_id\" class=\"input-mini\" " .
     "value=\"" .  $scr_id . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" );
     
    print( "        <td>\n" .
     "         <select name=\"idn_id\" class=\"input-small\" onChange=\"document.getElementById( 'i_historical' ).submit();\">\n" .
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
    
    print( "        <td><input type=\"text\" name=\"h_date\" class=\"input-small\" " .
     "value=\"" . $h_date . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
     "        <td><input type=\"text\" name=\"ip_source\" class=\"input-small\" " .
     "maxlength=\"40\" value=\"" . $ip_source . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
     "        <td class=\"align-middle\"><input type=\"text\" class=\"input-xlarge\" name=\"message\" " .
     "value=\"" . $message . "\" onChange=\"document.getElementById( 'i_historical' ).submit();\" /></td>\n" .
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
     "<a class=\"btn\" href=\"" . $Script . "?action=H&start=0&size=" . $size . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_premier.gif\" alt=\"First\" /></a>" .
     "<a class=\"btn\" href=\"?action=H&start=" . $previous . "&size=" . $size . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_precedent.gif\" alt=\"Previous\" /></a>" .
     "&nbsp;" . ($start + 1) . "&nbsp;/&nbsp;" . ($start + $size) . "&nbsp;" .
     "<a class=\"btn\" href=\"?action=H&start=" . $next . "&size=" . $size . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_suivant.gif\" alt=\"Next\" /></a>" .
     "<a class=\"btn\" href=\"?action=H&start=" . ( $Total - $size ) . "&size=" . $size . "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_dernier.gif\" alt=\"Last\" /></a>" .
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
     "       <input type=\"submit\" class=\"button\" value=\"Purge\" /></p>" .
     "     </form>\n" .
     "     <p class=\"align-center\"><a class=\"button\" href=\"". URL_BASE . "/SM-admin.php\">" .
     $L_Return . "</a></p>\n" );
    
    break;

 case 'HX':
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
     "<a id=\"search_icon\" class=\"simple-selected\" style=\"cursor: pointer;\" onclick=\"javascript:hiddeRow();\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_search.png\" alt=\"" . $L_Search . "\" title=\"" . $L_Search . "\"></a></span></th>\n" .
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


 case 'PH': // Confirme la purge à réaliser.
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


 case 'PHX': // Purge effective de l'historique.
    if ( array_key_exists( 'purge_date', $_POST ) ) {
        $purge_date = $_POST[ 'purge_date' ];
    } else {
        print( $PageHTML->infoBox( $L_No_Purge_Date, $Script . '?action=H' ) );

        exit();
    }
    
    try {
        $Secrets->purgeHistoryEvents( $purge_date );
    } catch( Exception $e ) {
        $alert_message = $Secrets->formatHistoryMessage( $e->getMessage() );

        $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message,
         $IP_Source );
        
        print( $PageHTML->infoBox( $e->getMessage(), $Script . '?action=H', 1 ) );

        exit();
    }

    $Message = sprintf( $L_Success_Purge, $purge_date );
    
    $alert_message = $Secrets->formatHistoryMessage( $Message );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

    print( "<form method=\"post\" name=\"fMessage\" action=\"" . $Script . "?action=H\">\n" .
        " <input type=\"hidden\" name=\"iMessage\" value=\"" . htmlentities( $Message ) . "\" />\n" .
        "</form>\n" .
        "<script>document.fMessage.submit();</script>" );
    
    break;


 case 'S':
    // Informations sur le SecretServer.
    $Secret_Server = new Secret_Server();

    try {
        list( $Status, $Operator, $Creating_Date ) = $Secret_Server->SS_statusMotherKey();
    } catch( Exception $e ) {
        $Status = $e->getMessage();
    }

    if ( $PageHTML->getParameter( 'use_SecretServer' ) == '1' ) {
        $Select_Yes = 'selected';
        $Select_No = '';
    } else {
        $Select_Yes = '';
        $Select_No = 'selected';
    }

    $Operator_Key_Size = $PageHTML->getParameter( 'Operator_Key_Size' );
    $Operator_Key_Complexity = $PageHTML->getParameter( 'Operator_Key_Complexity' );

    $Mother_Key_Size = $PageHTML->getParameter( 'Mother_Key_Size' );
    $Mother_Key_Complexity = $PageHTML->getParameter( 'Mother_Key_Complexity' );

    print(
     "      <table class=\"table-bordered\" style=\"margin:10px auto;width:80%\">\n" .
     "       <thead>\n" .
     "       <tr>\n" .
     "        <th colspan=\"2\">" . $L_SecretServer_Management . "</th>\n" .
     "       </tr>\n" .
     "       </thead>\n" .
     
     "       <tbody>\n" .
     "       <tr>\n" .
     "        <td class=\"pair align-right\">" . $L_Status . "</td>\n" .
     "        <td class=\"pair\" id=\"iSecretServerStatus\" " .
     "data-operator=\"" . $L_Operator . "\" " . 
     "data-date-crea=\"" . $L_Creation_Date . "\" " . 
     "data-mk-loaded=\"" . $L_MOTHER_KEY_LOADED . "\" " . 
     ">\n" );
    
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
        print( '         <span class="bold bg-orange">&nbsp;' . ${$Status} . "&nbsp;</span>\n" );
    }

    print( "        </td>\n" .
     "       </tr>\n" .
     "       <tr>\n" .
     "        <td class=\"pair align-right align-middle\">" .
     $L_Load_Mother_Key . "</td>\n" .
     "        <td class=\"pair\">\n" .
     "         <table>\n" .
     "          <tr>\n" .
     "           <td class=\"pair\">" . $L_Insert_Operator_Key . "</td>\n" .
     "           <td><input type=\"text\" id=\"iOperator_Key\" /></td>\n" .
     "           <td><a href=\"javascript:LoadMotherKey();\" class=\"button\">" . $L_Load . "</a></td>\n" .
     "          </tr>\n" .
     "         </table>\n" .
     "        </td>\n" .
     "       </tr>\n" .
     "       <tr>\n" .
     "        <td class=\"pair align-right align-middle\">" .
     $L_Transcrypt_Mother_Key . "</td>\n" .
     "        <td class=\"pair\">\n" .
     "         <form class=\"form-search simple\" method=\"post\" " .
     "action=\"javascript:generatePassword( 'iNew_Operator_Key_1', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . " );checkPassword('iNew_Operator_Key_1', 'Result_1', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\">\n" .
     "         <table>\n" .
     "          <tr>\n" .
     "           <td class=\"pair\">" . $L_Insert_New_Operator_Key . "</td>\n" .
     "           <td>\n" .
     "            <div class=\"input-append\">\n" .
     "             <input type=\"text\" class=\"search-query input-large\" " .
     "id=\"iNew_Operator_Key_1\" name=\"New_Operator_Key\" " .
     "onFocus=\"checkPassword('iNew_Operator_Key_1', 'Result_1', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\" " .
     "onKeyup=\"checkPassword('iNew_Operator_Key_1', 'Result_1', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\" />\n" .
     "             <button type=\"submit\" class=\"btn btn-small\">" . $L_Generate . "</button>\n" .
     "            </div>\n" .
     "<img id=\"Result_1\" class=\"no-border\" width=\"16\" height=\"16\" " .
     "src=\"https://secretmanager.localhost/Pictures/blank.gif\" alt=\"Ok\">\n" .
     "           </td>\n" .
     "          </tr>\n" .
     "          <tr>\n" .
     "           <td class=\"pair\">&nbsp;</td>\n" .
     "           <td><a href=\"#\" class=\"button\" id=\"iSaveNewOeratorKey_1\" " .
     "data-cancel-op=\"".$L_Operation_Cancel_Not_Given_O_Key."\" " .
     "data-warning=\"".$L_Warning."\" " .
     "data-confirm=\"".$L_Confirm."\" " .
     "data-cancel=\"".$L_Cancel."\" " .
     "data-text-1=\"".$L_Warning_Transcrypt_mother_key."\">" . $L_Transcrypt . "</a></td>\n" .
     "          </tr>\n" .
     "         </table>\n" .
     "         </form>\n" .
     "        </td>\n" .
     "       </tr>\n" .

     "       <tr>\n" .
     "        <td class=\"pair align-right align-middle\">" .
     $L_New_Mother_Key . "</td>\n" .
     "        <td class=\"pair\">\n" .
     "          <table>\n" .
     "           <tr>\n" .
     "            <td class=\"pair\">" . $L_Insert_Operator_Key . "</td>\n" .
     "            <td>\n" .
     "             <form class=\"form-search simple\" method=\"post\" " .
     "action=\"javascript:generatePassword( 'iNew_Operator_Key_2', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . " );checkPassword('iNew_Operator_Key_2', 'Result_2', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\">\n" .
     "             <div class=\"input-append\">\n" .
     "              <input type=\"text\" class=\"search-query input-large\" " .
     "id=\"iNew_Operator_Key_2\" name=\"New_Operator_Key\" " .
     "onFocus=\"checkPassword('iNew_Operator_Key_2', 'Result_2', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\" " .
     "onKeyup=\"checkPassword('iNew_Operator_Key_2', 'Result_2', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\" />\n" .
     "              <button type=\"submit\" class=\"btn btn-small\">" .
     $L_Generate . "</button>\n" .
     "             </div>\n" .
     "<img id=\"Result_2\" class=\"no-border\" width=\"16\" height=\"16\" " .
     "src=\"https://secretmanager.localhost/Pictures/blank.gif\" alt=\"Ok\">\n" .
     "             </form>\n" .
     "            </td>\n" .
     "           </tr>\n" .
     "           <tr>\n" .
     "            <td class=\"pair\">" . $L_Insert_New_Mother_Key . "</td>\n" .
     "            <td>\n" .
     "             <form class=\"form-search simple\" method=\"post\" " .
     "action=\"javascript:generatePassword( 'iNew_Mother_Key', " . $Mother_Key_Complexity .
     ", " . $Mother_Key_Size . " );checkPassword('iNew_Mother_Key', 'Result_3', " . $Mother_Key_Complexity .
     ", " . $Mother_Key_Size . ");\">\n" .
     "             <div class=\"input-append\">\n" .
     "              <input type=\"text\" class=\"search-query input-large\" " .
     "id=\"iNew_Mother_Key\" name=\"New_Mother_Key\" " .
     "onFocus=\"checkPassword('iNew_Mother_Key', 'Result_3', " . $Operator_Key_Complexity .
     ", " . $Operator_Key_Size . ");\" " .
     "onKeyup=\"checkPassword('iNew_Mother_Key', 'Result_3', " . $Mother_Key_Complexity .
     ", " . $Mother_Key_Size . ");\"/>\n" .
     "              <button type=\"submit\" class=\"btn btn-small\">" . $L_Generate . "</button>\n" .
     "             </div>\n" .
     "<img id=\"Result_3\" class=\"no-border\" width=\"16\" height=\"16\" " .
     "src=\"https://secretmanager.localhost/Pictures/blank.gif\" alt=\"Ok\">\n" .
     "             </form>\n" .
     "            </td>\n" .
     "           </tr>\n" .
     "           <tr>\n" .
     "            <td class=\"pair\">&nbsp;</td>\n" .
     "            <td>\n" .
     "             <a href=\"javascript:confirmChangeMotherKey();\" class=\"button\" id=\"iChangeMotherKey\" " .
     "data-text=\"" . $L_Warning_Change_Mother_Key . "\">" . $L_Transcrypt .
     "</a>&nbsp;\n" .
     "             <a href=\"javascript:confirmCreateMotherKey();\" class=\"button\" " .
     "data-cancel-operation=\"" . $L_Operation_Cancel_Not_Given_Keys . "\" " .
     "data-text=\"" . $L_Warning_Create_Mother_Key . "\" " .
     "id=\"iCreateMotherKey\">" . $L_Create . "</a>\n" .
     "            </td>\n" .
     "           </tr>\n" .
     "          </table>\n" .
     "         </form>\n" .
     "        </td>\n" .
     "       </tr>\n" .
     "       <tr>\n" .
     "        <td class=\"pair align-right align-middle\" width=\"30%\">" . $L_Shutdown_SecretServer . "</td>\n" .
     "        <td class=\"pair\">\n" .
     "          <p><a href=\"javascript:shutdownSecretServer();\" class=\"button\" " .
     "id=\"iShutdownSecretServer\" " .
     "data-text=\"".$L_ERR_SERVER_NOT_STARTED."\" >" . $L_Execute . "</a></p>\n" .
     "        </td>\n" .
     "       </tr>\n" .
     "       </tbody>\n" .
     "       <tfoot>\n" .
     "       <tr colspan=\"2\">\n" .
     "        <th class=\"align-right align-middle\" width=\"30%\">&nbsp;</th>\n" .
     "        <th>\n" .
     "         <a class=\"button\" href=\"" . URL_BASE . "/SM-admin.php\">" . $L_Return . "</a>\n" .
     "        </th>\n" .
     "       </tr>\n" .
     "       </tfoot>\n" .
     "      </table>\n" 
    );
    
    break;


 // ========================================
 // Réponses aux requêtes AJAX.
 
 case 'LKX':  // Charge la clé Mère après récupération de la clé Opérateur.
    $Secret_Server = new Secret_Server();

    try {
        $Result = $Secret_Server->SS_loadMotherKey( $_POST[ 'Operator_Key' ] );
        
        if ( isset( ${$Result[0]} ) ) {
            $Message = ${$Result[0]};
            $Operator = $Result[1];
            $Date = $Result[2];
        } else {
            $Message = '';
            $Operator = '';
            $Date = '';
        }
        
        $Status = 'success';
    } catch( Exception $e ) {
        $Result = ${$e->getMessage()};
        $Status = 'error';
    }

    $alert_message = $Secrets->formatHistoryMessage( $Result[0] );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
        
    echo json_encode( array( 'Message' => $Message, 'Status' => $Status,
        'Operator' => $Operator, 'Date' => $Date ) );
    exit();
    
    break;


 case 'TMKX': // Transchiffre la clé Mère avec une nouvelle clé Operateur.
    include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
    
    $Secret_Server = new Secret_Server();

    try {
        $Status = $Secret_Server->SS_transcryptMotherKey( $_POST[ 'Operator_Key' ] );
        $Message = ${$Status[1]};
        
        echo json_encode( array( 'Status' => 'success', 'Message' => $Message ) );
    } catch( Exception $e ) {
        echo json_encode( array( 'Status' => 'error', 'Message' => ${$e->getMessage()} ) );
    }
    
    exit();

    break;


 case 'CMKX':
    $Secret_Server = new Secret_Server();

    try {
        $Operator_Key = $_POST[ 'Operator_Key' ];
        $Mother_Key = $_POST[ 'Mother_Key' ];

        list( $File, $C_Date, $Operator_Key_2, $Mother_Key_2 ) = $Secret_Server->SS_createMotherKey(
            $Operator_Key, $Mother_Key );

        $C_Date = date( 'Y-m-d H:i:s', (int)$C_Date );
//        print( $File.', '.$C_Date.', '.$Operator_Key_2.', '.$Mother_Key_2 );
        
        // Faire une page imprimable qui récapitule les informations créées.
        $Message = $L_Success_Page .
         "<table class=\"table-bordered\">\n" .
         " <thead>\n" .
         " <tr>\n" .
         "  <th colspan=\"2\">" . $L_New_Keys_Created . "</td>\n" .
         " </tr>\n" .
         " </thead>\n" .
         " <tbody>\n" .
         " <tr>\n" .
         "  <td class=\"align-right impair\">" . $L_Operator_Key . "</td>\n" .
         "  <td class=\"align-left pair\">" . $Operator_Key_2 . "</td>\n" .
         " </tr>\n" .
         " <tr>\n" .
         "  <td class=\"align-right impair\">" . $L_Mother_Key . "</td>\n" .
         "  <td class=\"align-left pair\">" . $Mother_Key_2 . "</td>\n" .
         " </tr>\n" .
         " <tr>\n" .
         "  <td class=\"align-right impair\">" . $L_Creation_Date . "</td>\n" .
         "  <td class=\"align-left pair\">" . $C_Date . "</td>\n" .
         " </tr>\n" .
         "</table>\n";
        
        $Status = 'success';
        $Result = $L_New_Keys_Created;
    } catch( Exception $e ) {
        $Status = 'error';
        $Result = ${$e->getMessage()};
        $Message = $Result;
    }

    $alert_message = $Secrets->formatHistoryMessage( $Result );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );
    
    echo json_encode( array( 'Status' => $Status, 'Message' => $Message ) );

    exit();

 case 'SHUTX':
    $Secret_Server = new Secret_Server();

    try {
        $Result = $Secret_Server->SS_Shutdown();
        $Result = 'SecretServer ' . $Result;
        $Status = 'success';
    } catch( Exception $e ) {
        $Result = ${$e->getMessage()};
        $Status = 'error';
    }

    $alert_message = $Secrets->formatHistoryMessage( $Result );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

    echo json_encode( array( 'Status' => $Status, 'Message' => $Result ) );

    exit();


case 'STOR':
    print(
     "      <table class=\"table-bordered\" style=\"margin:10px auto;width:70%\">\n" .
     "       <thead>\n" .
     "       <tr>\n" .
     "        <th colspan=\"2\">" . $L_Backup_Management . "</th>\n" .
     "       </tr>\n" .
     "       </thead>\n" .
     
     "       <tbody>\n" .
     "       <tr>\n" .
     "        <td class=\"pair align-right\"><a class=\"button\" href=\"javascript:backupSecrets();\">" . $L_Secrets_Backup . "</a></td>\n" .
     "        <td class=\"pair\">" . $L_Last_Secrets_Backup . "</td>\n" .
     "        <td class=\"pair bold\" id=\"iDateBackup\">" . $PageHTML->getParameter( 'Backup_Secrets_Date' ) . "</td>\n" .
     "       </tr>\n" .
     "       <tr>\n" .
     "        <td class=\"impair align-right\"><a class=\"button\" href=\"javascript:backupTotal();\">" . $L_Total_Backup . "</a></td>\n" .
     "        <td class=\"impair\">" . $L_Last_Total_Backup . "</td>\n" .
     "        <td class=\"impair bold\" id=\"iTotalDateBackup\">" . $PageHTML->getParameter( 'Backup_Total_Date' ) . "</td>\n" .
     "       </tr>\n" .
     "       </tbody>\n" .
     "       <tfoot>\n" .
     "       <tr>\n" .
     "        <th colspan=\"2\" class=\"align-center\"><a class=\"button\" href=\"" . $Script . "\">" . 
     $L_Return . "</a></th>\n" .
     "       </tr>\n" .
     "       </tfoot>\n" .
     "      </table\n"
     );
     
     break;


case 'STOR_SX':
    include( DIR_LIBRARIES . '/Class_Backup_PDO.inc.php' );
    
    $Backup = new Backup();
    
    try {
        $Date_Backup = $Backup->backup_secrets();
        $Result = $L_Backup_Secrets_Successful;
        $Status = 'success';
    } catch( Exception $e ) {
        $Result = $e->getMessage();
        $Status = 'error';
    }

    $PageHTML->setParameter( 'Backup_Secrets_Date', $Date_Backup );

    $alert_message = $Secrets->formatHistoryMessage( $Result );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

    echo json_encode( array( 'Status' => $Status, 'Message' => $Result,
        'Date' => $Date_Backup ) );

    exit();


case 'STOR_TX':
    include( DIR_LIBRARIES . '/Class_Backup_PDO.inc.php' );
    
    $Backup = new Backup();
    
    try {
        $Date_Backup = $Backup->backup_total();
        $Result = $L_Backup_Total_Successful;
        $Status = 'success';
    } catch( Exception $e ) {
        $Result = $e->getMessage();
        $Status = 'error';
    }

    $PageHTML->setParameter( 'Backup_Total_Date', $Date_Backup );

    $alert_message = $Secrets->formatHistoryMessage( $Result );

    $Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message, $IP_Source );

    echo json_encode( array( 'Status' => $Status, 'Message' => $Result,
        'Date' => $Date_Backup ) );

    exit();
}

print( "    </div> <!-- Fin : dashboard -->\n" .
 "   </div> <!-- Fin : zoneMilieuComplet -->\n" .
 "   <div id=\"afficherSecret\" class=\"tableau_synthese hide modal\" style=\"top:50%;left:40%;\">\n".
 "    <button type=\"button\" class=\"close\">×</button>\n".
 "    <p class=\"titre\">".$L_Secret_View."</p>\n".
 "    <div id=\"detailSecret\" style=\"margin:6px;padding:6px;min-width:150px;\" class=\"corps vertical-align align-center\"></div>\n" .
 "   </div> <!-- Fin : afficherSecret -->\n" .
 $PageHTML->construireFooter( 1, 'home' ) .
 $PageHTML->piedPageHTML() );

?>
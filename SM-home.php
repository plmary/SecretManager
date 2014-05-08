<?php

/**
* Ce script gère l'affichage des options auxquelles à droit l'utilisateur.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2013-11-22
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

// Force l'utilisateur à travailler en HTTPS.
if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: ' . URL_BASE . $URI );


$Action = '';
$Choose_Language = 0;


// Tailles des colonnes.
$S_Group = '210';
$S_Type = '90';
$S_Environment = '100';
$S_Application = '100';
$S_Host = '70';
$S_User = '70';
$S_Expiration_Date = '80';
$S_Comment = '110';
$S_Action = '80';


// Création du gestionnaire de page ainsi que du contexte.
include( DIR_LIBRARIES . '/Class_HTML.inc.php' );

$PageHTML = new HTML();


// Si l'utilisateur n'est pas passé par le processus de connexion, il est rerouté vers ce dernier.
if ( ! $PageHTML->is_connect() ) {
   header( 'Location: ' . URL_BASE . '/SM-login.php' );
	exit();
}


include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );


// Charge le fichier de "Hash", dans la mesure où le SecretServer ne sera pas utilisé.
include( DIR_LIBRARIES . '/Config_Hash.inc.php' );

include( DIR_LIBRARIES . '/Class_IICA_Identities_PDO.inc.php' );
include( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );


// Charge les différents objets utiles à cet écran.
$Identities = new IICA_Identities();

$Groups = new IICA_Groups();

$Secrets = new IICA_Secrets();

$Referentials = new IICA_Referentials();


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


function construireListe( $Search_Secrets, $orderBy = '', $Action = '' ) {
	include_once( DIR_LIBRARIES . '/Class_IICA_Secrets_PDO.inc.php' );
	include_once( DIR_LIBRARIES . '/Class_HTML.inc.php' );
	include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );

	$Security = new Security();

	$PageHTML = new HTML();

	$Secrets = new IICA_Secrets();

	// Lance la recherche à partir des critères.
	$List_Secrets = $Secrets->listSecrets2( $Search_Secrets, $_SESSION[ 'idn_id' ],
		$PageHTML->is_administrator(), $orderBy );

	$Total = count( $List_Secrets );

	foreach( $List_Secrets as $Secret ) {

		print( "       <tr class=\"surline\" id=\"" . $Secret->scr_id . "\" " .
		 "style=\"cursor: pointer;\" data-total=\"" . $Total . "\" " .
		 "data-cancel=\"" . $GLOBALS['L_Cancel'] . "\" data-modify=\"" . $GLOBALS['L_Modify'] . "\" " .
		 "data-delete=\"" . $GLOBALS['L_Delete'] . "\">\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Group'] ."px; " .
		 "width:". $GLOBALS['S_Group'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\" data-id=\"" . $Secret->sgr_id . "\">" . 
		 $Security->XSS_Protection( $Secret->sgr_label ) . "</td>\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Type'] ."px; " .
		 "width:". $GLOBALS['S_Type'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\" data-id=\"" . $Secret->stp_id . "\">" . ${$Secret->stp_name} . "</td>\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Environment'] ."px; " .
		 "width:". $GLOBALS['S_Environment'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\" data-id=\"" . $Secret->env_id . "\">" . ${$Secret->env_name} . "</td>\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Application'] ."px; " .
		 "width:". $GLOBALS['S_Application'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\" data-id=\"" . $Secret->app_id . "\">" . $Security->XSS_Protection( $Secret->app_name ) . "</td>\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Host'] ."px; " .
		 "width:". $GLOBALS['S_Host'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\">" . $Security->XSS_Protection( $Secret->scr_host ) . "</td>\n" .
		 "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_User'] ."px; " .
		 "width:". $GLOBALS['S_User'] ."px;\" onclick=\"viewPassword(" . 
		 $Secret->scr_id . ");\">" . $Security->XSS_Protection( $Secret->scr_user ) . "</td>\n" );

		$Date_1 = new DateTime('now');
		$Date_2 = new DateTime($Secret->scr_expiration_date);
		$Interval = $Date_1->diff($Date_2);

		if ( $Secret->scr_expiration_date == '' or $Secret->scr_expiration_date == '0000-00-00 00:00:00' ) {
			$Secret->scr_expiration_date = '';
			$myClass = '';
		} else {
			$myClass = '';

			if ($Interval->format('%R%a') <= '+7') {
				$myClass = 'btn-warning ';
			}

			if ($Interval->format('%R%a') < '+2') {
				$myClass = 'btn-danger ';
			}
		}

		 print( "        <td class=\"".$myClass."align-middle\" style=\"max-width:". $GLOBALS['S_Expiration_Date'] ."px; " .
		  "width:". $GLOBALS['S_Expiration_Date'] ."px;\" onclick=\"viewPassword(" . 
		  $Secret->scr_id . ");\">" . $Security->XSS_Protection( $Secret->scr_expiration_date ) . "</td>\n" .
		  "        <td class=\"align-middle\" style=\"max-width:". $GLOBALS['S_Comment'] ."px; " .
		  "width:". $GLOBALS['S_Comment'] ."px;\" onclick=\"viewPassword(" . 
		  $Secret->scr_id . ");\">" . $Security->XSS_Protection( $Secret->scr_comment ) . "</td>\n" );

		$Update_Right = 0;
		$Delete_Right = 0;

		$groupsRights = $GLOBALS['groupsRights'];

		if ( ! $PageHTML->is_administrator() ) {
			if ( array_key_exists( $Secret->sgr_id, $groupsRights ) ) {
				$Update_Right = in_array( 3, $groupsRights[ $Secret->sgr_id ] );
				$Delete_Right = in_array( 4, $groupsRights[ $Secret->sgr_id ] );
			}
		}

		if ( $Action == 'R2' ) $Home = 'home-r2';
		else $Home = 'home';

		$Buttons = '';
		$B_Rights = '';
		
		if ( $PageHTML->is_administrator() or $Update_Right ) {
			$Buttons .= "         <a class=\"simple\" href=\"javascript:setSecret(" . $Secret->scr_id .
			 ")\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" .
			 $GLOBALS['L_Modify'] . "\" title=\"" . $GLOBALS['L_Modify'] . "\" /></a>\n";

			$B_Rights .= 'M';
		}
		
		if ( $PageHTML->is_administrator() or $Delete_Right ) {
			$Buttons .= "         <a class=\"simple\" href=\"javascript:setSecret(" . $Secret->scr_id .
			 ", 'D')\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" .
			 $GLOBALS['L_Delete'] . "\" title=\"" . $GLOBALS['L_Delete'] . "\" /></a>\n";

			$B_Rights .= 'D';
		}

		$Buttons .= "         <a class=\"simple\" href=\"javascript:viewPassword( " . 
		 $Secret->scr_id . " );\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_eye.png\" alt=\"" .
		 $GLOBALS['L_Password_View'] . "\" title=\"" . $GLOBALS['L_Password_View'] . "\" /></a>\n";
		
		print( "        <td style=\"max-width:". $GLOBALS['S_Action'] ."px; width:". $GLOBALS['S_Action'] ."px;\" data-right=\"" . $B_Rights . "\">\n" .
		 $Buttons .
		 "        </td>\n" .
		 "       </tr>\n" );
	}

	return $Total;
}


switch( $Action ) {
 case 'R': // Fonction de Recherche.
 	if ( $_POST[ 'Search_Secrets' ] != '' ) {
	 	$Search_Secrets = $_POST[ 'Search_Secrets' ];
 	} else {
 		$Search_Secrets = '';
 	}

	construireListe( $Search_Secrets, $orderBy );

 	exit();


 default: // Affichage des secrets.
    include( DIR_LIBRARIES . '/password_js.php' );
	$Javascripts = array( 'Ajax_secrets.js', 'Ajax_home.js', 'SecretManager.js' );

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
	 "\n" );

	print( "    <div id=\"dashboard\">\n\n" );

	if ( $PageHTML->is_administrator() ) $idn_id = '';
	else $idn_id = $_SESSION[ 'idn_id' ];
	
	// A la première connexion, on affiche les informations relatives à la dernière connexion.
	if ( array_key_exists( 'last_login', $_GET ) ) {
		print( 
		 "<script>\n" .
		 "     var myVar=setInterval(function(){cacherInfo()},9000);\n" .
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


	// =====================
	// Tableau des résultats

	// Prend en compte le critère de recherche.
	if ( array_key_exists( 'searchSecret', $_POST ) ) {
		$Search_Secrets = $_POST[ 'searchSecret' ];
		$_SESSION[ 'searchSecret' ] = $Search_Secrets;
	} else {
		if ( array_key_exists( 'searchSecret', $_SESSION ) ) $Search_Secrets = $_SESSION[ 'searchSecret' ];
		else $Search_Secrets = '';
	}


	$myButtons = '';

	if ( $PageHTML->is_administrator() or $groupsRights[ 'W' ] == 1 ) {
//    	$addButton = '<a class="btn btn-small" href="' . URL_BASE . '/SM-secrets.php?action=SCR_A&rp=home" title="' . $L_Create . '"><i class="icon-plus"></i></a>';
    	$addButton = '<a class="btn btn-small" href="javascript:getCreateSecret( 0 );" title="' . $L_Create . '"><i class="icon-plus"></i></a>';
    } else {
    	$addButton = '';
    }

   	$addButton = '<form class="form-search simple" method="post" name="searchForm" action="' . $Script . '?action=R2">' .
   	 '<div class="input-append">' .
	 '<input type="text" class="span2 search-query" id="iSearchSecret" name="searchSecret" value="' . $Search_Secrets . '" />' .
	 '<button type="submit" class="btn btn-small" title="' . $L_Search . '"><img class="no-border" src="' . URL_PICTURES . '/b_search.png" alt="'. $L_Search . 
	 '" /></button>' .
	 '</div>' .
	 $addButton .
	 '</form>';

	$myButtons = '<div style="float: right; display: inline; margin-right: 3px;">' . $addButton . "</div>";

	print( "     <table class=\"table-bordered principal\">\n" .
	 "      <thead class=\"fixedHeader\">\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"9\"><span style=\"height: 100%;vertical-align:middle;\">" . $L_List_Secrets ."</span>". $myButtons . "</th>\n" .
	 "       </tr>\n" );
	
	print( "       <tr class=\"pair\">\n" );
	
	if ( $orderBy == 'group' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'group-desc';
	} else {
		if ( $orderBy == 'group-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'group';
	}

	print( "        <td style=\"width:". $S_Group ."px; max-width:". $S_Group ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Group . "</td>\n" );
	 
	if ( $orderBy == 'type' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'type-desc';
	} else {
		if ( $orderBy == 'type-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'type';
	}

	print( "        <td style=\"width:". $S_Type ."px; max-width:". $S_Type ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Type . "</td>\n" );
	 
	if ( $orderBy == 'environment' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'environment-desc';
	} else {
		if ( $orderBy == 'environment-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'environment';
	}

	print( "        <td style=\"width:". $S_Environment ."px; max-width:". $S_Environment ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Environment . "</td>\n" );
	 
	if ( $orderBy == 'application' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'application-desc';
	} else {
		if ( $orderBy == 'application-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'application';
	}

	print( "        <td style=\"width:". $S_Application ."px; max-width:". $S_Application ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Application . "</td>\n" );
	 
	if ( $orderBy == 'host' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'host-desc';
	} else {
		if ( $orderBy == 'host-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'host';
	}

	print( "        <td style=\"width:". $S_Host ."px; max-width:". $S_Host ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Host . "</td>\n" );
	 
	if ( $orderBy == 'user' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'user-desc';
	} else {
		if ( $orderBy == 'user-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'user';
	}

	print( "        <td style=\"width:". $S_User ."px; max-width:". $S_User ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_User . "</td>\n" );
	 
	if ( $orderBy == 'expiration_date' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'expiration_date-desc';
	} else {
		if ( $orderBy == 'expiration_date-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'expiration_date';
	}

	print( "        <td style=\"width:". $S_Expiration_Date ."px; max-width:". $S_Expiration_Date ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Expiration_Date . "</td>\n" );
	 
	if ( $orderBy == 'comment' ) {
		$tmpClass = 'order-select';
		
		$tmpSort = 'comment-desc';
	} else {
		if ( $orderBy == 'comment-desc' ) $tmpClass = 'order-select';
		else $tmpClass = 'order';
		
		$tmpSort = 'comment';
	}

	print( "        <td style=\"width:". $S_Comment ."px; max-width:". $S_Comment ."px;\" onclick=\"javascript:document.location='" . $Script . 
	 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Comment . "</td>\n" );


	print( "        <td style=\"width:". $S_Action ."px; max-width:". $S_Action . "px;\">" . $L_Actions . "</td>\n" .
	 "       </tr>\n" .
	 "      </thead>\n" .
	 "      <tbody id=\"listeSecrets\" class=\"scrollContent\">\n" );

	$Total = construireListe( $Search_Secrets, $orderBy );
		
	print( "      </tbody>\n" .
	 "      <tfoot><tr><th colspan=\"9\">Total : <span id=\"total\" class=\"green\">" . 
	 $Total . "</span>" . $myButtons . "</th></tr></tfoot>\n" .
	 "     </table>\n" .
	 "\n" .
	 "    </div> <!-- fin : dashboard -->\n" );

   break;


 // ============================================
 // Réponses aux appels AJAX

 case 'AJAX_LV':
    // Cet appel remonte les listes et libellés utiles à la création ou la modification d'un secret.

    // Force la traduction des libellés des Types.
    foreach( $List_Types as $KeyT => $ValueP ) {
        foreach( $ValueP as $Key => $Value ) {
            if ( $Key == 'stp_name' ) $Value = ${$Value};

            $List_Types_2[ $KeyT ][ $Key ] = $Value;
        }
    }

    // Force la traduction des libellés des Environnements.
    foreach( $List_Environments as $KeyT => $ValueP ) {
        foreach( $ValueP as $Key => $Value ) {
            if ( $Key == 'env_name' ) $Value = ${$Value};

            $List_Environments_2[ $KeyT ][ $Key ] = $Value;
        }
    }

    try {
        $Secret = $Secrets->get( $_POST[ 'scr_id' ] );

        $Resultat = array(
            'statut' => 'success',
            'listGroups' => $List_Groups,
            'listTypes' => $List_Types_2,
            'listEnvironments' => $List_Environments_2,
            'L_Group' => $L_Group,
            'L_Type' => $L_Type,
            'L_Environment' => $L_Environment,
            'L_Application' => $L_Application,
            'L_Host' => $L_Host,
            'L_User' => $L_User,
            'L_Expiration_Date' => $L_Expiration_Date,
            'L_Comment' => $L_Comment,
            'L_Secret' => $L_Secret,
            'L_Alert' => $L_Alert,
            'alert' => $Secret->scr_alert,
            'L_Password' => $L_Password,
            'Password' => $Secret->scr_password
            );
    } catch( Exception $e ) {
        $Resultat = array(
            'statut' => 'error',
            'message' => $e->getMessage()
            );
        
    }

    echo json_encode( $Resultat );
    exit();


 case 'AJAX_S':
    // Cet appel sauvegarde les informations modifiées d'un secret.

    $Secret = $Secrets->set( $_POST['scr_id'], $_POST['sgr_id'], $_POST['stp_id'],
        $_POST['scr_host'], $_POST['scr_user'], $_POST['scr_password'],
	    $_POST['scr_comment'], $_POST['scr_alert'], $_POST['env_id'],
	    $_POST['scr_application'], $_POST['scr_expiration_date'] );

    if ( $Secret === true ) {
        $Status = 'success';
        $Message = $L_Secret_Modified;
    } else {
        $Status = 'error';
        $Message = $L_ERR_MODI_Secret;
    }

    $Resultat = array(
        'status' => $Status,
        'message' => $Message
        );

    echo json_encode( $Resultat );
    exit();

	break;


 case 'AJAX_R':
    // Cet appel actualise tous les secrets.

    construireListe('');
    exit();

	break;


 case 'AJAX_D':
    // Cet appel sauvegarde les informations modifiées d'un secret.

    $Secret = $Secrets->delete( $_POST['scr_id'] );

    if ( $Secret === true ) {
        $Status = 'success';
        $Message = $L_Secret_Deleted;
    } else {
        $Status = 'error';
        $Message = $L_ERR_DELE_Secret;
    }

    $Resultat = array(
        'status' => $Status,
        'message' => $Message
        );

    echo json_encode( $Resultat );
    exit();

	break;
}

print( "   </div> <!-- Fin : zoneMilieuComplet -->\n" .
 "   <!-- Début : afficherSecret -->\n" .
 "   <div id=\"afficherSecret\" class=\"tableau_synthese hide modal\" style=\"left:45%;\">\n".
 "    <button type=\"button\" class=\"close\">×</button>\n".
 "    <p class=\"titre\">".$L_Secret_View."</p>\n".
 "    <div id=\"detailSecret\" style=\"margin:6px;padding:6px;min-width:250px;\" class=\"corps vertical-align align-center\"></div>\n" .
 "   </div> <!-- Fin : afficherSecret -->\n" .
 $PageHTML->construireFooter( 1, 'home' ) .
 $PageHTML->piedPageHTML() );

?>
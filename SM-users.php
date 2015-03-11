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
include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-profils.php' );
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


$verbosity_alert = $PageHTML->getParameter('verbosity_alert');


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
	$JS_Scripts = array( 'SecretManager.js', 'Ajax_users.js', 'Ajax_profiles.js', 'Ajax_entities.js',
	    'Ajax_civilities.js', 'Ajax_secrets.js' );

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
	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	
	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right;"><a class="button" href="' . $Script . '?action=add">' . $L_Create . '</a></span>' ;

		$returnButton = "<span style=\"float: right\">" .
		 "<a class=\"button\" href=\"SM-admin.php\">" . $L_Return . "</a></span>";

		$Buttons = $addButton . $returnButton;


		if ( array_key_exists( 'particular', $_GET ) ) {
			$particular = $_GET[ 'particular' ];
			$Buttons = "<span style=\"float: right\">" .
				 "<a class=\"button\" href=\"SM-home.php\">" . $L_Return . "</a></span>";

		} else {
			$particular = '';
		}


		print( "     <table class=\"table-bordered\" cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"10\">" . $L_List_Users . $Buttons . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );


		// Récupère la liste détaillée des Identités (avec toutes les liaisons)
		$List_Identities = $Identities->detailedListIdentities( $orderBy, $particular );

		print( "       <tr class=\"pair\">\n" );
	 

		// Classe les données selon la colonne sélectionnée.
		if ( $orderBy == 'entity' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'entity-desc';
		} else {
			if ( $orderBy == 'entity-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'entity';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Entity .
		 "</th>\n" );

		 
		if ( $orderBy == 'first_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'first_name-desc';
		} else {
			if ( $orderBy == 'first_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'first_name';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_First_Name .
		 "</th>\n" );

		 
		if ( $orderBy == 'last_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'last_name-desc';
		} else {
			if ( $orderBy == 'last_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'last_name';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Last_Name .
		 "</th>\n" );
		
		 
		if ( $orderBy == 'username' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'username-desc';
		} else {
			if ( $orderBy == 'username-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'username';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . $L_Username .
		 "</th>\n" );

		 
		if ( $orderBy == 'last_connection' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'last_connection-desc';
		} else {
			if ( $orderBy == 'last_connection-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'last_connection';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Last_Connection . "</th>\n" );

		 
		if ( $orderBy == 'administrator' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'administrator-desc';
		} else {
			if ( $orderBy == 'administrator-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'administrator';
		}

		if ( strlen( $L_Administrator ) > 5 ) $L_Administrator = sprintf( "%.5s&hellip;", $L_Administrator );

		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Administrator . "</th>\n" );

		 
		if ( $orderBy == 'operator' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'operator-desc';
		} else {
			if ( $orderBy == 'operator-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'operator';
		}

		if ( strlen( $L_Operator ) > 5 ) $L_Operator = sprintf( "%.5s&hellip;", $L_Operator );

		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Operator . "</th>\n" );

		 
		if ( $orderBy == 'api' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'api-desc';
		} else {
			if ( $orderBy == 'api-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'api';
		}

		if ( strlen( $L_API ) > 5 ) $L_Operator = sprintf( "%.5s&hellip;", $L_API );

		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_API . "</th>\n" );
		
		 
		print( "        <th>" . $L_Status . "</th>\n" .
		 "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );
		
		
		$BackGround = "pair";
		
		foreach( $List_Identities as $Identity ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";


			if ( $Identity->idn_disable == 0 )
				$Disable = $L_No;
			else
				$Disable = $L_Yes;


			if ( $Identity->idn_super_admin == 0 ) {
				$Flag_Admin = '<img class="no-border" src="' . URL_PICTURES . '/bouton_non_coche.gif" alt="Ko" />';
			} else {
				$Flag_Admin = '<img class="no-border" src="' . URL_PICTURES . '/bouton_coche.gif" alt="Ko" />';
		  	}


			if ( $Identity->idn_operator == 0 ) {
				$Flag_Oper = '<img class="no-border" src="' . URL_PICTURES . '/bouton_non_coche.gif" alt="Ko" />';
			} else {
				$Flag_Oper = '<img class="no-border" src="' . URL_PICTURES . '/bouton_coche.gif" alt="Ko" />';
		  	}


			if ( $Identity->idn_api == 0 ) {
				$Flag_API = '<img class="no-border" src="' . URL_PICTURES . '/bouton_non_coche.gif" alt="Ko" />';
			} else {
				$Flag_API = '<img class="no-border" src="' . URL_PICTURES . '/bouton_coche.gif" alt="Ko" />';
		  	}


			/*
			** Vérifie le statut de l'identité.
			*/
			$Flag_Status = 0;
			$Msg_Error = '';
		

			if ( $Identity->idn_attempt > $_Max_Attempt ) {
				$Flag_Status = 1;
				$Msg_Error = $L_Attempt_Exceeded;
			}
		

			if ( $Identity->idn_expiration_date != '0000-00-00 00:00:00' ) {
				if ( $Identity->idn_expiration_date < date( 'Y-m-d' ) ) {
					if ( $Flag_Status == 1 ) {
						$Msg_Error .= ', ';
					} else {
						$Flag_Status = 1;
					}

					$Msg_Error .= $L_Expiration_Date_Exceeded;
				}
			}


			if ( $Identity->idn_last_connection != '0000-00-00 00:00:00' ) {
				$datetime1 = new DateTime( date( 'Y-m-d' ) );
				$datetime2 = new DateTime( $Identity->idn_last_connection );

				$interval = $datetime1->diff( $datetime2 );

				if ( $interval->format('%R') == '-' ) {
					if ( $interval->format('%m') >= $_Default_User_Lifetime ) {
						if ( $Flag_Status == 1 ) {
							$Msg_Error .= ', ';
						} else {
							$Flag_Status = 1;
						}

						$Msg_Error .= $L_Last_Connection_Old;
					}
				}
			} else {
				if ( $Flag_Status == 1 ) {
					$Msg_Error .= ', ';
				} else {
					$Flag_Status = 1;
				}

				$Msg_Error .= $L_Never_Connected;
			}

		
			if ( $Identity->idn_disable == 1 ) {
				if ( $Flag_Status == 1 ) {
					$Msg_Error .= ', ';
				} else {
					$Flag_Status = 1;
				}

				$Msg_Error .= $L_User_Disabled;
			}

		
			if ( $Identity->total_prf == 0 and $Identity->idn_super_admin == FALSE ) {
				if ( $Flag_Status == 1 ) {
					$Msg_Error .= ', ';
				} else {
					$Flag_Status = 1;
				}

				$Msg_Error .= $L_No_User_Profile_Associated;
			}
		

			if ( $Flag_Status == 1 ) {
				$Flag_Status = '<img src="' . URL_PICTURES . '/s_attention.png" class="no-border" alt="Ko" title="' . $Msg_Error . '" />';
			} else {
				$Flag_Status = '<img src="' . URL_PICTURES . '/s_okay.png" class="no-border" alt="Ok" title="Ok" />';
			}

			print( "       <tr class=\"" . $BackGround . " surline\">\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Identity->ent_label ) . "</td>\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Identity->cvl_first_name ) . "</td>\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Identity->cvl_last_name ) . "</td>\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Identity->idn_last_connection ) . "</td>\n" .
			 "        <td class=\"align-center align-middle\">" . $Flag_Admin . "</td>\n" .
			 "        <td class=\"align-center align-middle\">" . $Flag_Oper . "</td>\n" .
			 "        <td class=\"align-center align-middle\">" . $Flag_API . "</td>\n" .
			 "        <td class=\"align-center align-middle\">" . $Flag_Status . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=M&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usredit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=D&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrdrop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=V&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrcheck.png\" alt=\"" . $L_Verify . "\" title=\"" . $L_Verify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=P&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrlist.png\" alt=\"" .
			  $L_Profiles . "\" title=\"" . $L_Profiles_Associate . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"10\">Total : <span class=\"green\">" . 
		 count( $List_Identities ) . "</span>" . $Buttons . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'ADD':
	$T_Entities = $Entities->listEntities();
	$T_Civilities = $Civilities->listCivilities();
	
	print( "     <form name=\"addIdentity\" method=\"post\" action=\"" . 
	 $Script . "?action=ADDX\">\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_User_Create . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td width=\"35%\">" . $L_Entity . "</td>\n" .
	 "        <td width=\"65%\">\n" .

	 "         <table>\n" .
	 "          <tr>\n" .
	 "           <td>\n" .
	 "            <select name=\"Id_Entity\">\n" );

	foreach( $T_Entities as $Entity ) {
	 	print( "             <option value=\"" . $Entity->ent_id . "\">" .
	 	 $Security->XSS_Protection( $Entity->ent_code ) . " - " . 
	 	 $Security->XSS_Protection( $Entity->ent_label ) . "</option>\n" );
	}

	print( "            </select>\n" .
	 "            <a class=\"button\" href=\"" . $Script . "?action=ENT_V&rp=users_a\">" .
	 $L_Entities_Management . "</a>\n" .
	 "           </td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Civility . "</td>\n" .
	 "        <td>\n" .

	 "         <table>\n" .
	 "          <tr>\n" .
	 "           <td>\n" .
	 "            <select name=\"Id_Civility\">\n" );
	 
	foreach( $T_Civilities as $Civility ) {
	 	print( "             <option value=\"" . $Civility->cvl_id . "\">" . 
		 $Security->XSS_Protection( $Civility->cvl_first_name ) . " " .
		 $Security->XSS_Protection( $Civility->cvl_last_name ) . "</option>\n" );
	}
	
	print( "            </select>\n" .
	 "            <a class=\"button\" href=\"" . $Script . 
	 "?action=CVL_V&paction=ADD\">" .
	 $L_Civilities_Management . "</a>\n" .
	 "           </td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .

	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Username . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Username\" size=\"20\"></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Rights . "</td>\n" .
	 "        <td>\n" .

	 "         <table>\n" .
	 "          <tr>\n" .
	 "           <td><label for=\"iAdministrator\">" . $L_Administrator . "</label></td>\n" .
	 "           <td><input id=\"iAdministrator\" name=\"Administrator\" type=\"checkbox\" /></td>\n" .
	 "           <td><label for=\"iOperator\">" . $L_Operator . "</label></td>\n" .
	 "           <td><input id=\"iOperator\" name=\"Operator\" type=\"checkbox\" /></td>\n" .
	 "           <td><label for=\"iAPI\">" . $L_API . "</label></td>\n" .
	 "           <td><input id=\"iAPI\" name=\"API\" type=\"checkbox\" /></td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .

	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr id=\"iForcePassword\" class=\"hide\">\n" .
	 "        <td>" . $L_Force_Default_Password . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Password\" size=\"20\" placeholder=\"" . $L_Empty_Default_Password . "\"></td>\n" .
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
	 "document.addIdentity.Id_Entity.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'ADDX':
	$Return_Page = $Script;
 
	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	
	
	// Contrôle les variables transmises.
	if ( isset( $_POST[ 'Administrator' ] ) ) {
		if ( $_POST[ 'Administrator' ] == 'on' )
			$SuperAdmin = 1;
	} else {
		$SuperAdmin = 0;
	}

	if ( isset( $_POST[ 'Operator' ] ) ) {
		if ( $_POST[ 'Operator' ] == 'on' )
			$Operator = 1;
	} else {
		$Operator = 0;
	}

	if ( isset( $_POST[ 'API' ] ) ) {
		if ( $_POST[ 'API' ] == 'on' )
			$API = 1;
	} else {
		$API = 0;
	}

	if ( ! $Username = $Security->valueControl( $_POST[ 'Username' ] ) ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Username)', $Return_Page, 1 ) );
		exit();
	}

	if ( ($ent_id = $Security->valueControl( $_POST[ 'Id_Entity' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Id_Entity)', $Return_Page, 1 ) );
		exit();
	}

	if ( ($cvl_id = $Security->valueControl( $_POST[ 'Id_Civility' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Id_Civility)', $Return_Page, 1 ) );
		exit();
	}

	if ( ! $Password = $Security->valueControl( $_POST[ 'Password' ] ) ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Password)', $Return_Page, 1 ) );
		exit();
	}
	

	// ========================================================================
	// Calcule un nouveau grain de sel spécifique à l'utilisateur et l'utilise
	// pour chiffrer le mot de passe.
	$size = 8;
	$complexity = 2; // Majuscules, Minuscules et Chiffres
	
	$Salt = $Security->passwordGeneration( $size, $complexity );
	
	if ( $Password == '' ) {
		$Authenticator = sha1( $_Default_Password . $Salt );
	} else {
		$Authenticator = sha1( $Password . $Salt );
	}

	
	if ( $verbosity_alert == 2 ) {
		$tEntity = $Identities->getEntity( $ent_id );
		$tCivility = $Identities->getCivility( $cvl_id );

		$oUser = new stdClass();
		$oUser->idn_login = $Username;
		$oUser->idn_super_admin = $SuperAdmin;
		$oUser->idn_operator = $Operator;
		$oUser->cvl_last_name = stripslashes( $tCivility->cvl_last_name );
		$oUser->cvl_first_name = stripslashes( $tCivility->cvl_first_name );
		$oUser->ent_code = stripslashes( $tEntity->ent_code );
		$oUser->ent_label = stripslashes( $tEntity->ent_label );
	}


	try {
		$Identities->set( '', $Username, $Authenticator, 1, 0,
			$SuperAdmin, $Operator, $ent_id, $cvl_id, $API, $Salt );

		$Last_ID = $Identities->LastInsertId;

		$alert_message = $PageHTML->getTextCode( 'L_User_Created' ) . ' [' . $Last_ID . ']';

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( $Last_ID );
		}
		
		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 2, LOG_INFO );
	} catch( PDOException $e ) {
		$alert_message = $PageHTML->getTextCode( 'L_ERR_CREA_Identity' );

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( '', $oUser );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 2, LOG_ERR );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_CREA_Identity, $Return_Page, 1 ) );
		exit();
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			$Message = 'L_ERR_DUPL_Identity';

			print( $PageHTML->returnPage( $L_Title, ${$Message}, $Return_Page, 1 ) );
		} else {
			$Message = 'L_ERR_CREA_Identity';
			
			print( $PageHTML->returnPage( $L_Title, ${$Message}, $Return_Page, 1 ) );
		}

		$alert_message = $PageHTML->getTextCode( $Message );

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( '', $oUser );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 2, LOG_ERR );
		
		exit();
	}


	print( "<form method=\"post\" action=\"" . $Return_Page . "\" name=\"fInfoMessage\">\n" .
		" <input type=\"hidden\" name=\"infoMessage\" value=\"" . $L_User_Created ."\">\n" .
		"</form>\n" .
		"<script>document.fInfoMessage.submit();</script>" );
	break;


 case 'D':
	$Return_Page = $Script;
 
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}
	
	$Identity = $Identities->detailedGet( $idn_id );
	
	if ( $Identity->cvl_sex == 0 )
		$Sex = $L_Man;
	else
		$Sex = $L_Woman;

	if ( $Identity->idn_super_admin == 1 )
		$Flag_Administrator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Administrator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";

	if ( $Identity->idn_operator == 1 )
		$Flag_Operator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Operator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";

	if ( $Identity->idn_api == 1 )
		$Flag_API = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_API = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";


	print( "     <form name=\"deleteEntity\" method=\"post\" action=\"" . $Script . 
	 "?action=DX&idn_id=" . $idn_id . "\">\n" .
	 "      <input type=\"hidden\" name=\"cvl_id\" value=\"" . $Identity->cvl_id . "\" />\n" .
	 "      <table class=\"table-center table-norm\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_User_Delete . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Entity . "</td>\n" .
	 "        <td  class=\"pair blue1 bold td-aere\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . ' - ' . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Civility . "</td>\n" .
	 "        <td  class=\"pair green bold td-aere\">\n" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . ' ' .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) . ' (' .
	 $Sex . ")</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Username . "</td>\n" .
	 "        <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Rights . "</td>\n" .
	 "        <td class=\"bg-light-grey td-aere\">\n" .
	 "         " . $L_Administrator . "\n" .
	 "         " . $Flag_Administrator . "\n" .
	 "         " . $L_Operator . "\n" .
	 "         " . $Flag_Operator . "\n" .
	 "         " . $L_API . "\n" .
	 "         " . $Flag_API . "\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td class=\"td-aere\"><input name=\"b_cancel\" type=\"submit\" class=\"button\" value=\"".
	 $L_Delete . "\" /><a  class=\"button\" href=\"". $Script . "\">" . $L_Cancel .
	 "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.deleteEntity.b_cancel.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'DX':
	$Return_Page = $Script;
 
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		exit();
	}

	try {
		$oUser = $Identities->detailedGet( $idn_id );

		$Identities->delete( $idn_id );

		$alert_message = $PageHTML->getTextCode( 'L_User_Deleted' ) . ' [' . $idn_id . ']';

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( '', $oUser );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 4, LOG_INFO );
	} catch( PDOException $e ) {
		$alert_message = $PageHTML->getTextCode( 'L_ERR_DELE_Identity' ) . ' [' . $idn_id . ']';

		if ( $verbosity_alert == 2 ) { print($e->getMessage() . ' - ' .$alert_message);
			$alert_message .= $Identities->getUserForHistory( $idn_id );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 4, LOG_ERR );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_DELE_Identity, $Return_Page, 1 ) );
		exit();
	}
	
	print( "<form method=\"post\" action=\"" . $Return_Page . "\" name=\"fInfoMessage\">\n" .
	 " <input type=\"hidden\" name=\"infoMessage\" value=\"" . $L_User_Deleted . "\" />\n" .
	 "</form>\n" .
	 "<script>document.fInfoMessage.submit();</script>" );

	break;


 case 'M':
	$Return_Page = $Script;
 
	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->get( $idn_id );
	
	if ( $Identity->idn_super_admin == 1 )
		$Flag_Check_Administrator = "checked";
	else
		$Flag_Check_Administrator = "";

	if ( $Identity->idn_operator == 1 )
		$Flag_Check_Operator = "checked";
	else
		$Flag_Check_Operator = "";

	if ( $Identity->idn_api == 1 ) {
		$Flag_Check_API = "checked";
		$View_Button_Default = 'hide';
		$View_Field_Forced = 'display: inline-block;';
	} else {
		$Flag_Check_API = "";
		$View_Button_Default = 'button';
		$View_Field_Forced = 'display: none;';
	}


	$T_Entities = $Entities->listEntities();
	$T_Civilities = $Civilities->listCivilities();

	
	print(
	 "     <form name=\"m_identity\" method=\"post\" action=\"" . $Script . "?action=MX\">\n" .
	 "      <input type=\"hidden\" name=\"idn_id\" value=\"" . $idn_id . 
	 "\" />\n" .
	 "      <table class=\"table-center\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_User_Modify . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle td-aere\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"td-aere\">\n" .
	 "         <select id=\"iSelectEntity\" name=\"ent_id\">\n" );

	foreach( $T_Entities as $Entity ) {
		if ( $Identity->ent_id == $Entity->ent_id )
			$Flag = "selected";
		else
			$Flag = "";

	 	print( "            <option value=\"" . $Entity->ent_id . "\" " . $Flag . ">" . 
	 	 $Security->XSS_Protection( $Entity->ent_code ) . " - " . 
	 	 $Security->XSS_Protection( $Entity->ent_label ) . "</option>\n" );
	}

	print( "         </select>\n" .
	 "         <a class=\"button\" href=\"javascript:putAddEntity('".addslashes($L_Entity_Create)."','".
	 $L_Code."','".$L_Label."','".$L_Cancel."','".$L_Create."');\" title=\"" . $L_Entity_Create . "\">+</a>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle td-aere\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"td-aere\">\n" .
	 "         <select id=\"iSelectCivility\" name=\"cvl_id\">\n" );
 	
	foreach( $T_Civilities as $Civility ) {
		if ( $Identity->cvl_id == $Civility->cvl_id )
			$Flag = "selected";
		else
			$Flag = "";

	 	print( "            <option value=\"" . $Civility->cvl_id . "\" " . $Flag . ">" . 
	 	 $Security->XSS_Protection( $Civility->cvl_first_name ) . " " .
	 	 $Security->XSS_Protection( $Civility->cvl_last_name ) . "</option>\n" );
	}

	
	if ( $Identity->idn_attempt > $_Max_Attempt )
		$Attempt_Color = "bg-orange";
	else
		$Attempt_Color = "bg-green";


	if ( $Identity->idn_disable == 1 ) {
		$Disable_Color = "bg-orange";
		$Disable_Msg = $L_Yes;
		$Disable_Action = $L_To_Activate_User;
		$Disable_Status = 0;
	} else {
		$Disable_Color = "bg-green";
		$Disable_Msg = $L_No;
		$Disable_Action = $L_To_Deactivate_User;
		$Disable_Status = 1;
	}


	$Msg_Color = 'bg-green';

	if ( $Identity->idn_expiration_date != '0000-00-00 00:00:00' ) {
		$datetime1 = new DateTime( date( 'Y-m-d' ) );
		$datetime2 = new DateTime( $Identity->idn_expiration_date );

		$interval = $datetime1->diff( $datetime2 );

		if ( $interval->format('%R') == '-' ) {
			$Msg_Color = 'bg-orange';
		}
	} else {
		$Msg_Color = 'bg-orange';
	}

	$Msg_Expiration_Date = '<span  id="expiration-date" class="' . $Msg_Color . '">&nbsp;' .
	 $Identity->idn_expiration_date . '&nbsp;</span>';
	

	print( "         </select>\n" .
	 "         <a class=\"button\" href=\"javascript:putAddCivility('".addslashes($L_Civility_Create)."','".$L_First_Name.
	 "','".$L_Last_Name."','".$L_Sex."','".$L_Man."','".$L_Woman."','".$L_Cancel."','".$L_Create."');\" " .
	 "title=\"" . $L_Civility_Create . "\">+</a>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Username . "</td>\n" .
	 "        <td class=\"td-aere\"><input name=\"Username\" type=\"text\" size=\"20\" value=\"" . 
	 $Security->XSS_Protection( $Identity->idn_login ). "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle td-aere\">" . $L_Rights . "</td>\n" .
	 "        <td class=\"td-aere\">\n" .
	 "           <label for=\"iAdministrator\">" . $L_Administrator . "</label>\n" .
	 "           <input id=\"iAdministrator\" name=\"Administrator\" type=\"checkbox\" " . $Flag_Check_Administrator . " />\n" .
	 "           <label for=\"iOperator\">" . $L_Operator . "</label>\n" .
	 "           <input id=\"iOperator\" name=\"Operator\" type=\"checkbox\" " . $Flag_Check_Operator . " />\n" .
	 "           <label for=\"iAPI2\">" . $L_API . "</label>\n" .
	 "           <input id=\"iAPI2\" name=\"API\" type=\"checkbox\" " . $Flag_Check_API . " />\n" .
	 "        </td>\n" .
 	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Password . "</td>\n" .
	 "        <td class=\"td-aere\">" .
	 "<a class=\"" . $View_Button_Default . "\" id=\"iResetDefault\" href=\"javascript:resetPassword('" . $idn_id . "');" .
	 "\">" . $L_Authenticator_Reset . "</a>" . 
	 "<input name=\"Password\" id=\"iForceField\" style=\"" . $View_Field_Forced . "\" type=\"text\" size=\"20\" placeholder=\"" . $L_Empty_No_Change_Password . "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Attempt . "</td>\n" .
	 "        <td class=\"td-aere\"><span id=\"total-attempt\" class=\"" . $Attempt_Color . "\">&nbsp;" . $Identity->idn_attempt .
	 "&nbsp;</span>&nbsp;/&nbsp; " .
	 $_Max_Attempt . " <a class=\"button\" href=\"javascript:resetAttempt('" . $idn_id . "');\">" . $L_Attempt_Reset .
	 "</a></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Expiration_Date . "</td>\n" .
	 "        <td class=\"td-aere\">" . $Msg_Expiration_Date . "&nbsp;<a class=\"button\" href=\"javascript:resetExpirationDate('" . $idn_id . "');\">" . $L_Expiration_Date_Reset . "</a></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Disabled . "</td>\n" .
	 "        <td class=\"td-aere\"><span id=\"disabled-user\" class=\"" . $Disable_Color . "\">&nbsp;" . $Disable_Msg .
	 "&nbsp;</span>&nbsp;<a id=\"action-button\" class=\"button\" href=\"javascript:enableDisableUser('" . $idn_id . "','" . $Disable_Status .
	 "');\">" . $Disable_Action . "</a></td>\n" .
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
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.m_identity.ent_id.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'MX':
	$Return_Page = $Script;

	if ( isset( $_POST[ 'Administrator' ] ) ) {
		if ( $_POST[ 'Administrator' ] == 'on' )
			$SuperAdmin = 1;
	} else {
		$SuperAdmin = 0;
	}
	
	if ( isset( $_POST[ 'Operator' ] ) ) {
		if ( $_POST[ 'Operator' ] == 'on' )
			$Operator = 1;
	} else {
		$Operator = 0;
	}
	
	if ( isset( $_POST[ 'API' ] ) ) {
		if ( $_POST[ 'API' ] == 'on' )
			$API = 1;
	} else {
		$API = 0;
	}


	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		exit();
	}

	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (ent_id)', $Return_Page, 1 ) );
		exit();
	}

	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (cvl_id)', $Return_Page, 1 ) );
		exit();
	}

	if ( ! $Username = $Security->valueControl( $_POST[ 'Username' ] ) ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Username)', $Return_Page, 1 ) );
		exit();
	}

	if ( $_POST[ 'Password'] != '' ) {
		if ( ! $Password = $Security->valueControl( $_POST[ 'Password' ] ) ) {
			print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (Password)', $Return_Page, 1 ) );
			exit();
		}
	} else {
		$Password = '';
	}


	// ========================================================================
	// Calcule un nouveau grain de sel spécifique à l'utilisateur et l'utilise
	// pour chiffrer le mot de passe.
	if ( $Password != '' ) {
		$size = 8;
		$complexity = 2; // Majuscules, Minuscules et Chiffres
		
		$Salt = $Security->passwordGeneration( $size, $complexity );
	
		$Authenticator = sha1( $Password . $Salt );
	} else {
		$Authenticator = '';
		$Salt = '';
	}

	
	try {
		$Identities->set( $idn_id, $Username, $Authenticator, 1, 0, $SuperAdmin, $Operator, $ent_id,
		 $cvl_id, $API, $Salt );

		$alert_message = $PageHTML->getTextCode( 'L_User_Modified' ) . ' [' . $idn_id . ']';

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( $idn_id );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, LOG_INFO );
	} catch( PDOException $e ) {
		$alert_message = $PageHTML->getTextCode( 'L_ERR_MODI_Identity' );

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( $idn_id ) . ' [' . $idn_id . ']';
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, LOG_ERR );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_MODI_Identity, $Return_Page, 1 ) );
		exit();
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			$Message = $L_ERR_DUPL_Identity;
			$L_Message = 'L_ERR_DUPL_Identity';

			print( $PageHTML->returnPage( $L_Title, $Message, $Return_Page, 1 ) );
		} else {
			$Message = $L_ERR_MODI_Identity;
			$L_Message = 'L_ERR_MODI_Identity';
			
			print( $PageHTML->returnPage( $L_Title, $Message, $Return_Page, 1 ) );
		}

		$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $idn_id . ']';

		if ( $verbosity_alert == 2 ) {
			$alert_message .= $Identities->getUserForHistory( $idn_id );
		}

		$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, LOG_ERR );

		exit();
	}


	print( "<form method=\"post\" action=\"" . $Return_Page . "\" name=\"fInfoMessage\" >\n" .
	 " <input type=\"hidden\" name=\"infoMessage\" value=\"" . $L_User_Modified . "\" />\n" .
	 "</form>\n" .
	 "<script>document.fInfoMessage.submit();</script>\n"
	 );
	break;


 case 'V':
	$Return_Page = $Script;

	include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
	
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );
	
	if ( $Identity->idn_super_admin == 1 )
		$Flag_Check_Administrator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_coche.gif\" alt=\"Ko\" />";
	else
		$Flag_Check_Administrator = "<img class=\"no-border\" src=\"" . URL_PICTURES . "/bouton_non_coche.gif\" alt=\"Ko\" />";


	if ( $Identity->cvl_sex == 0 )
		$Flag_Sex = $L_Man;
	else
		$Flag_Sex = $L_Woman;


	if ( $Identity->idn_change_authenticator == 1 )
		$Flag_Change_Authenticator = $L_Yes;
	else
		$Flag_Change_Authenticator = $L_No;


	if ( $Identity->idn_disable == 1 )
		$Flag_Disable = $L_Yes;
	else
		$Flag_Disable = $L_No;

	
	print(
	 "    <form method=\"post\" action=\"" . $Script . "\">\n" .
	 "     <table class=\"table-center table-norm\">\n" .
	 "      <thead>\n" .
	 "      <tr>\n" .
	 "       <th colspan=\"2\">" . $L_User_View . "</th>\n" .
	 "      </tr>\n" .
	 "      </thead>\n" .
	 "      <tbody>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle td-aere\">" . $L_Entity . "</td>\n" .
	 "       <td class=\"pair blue1 bold td-aere\">\n" .
	 $Security->XSS_Protection( $Identity->ent_code ) . " - " . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td></tr>\n" .
	 "       </td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle td-aere\">" . $L_Civility . "</td>\n" .
	 "       <td class=\"pair green bold td-aere\">\n" .
	 "        " . $Security->XSS_Protection( $Identity->cvl_first_name ) . " " .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) . " (" .
	 $Flag_Sex . ")\n" .
	 "       </td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Username . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Change_Authenticator_Flag . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . $Flag_Change_Authenticator . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Attempt . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . $Identity->idn_attempt . ' / ' . $_Max_Attempt .
	 "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Disabled . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . $Flag_Disable . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Last_Connection . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_last_connection ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Expiration_Date . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_expiration_date ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right td-aere\">" . $L_Updated_Authentication . "</td>\n" .
	 "       <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_updated_authentication ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle td-aere\">" . $L_Administrator . "</td>\n" .
	 "       <td class=\"td-aere\">" . $Flag_Check_Administrator . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"td-aere\">&nbsp;</td>\n" .
	 "       <td class=\"td-aere\"><input id=\"b_return\"type=\"submit\" class=\"button\" value=\"".
	 $L_Return . "\" /></td>\n" .
	 "      </tr>\n" .
	 "      </tbody>\n" .
	 "     </table>\n" .
	 "    </form>\n" .
	 "    <script>\n" .
	 "document.getElementById( 'b_return' ).focus();\n" .
	 "    </script>\n"
	);
	
	break;


 case 'ENT_V':
	if ( array_key_exists( 'rp', $_GET ) ) {
 		switch( $_GET[ 'rp' ] ) {
 		 default:
			$_SESSION[ 'Prev_Page' ] = $Script;
			$Return_Button = $L_Users_List_Return;
			break;

 		 case 'users_m':
 			$_SESSION[ 'Prev_Page' ] = 'SM-users.php?action=M&idn_id=' .
 			 $_GET[ 'idn_id' ];
 			$Return_Button = $L_Return;
 			break;

 		 case 'users_a':
 			$_SESSION[ 'Prev_Page' ] = 'SM-users.php?action=ADD';
 			$Return_Button = $L_Return;
 			break;

 		 case 'home':
 			$_SESSION[ 'Prev_Page' ] = 'SM-home.php';
 			$Return_Button = $L_Return;
 			break;

 		 case 'admin':
 			$_SESSION[ 'Prev_Page' ] = 'SM-admin.php';
 			$Return_Button = $L_Return;
 			break;
 		}
 	}
 	
 	if ( isset( $_SESSION[ 'Prev_Page' ] ) ) {
 		$Prev_Action = $_SESSION[ 'Prev_Page' ];
		$Return_Button = $L_Return;
 	} else {
 		$Prev_Action = $Script;
		$Return_Button = $L_Users_List_Return;
 	}
 	
	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'code';
	}

	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$addButton = "<span style=\"float: right\"><a class=\"button\" href=\"javascript:putAddEntity('" .
		    addslashes(htmlspecialchars( $L_Entity_Create, ENT_COMPAT )) . "', '" . $L_Code . "', '" .
		    $L_Label . "', '" . $L_Cancel . "', '" . $L_Create . "');\">" .
			$L_Create . "</a></span>" ;

		$returnButton = "<span style=\"float: right\"><a class=\"button\" href=\"" .
		 $Prev_Action . "\">" . $Return_Button . "</a></span>";

		if ( $orderBy == 'code' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'code-desc';
		} else {
			if ( $orderBy == 'code-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'code';
		}

		print( "     <table class=\"table-bordered\" cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_List_Entities . $addButton . $returnButton . "</th>\n" .
		 "       </tr>\n" .
		 "       <tr class=\"pair\">\n" .
		 "        <td width=\"30%\" onclick=\"javascript:document.location='" . $Script . 
		 "?action=ENT_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Code . "</td>\n" );

		if ( $orderBy == 'label' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'label-desc';
		} else {
			if ( $orderBy == 'label-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'label';
		}

		print( "        <td width=\"50%\" onclick=\"javascript:document.location='" . $Script . 
		 "?action=ENT_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Label . "</td>\n" .
		 "        <td width=\"20%\">" . $L_Actions . "</td>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody id=\"listeSecrets\">\n" );

		
		$List_Entities = $Entities->listEntities( $orderBy );
		
		foreach( $List_Entities as $Entity ) {
			print( "       <tr id=\"entity_". $Entity->ent_id ."\" class=\"surline\">\n" .
			 "        <td id=\"code-" .  $Entity->ent_id  . "\">" . 
			 $Security->XSS_Protection( $Entity->ent_code ) . "</td>\n" .
			 "        <td id=\"label-" .  $Entity->ent_id  . "\">" . 
			 $Security->XSS_Protection( $Entity->ent_label ) . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"javascript:modifyEntity('" . $Entity->ent_id .
			 "','" . $L_Cancel . "','" . $L_Modify . "');\"><img class=\"no-border\" src=\"" . URL_PICTURES .
			 "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"javascript:confirmDeleteEntity('" .
			 $Entity->ent_id . "','" . $L_Warning . "','" . $L_Confirm_Delete_Entity . "','" . $L_Cancel .
			 "','" . $L_Confirm . "');" .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"7\">Total : <span id=\"total\" class=\"green\">" . 
		 count( $List_Entities ) . "</span>" . $addButton . $returnButton . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		$Return_Page = URL_BASE . '/SM-home.php';

		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'ENT_C':
	$Return_Page = $Script . '?action=ENT_V';

	print(
	 "     <form name=\"c_entity\" method=\"post\" action=\"" . $Script . "?action=ENT_CX\">\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Entity_Create . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Code . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Code\" size=\"10\" maxlength=\"10\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Label . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Label\" size=\"35\" maxlength=\"35\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Create . "\" /><a class=\"button\" href=\"" . $Return_Page .
	 "\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.c_entity.Code.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'ENT_CX':
 	$Last_ID = '';

	if ( ! $Code = $Security->valueControl( $_POST[ 'Code' ] ) ) {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_Invalid_Value . ' (Code)'
			);

		print( json_encode( $Resultat ) );

		exit();
	}

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
		$Entities->set( '', $Code, $Label );
		$Last_ID = $Entities->LastInsertId;

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'IdEntity' => $Last_ID,
			'Message' => $L_Entity_Created,
			'Script' => $Script,
			'URL_PICTURES' => URL_PICTURES,
			'L_Modify' => $L_Modify,
			'L_Delete' => $L_Delete,
            'L_Warning' => $L_Warning,
            'L_Confirm_Delete_Entity' => $L_Confirm_Delete_Entity,
            'L_Cancel' => $L_Cancel,
			'L_Confirm' => $L_Confirm
			);

		$L_Status = LOG_INFO;
		$L_Message = 'L_Entity_Created';
		$Message = ${$L_Message};
	} catch( PDOException $e ) {
		$L_Status = LOG_ERR;
		$L_Message = 'L_ERR_CREA_Entity';
		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $Message
			);
	} catch( Exception $e ) {
		$L_Status = LOG_ERR;

		if ( $e->getCode() == 1062 ) {
			$L_Message = 'L_ERR_DUPL_Entity';
		} else {
			$L_Message = 'L_ERR_CREA_Entity';
		}

		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $Message
			);
	}

	print( json_encode( $Resultat ) );

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $Last_ID . ']';

	if ( $verbosity_alert == 2 ) $alert_message .= $Entities->getMessageForHistory( $Last_ID );

	$Security->updateHistory( 'L_ALERT_ENT', $alert_message, 2, $L_Status );

	exit();


 case 'ENT_M':
	$Return_Page = $Script . '?action=ENT_V';

	if ( ($ent_id = $Security->valueControl( $_GET[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (ent_id)', $Return_Page, 1 ) );
		break;
	}

	$Entity = $Entities->get( $ent_id );
	
	print(
	 "     <form name=\"m_entity\" method=\"post\" action=\"" . $Script . "?action=ENT_MX\">\n" .
	 "      <input type=\"hidden\" name=\"ent_id\" value=\"" . $ent_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Entity_Modify . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Code . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Code\" size=\"10\" maxlength=\"10\" value=\"" . $Security->XSS_Protection( $Entity->ent_code ) . "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Label . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Label\" size=\"35\" maxlength=\"35\" value=\"" . $Security->XSS_Protection( $Entity->ent_label ) . "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Modify . "\" /><a class=\"button\" href=\"" . $Script . "?action=ENT_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.m_entity.Code.focus();\n" .
	 "     </script>\n"
	);
	
	break;


 case 'ENT_MX':
	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (ent_id)'
			) );

		exit();
	}

	if ( ! $Code = $Security->valueControl( $_POST[ 'Code' ] ) ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (Code)'
			) );

		exit();
	}

	if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (Label)'
			) );

		exit();
	}


	try {
		$Entities->set( $ent_id, $Code, $Label );

		echo json_encode( array(
			'Status' => 'success',
			'Message' => $L_Entity_Modified
			) );

		$L_Status = LOG_INFO;
		$L_Message = 'L_Entity_Modified';
		$Message = ${$L_Message};
	} catch( PDOException $e ) {
		$L_Status = LOG_ERR;
		$L_Message = 'L_ERR_MODI_Entity';
		$Message = ${$L_Message};

		echo json_encode( array(
			'Status' => 'error',
			'Message' => $Message
			) );
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			$L_Message = 'L_ERR_DUPL_Entity';
		} else {
			$L_Message = 'L_ERR_MODI_Entity';
		}

		$L_Status = LOG_ERR;
		$Message = ${$L_Message};

		echo json_encode( array(
			'Status' => 'error',
			'Message' => $Message
			) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $ent_id . ']';

	if ( $verbosity_alert == 2 ) $alert_message .= $Entities->getMessageForHistory( $ent_id );

	$Security->updateHistory( 'L_ALERT_ENT', $alert_message, 3, $L_Status );

	exit();


 case 'ENT_D':
	$Return_Page = $Script . '?action=ENT_V';
	
	if ( ($ent_id = $Security->valueControl( $_GET[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_ERR_CREA_Entity . ' (ent_id)', $Return_Page, 1 ) );
		break;
	}

	$Entity = $Entities->get( $ent_id );
	
	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=ENT_DX\">\n" .
	 "      <input type=\"hidden\" name=\"ent_id\" value=\"" . $ent_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Entity_Delete . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Code . "</td>\n" .
	 "        <td class=\"pair\">" . 
	 $Security->XSS_Protection( $Entity->ent_code ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Label . "</td>\n" .
	 "        <td class=\"pair\">" . 
	 $Security->XSS_Protection( $Entity->ent_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a class=\"button\" href=\"" . $Script . "?action=ENT_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'ENT_DX':
	$Return_Page = $Script . '?action=ENT_V';
 
	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_Invalid_Value . ' (ent_id)'
			);

		print( json_encode( $Resultat ) );

		exit();
	}

	try {
		$Entity = $Entities->get( $ent_id );

		$Entities->delete( $ent_id );

		$L_Status = LOG_INFO;
		$L_Message = 'L_Entity_Deleted';

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => ${$L_Message}
			);

		print( json_encode( $Resultat ) );
	} catch( PDOException $e ) {
		$L_Status = LOG_ERR;
		$L_Message = 'L_ERR_DELE_Entity';
		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $Message
			);

		print( json_encode( $Resultat ) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $ent_id . ']';

	if ( $verbosity_alert == 2 ) $alert_message .= $Entities->getMessageForHistory( $ent_id, $Entity );

	$Security->updateHistory( 'L_ALERT_ENT', $alert_message, 4, $L_Status );

	exit();


 case 'CVL_V':
	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'last_name';
	}

 	if ( array_key_exists( 'rp', $_GET ) ) {
 		if ( $_GET[ 'rp' ] != '' ) {
 			switch( $_GET[ 'rp' ] ) {
 			 default:
	 			$Prev_Page = $Script;
	 			break;

	 		 case 'home':
	 		 case 'users':
	 		 case 'admin':
	 			$Prev_Page = 'SM-' . $_GET[ 'rp' ] . '.php';
	 			break;

	 		 case 'users_m':
	 			$Prev_Page = 'SM-users.php?action=M&idn_id=' . $_GET[ 'idn_id' ];
	 			break;
 			}
 		}
  	} else {
		$Prev_Page = $Script;
	}

 	
	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$addButton = "<span style=\"float: right\"><a class=\"button\" href=\"javascript:putAddCivility('".
		    addslashes($L_Civility_Create)."','".$L_First_Name."','".$L_Last_Name."','".$L_Sex."','".
    		$L_Man."','".$L_Woman."','".$L_Cancel."','".$L_Create."')\">" . $L_Create . "</a></span>" ;
		$returnButton = "<span style=\"float: right\"><a class=\"button\" href=\"" .
		 $Prev_Page . "\">" . $L_Return . "</a></span>"; // L_Users_List_Return
		
		print( "     <table class=\"table-bordered\" cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"4\">" . $L_List_Civilities . $addButton . $returnButton . "</th>\n" .
		 "       </tr>\n" );
		
		print( "       <tr class=\"pair\">\n" );

		if ( $orderBy == 'first_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'first_name-desc';
		} else {
			if ( $orderBy == 'first_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'first_name';
		}
		print( "        <td onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\" width=\"25%\">" . 
		 $L_First_Name . "</td>\n" );


		if ( $orderBy == 'last_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'last_name-desc';
		} else {
			if ( $orderBy == 'last_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'last_name';
		}
		print( "        <td onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\" width=\"35%\">" . 
		 $L_Last_Name . "</td>\n" );


		if ( $orderBy == 'sex' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'sex-desc';
		} else {
			if ( $orderBy == 'sex-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'sex';
		}
		print( "        <td onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\" width=\"20%\">" . 
		 $L_Sex . "</td>\n" );

		print( "        <td width=\"20%\">" . $L_Actions . "</td>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody id=\"listeSecrets\">\n" );

		 
		$List_Civilities = $Civilities->listCivilities( $orderBy );

		
		foreach( $List_Civilities as $Civility ) {
			if ( $Civility->cvl_sex == 0 )
				$Flag_Sex = $L_Man;
			else
				$Flag_Sex = $L_Woman;

			print( "       <tr id=\"civility-".$Civility->cvl_id."\" class=\"surline\">\n" .
			 "        <td id=\"first_name-".$Civility->cvl_id."\">" . 
			 $Security->XSS_Protection( $Civility->cvl_first_name ) . "</td>\n" .
			 "        <td id=\"last_name-".$Civility->cvl_id."\">" . 
			 $Security->XSS_Protection( $Civility->cvl_last_name ) . "</td>\n" .
			 "        <td id=\"sex-".$Civility->cvl_id."\">" . $Flag_Sex . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"javascript:modifyCivility('".$Civility->cvl_id."','".$L_Man.
			 "','".$L_Woman."','".$L_Cancel."','".$L_Modify."')\"><img class=\"no-border\" src=\"" .
			 URL_PICTURES . "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"javascript:confirmDeleteCivility('".$Civility->cvl_id."','".
			 addslashes($L_Civility_Delete)."','".addslashes($L_Confirm_Delete_Civility)."','".$L_Cancel."','".$L_Confirm.
			 "');\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . $L_Delete .
			 "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"4\">Total : <span id=\"total\" class=\"green\">" . 
		 count( $List_Civilities ) . "</span>" . $addButton . $returnButton . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'CVL_CX':
	if ( ! $Last_Name = $Security->valueControl( $_POST[ 'Last_Name' ] ) ) {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_Invalid_Value . ' (Last_Name)'
			);

		print( json_encode( $Resultat ) );

		exit();
	}

	if ( ! $First_Name = $Security->valueControl( $_POST[ 'First_Name' ] ) ) {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_Invalid_Value . ' (First_Name)'
			);

		print( json_encode( $Resultat ) );

		exit();
	}

	if ( ($Sex = $Security->valueControl( $_POST[ 'Sex' ], 'NUMERIC' )) === FALSE ) {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_Invalid_Value . ' (Sex)'
			);

		print( json_encode( $Resultat ) );

		exit();
	}


	try {
		$Last_ID = '';

		$Civilities->set( '', $Last_Name, $First_Name, $Sex, '', '' );

		$Last_ID = $Civilities->LastInsertId;

		$L_Status = LOG_INFO;
		$L_Message = 'L_Civility_Created';
		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'IdCivility' => $Last_ID,
			'Message' => $Message,
            'Script' => $Script,
            'URL_PICTURES' => URL_PICTURES,
            'L_Modify' => $L_Modify,
            'L_Delete' => $L_Delete,
            'L_Warning' => $L_Warning,
            'L_Cancel' => $L_Cancel,
            'L_Confirm_Delete_Civility' => $L_Confirm_Delete_Civility,
            'L_Confirm' => $L_Confirm,
            'L_Man' => $L_Man,
            'L_Woman' => $L_Woman
			);
	} catch( PDOException $e ) {
		$L_Status = LOG_ERR;
		$L_Message = 'L_ERR_CREA_Civility';
		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $Message
			);
	} catch( Exception $e ) {
		$L_Status = LOG_ERR;

		if ( $e->getCode() == 1062 ) {
			$L_Message = 'L_ERR_DUPL_Civility';
		} else {
			$L_Message = 'L_ERR_CREA_Civility';
		}

		$Message = ${$L_Message};

		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $Message
			);
	}

	print( json_encode( $Resultat ) );
 
 	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $Last_ID . ']';

 	if ( $verbosity_alert == 2 ) $alert_message .= $Civilities->getMessageForHistory( $Last_ID );

	$Security->updateHistory( 'L_ALERT_CVL', $alert_message, 2, $L_Status );

	exit();


 case 'CVL_MX':
	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (cvl_id)'
		) );

		exit();
	}

	if ( ! $Last_Name = $Security->valueControl( $_POST[ 'Last_Name' ] ) ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (Last_Name)'
		) );

		exit();
	}

	if ( ! $First_Name = $Security->valueControl( $_POST[ 'First_Name' ] ) ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (First_Name)'
		) );

		exit();
	}

	if ( ($Sex = $Security->valueControl( $_POST[ 'Sex' ], 'NUMERIC' )) == -1 ) {
		echo json_encode( array(
			'Status' => 'error',
			'Message' => $L_Invalid_Value . ' (Sex)'
		) );

		exit();
	}

	try {
		$Civilities->set( $cvl_id, $Last_Name, $First_Name, $Sex, '', '' );

		$L_Status = LOG_INFO;
		$L_Message = 'L_Civility_Modified';
		$Message = ${$L_Message};

		echo json_encode( array(
			'Status' => 'success',
			'Message' => $Message
		) );
	} catch( PDOException $e ) {
		$L_Status = LOG_INFO;
		$L_Message = 'L_ERR_MODI_Civility';
		$Message = ${$L_Message};

		echo json_encode( array(
			'Status' => 'error',
			'Message' => $Message
		) );
	} catch( Exception $e ) {
		$L_Status = LOG_ERR;
		$L_Message = 'L_ERR_MODI_Civility';

		if ( $e->getCode() == 1062 ) {
			$L_Message = 'L_ERR_DUPL_Civility';
		}

		$Message = ${$L_Message};

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $Message
        ) );
	}

 	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $cvl_id . ']';

 	if ( $verbosity_alert == 2 ) $alert_message .= $Civilities->getMessageForHistory( $cvl_id );

	$Security->updateHistory( 'L_ALERT_CVL', $alert_message, 3, $L_Status );

	exit();


 case 'CVL_DX':
	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (cvl_id)'
            ) );

		exit();
	}

	try {
		$Civility = $Civilities->get( $cvl_id );

		$Civilities->delete( $cvl_id );
		
		$L_Status = LOG_INFO;
		$L_Message = 'L_Civility_Deleted';

        echo json_encode( array(
            'Status' => 'success',
            'Message' => ${$L_Message}
            ) );
	} catch( PDOException $e ) {
		$L_Status = LOG_ERR;
		$L_Message = $L_ERR_DELE_Civility;

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Message . ' ('.$e->getMessage().')'
            ) );

		exit();
	}

 	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $cvl_id . ']';

 	if ( $verbosity_alert == 2 ) $alert_message .= $Civilities->getMessageForHistory( $cvl_id, $Civility );

	$Security->updateHistory( 'L_ALERT_CVL', $alert_message, 4, $L_Status );

	exit();


 case 'RST_PWDX':
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (idn_id)'
            ) );

		exit();
	}

	try {
		$Authentication->resetPassword( $idn_id );

		$Message = $L_Password_Reseted;
		$L_Message = 'L_Password_Reseted';
		$L_Status = LOG_INFO;

        echo json_encode( array(
            'Status' => 'success',
            'Message' => $Message
            ) );
	} catch( PDOException $e ) {
		$Message = $L_ERR_RST_Password;
		$L_Message = 'L_ERR_RST_Password';
		$L_Status = LOG_ERR;

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $Message
            ) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $idn_id . ']';

	if ( $verbosity_alert == 2 ) {
		$alert_message .= $Identities->getUserForHistory( $idn_id );
	}

	$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, $L_Status );

	exit();


 case 'P':
	include( DIR_LIBRARIES . '/Class_IICA_Profiles_PDO.inc.php' );
	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	
	$Profiles = new IICA_Profiles();

	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Script, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );
	
	$Action_Button = "<a class=\"button\" href=\"javascript:putAddProfile();\" title=\"" . $L_Profile_Create . "\">&nbsp;+&nbsp;</a>" ;
	
	print( "     <form method=\"post\" action=\"" . $Script . "?action=PX&idn_id=" .
	 $idn_id . "\">\n" .
	 "      <table style=\"margin:10px auto;width:60%\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Users_Profiles . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle td-aere\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"pair blue1 bold td-aere\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . " - " . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle td-aere\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"pair green bold td-aere\">" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . " " .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) .
	 "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Username . "</td>\n" .
	 "        <td class=\"bg-light-grey td-aere\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right td-aere\">" . $L_Associated_Profiles . "</td>\n" .
	 "        <td>\n" .
//	 "         <div id=\"dashboard\">\n" .
	 "         <table class=\"table-bordered table-max inner\">\n" .
	 "          <thead>\n" .
	 "           <tr><th colspan=\"3\" class=\"align-right\"><span>" . $Action_Button . "</span></th></tr>\n" .
	 "          </thead>\n" .
	 "          <tbody id=\"listeSecrets\">\n" );

	$List_Profiles = $Profiles->listProfiles();

	$List_Profiles_Associated = $Identities->listProfiles( $idn_id );
	
	$BackGround = 'pair';


	if ( $List_Profiles == array() ) {
			print( "          <tr>\n" .
			 "           <td class=\"bg-green td-aere\">" . $L_No_Profile . "</td>\n" .
			 "          </tr>\n" );
	} else {
		foreach( $List_Profiles as $Profile ) {
			if ( array_key_exists( $Profile->prf_id, $List_Profiles_Associated ) ) {
				$Validate = ' checked';
			} else {
				$Validate = '';
			}

			if ( $BackGround == 'pair' ) $BackGround = 'impair';
			else $BackGround = 'pair';

			
			print( "          <tr class=\"" . $BackGround . " td-aere\">\n" .
			 "           <td class=\"align-middle align-center\"><input type=\"checkbox\" name=\"" . $Profile->prf_id . 
			 "\" id=\"P_" . $Profile->prf_id . "\"" . $Validate . " /></td>\n" .
			 "           <td class=\"td-aere align-middle\"><label for=\"P_" . $Profile->prf_id . "\">" .
			 stripslashes( $Profile->prf_label ) . "</label></td>\n" .
			 "           <td class=\"align-center\"><a class=\"simple\" href=\"?action=PRF_G&prf_id=" . $Profile->prf_id . "&home=P&idn_id=". $_GET['idn_id'] . "\">" .
			 "<img src=\"" . URL_PICTURES . "/b_usrscr_2.png\" class=\"no-border\" alt=\"" . $L_Groups_Associate . "\" title=\"" . $L_Groups_Associate . "\" /></a></td>\n" .
			 "          </tr>\n" );
		}
	}

	print( "          </tbody>\n" .
	 "          <tfoot>\n" .
	 "          <tr>\n" .
	 "           <th colspan=\"3\" class=\"td-aere\">Total : <span class=\"green bold\">" . 
	 count( $List_Profiles ) . "</span><span class=\"div-right\">" . $Action_Button . "</span></th>\n" .
	 "          </tr>\n" .
	 "          </tfoot>\n" .
	 "         </table>\n" .
// 	 "         </div>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Associate . "\" /><a class=\"button\" href=\"" . $Script . "\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" 
	);
	
	break;


 case 'PX':
	$Return_Page = $Script . '?action=P&idn_id=' .
	 $_GET[ 'idn_id' ];
 
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->returnPage( $L_Title, $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		exit();
	}

	try {
		$Identities->deleteProfiles( $idn_id );
		
		if ( $_POST != array() ) {
			if ( $verbosity_alert == 2 ) {
				$tmp = $Identities->getCivility( '', $idn_id );
				$alert_message = $PageHTML->getTextCode( 'L_Association_Terminated' ) . ' [' . $tmp->cvl_first_name . ' ' . $tmp->cvl_last_name . ' => ';
			} else {
				$alert_message = $PageHTML->getTextCode( 'L_Association_Terminated' ) . ' [' . $idn_id . ' => ';
			}

			$Profils = '';

			foreach( $_POST as $Key => $Value ) {
				$Identities->addProfile( $idn_id, $Key );

				if ( $Profils != '' ) $Profils .= ', ';

				if ( $verbosity_alert == 2 ) {
					$tmp = $Identities->getProfile( $Key );
					$Profils .= $tmp->prf_label;
				} else {
					$Profils .= $Key;
				}
			}

			$alert_message .= $Profils . ']';

			$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 2, LOG_INFO );
		}
	} catch( PDOException $e ) {
		print( $PageHTML->returnPage( $L_Title, $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		exit();
	}

	print( "<form method=\"post\" name=\"fInfoMessage\" action=\"" . $Script . "\">\n" .
		" <input type=\"hidden\" name=\"infoMessage\" value=\"". $L_Association_Terminated . "\" />\n" .
		"</form>\n" .
		"<script>document.fInfoMessage.submit();</script>\n" );

	break;


 case 'RST_ATTX':
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (idn_id)'
            ) );

		exit();
	}
 
	try {
		$Authentication->resetAttempt( $idn_id );

		$Message = $L_Attempt_Reseted;
		$L_Message = 'L_Attempt_Reseted';
		$L_Status = LOG_INFO;

        echo json_encode( array(
            'Status' => 'success',
            'Message' => $Message
            ) );
	} catch( PDOException $e ) {
		$Message = $L_ERR_RST_Attempt;
		$L_Message = 'L_ERR_RST_Attempt';
		$L_Status = LOG_ERR;

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $Message
            ) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $idn_id . ']';

	if ( $verbosity_alert == 2 ) {
		$alert_message .= $Identities->getUserForHistory( $idn_id );
	}

	$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, $L_Status );

    exit();


 case 'RST_EXPX':
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (idn_id)'
            ) );

		exit();
	}
 
	try {
		$Expiration_Date = $Authentication->resetExpirationDate( $idn_id );

		$Message = $L_Expiration_Date_Reseted;
		$L_Message = 'L_Expiration_Date_Reseted';
		$L_Status = LOG_INFO;

        echo json_encode( array(
            'Status' => 'success',
            'Message' => $Message,
            'Expiration_Date' => $Expiration_Date
            ) );
	} catch( PDOException $e ) {
		$Message = $L_ERR_RST_Expiration;
		$L_Message = 'L_ERR_RST_Expiration';
		$L_Status = LOG_ERR;

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $Message
            ) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $idn_id . ']';

	if ( $verbosity_alert == 2 ) {
		$alert_message .= $Identities->getUserForHistory( $idn_id );
	}

	$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, $L_Status );

	exit();


 case 'RST_DISX':
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (idn_id)'
        ) );

		exit();
	}

	if ( ($Action = $Security->valueControl( $_POST[ 'Status' ], 'NUMERIC' )) == -1 ) {
        echo json_encode( array(
            'Status' => 'error',
            'Message' => $L_Invalid_Value . ' (Status)'
        ) );

		exit();
	}


	try {
		$Authentication->setDisable( $idn_id, $Action );

        if ( $Action == 1 ) {
            $Disable_Color = "bg-orange";
            $Disable_Msg = $L_Yes;
            $Disable_Action = $L_To_Activate_User;
            $Disable_Status = 0;
            $Message = $L_User_Disabled;
			$L_Message = 'L_User_Disabled';
        } else {
            $Disable_Color = "bg-green";
            $Disable_Msg = $L_No;
            $Disable_Action = $L_To_Deactivate_User;
            $Disable_Status = 1;
            $Message = $L_User_Enabled;
			$L_Message = 'L_User_Enabled';
        }

		$L_Status = LOG_INFO;

        echo json_encode( array(
            'Status' => 'success',
            'Message' => $Message,
            'Disable_Color' => $Disable_Color,
            'Disable_Msg' => $Disable_Msg,
            'Disable_Action' => $Disable_Action,
            'Disable_Status' => $Disable_Status
        ) );
	} catch( PDOException $e ) {
		$Message = $L_ERR_RST_Disable;
		$L_Message = 'L_ERR_RST_Disable';
		$L_Status = LOG_INFO;

        echo json_encode( array(
            'Status' => 'error',
            'Message' => $Message
        ) );
	}

	$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $idn_id . ']';

	if ( $verbosity_alert == 2 ) {
		$alert_message .= $Identities->getUserForHistory( $idn_id );
	}

	$Security->updateHistory( 'L_ALERT_IDN', $alert_message, 3, $L_Status );

	exit();


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
		if ( $_GET[ 'rp'] = 'admin' ) $_SESSION[ 'p_action' ] = 'SM-admin.php';
	}

	if ( ! isset( $_SESSION[ 'p_action' ] ) ) {
		$_SESSION[ 'p_action' ] = 'SM-admin.php';
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
		 "      <tbody id=\"listeSecrets\">\n" );
				 
		$List_Profiles = $Profiles->listProfiles( $orderBy );

		foreach( $List_Profiles as $Profile ) {
			print( "       <tr id=\"profil_" . $Profile->prf_id . "\" class=\"surline\">\n" .
			 "        <td id=\"label_" . $Profile->prf_id . "\" class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Profile->prf_label ) . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"javascript:modifyProfile('" .
			 $Profile->prf_id . "');\">" .
			 "<img class=\"no-border\" src=\"" . URL_PICTURES . "/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"javascript:confirmDeleteProfile( '" . $Profile->prf_id . "');\">" .
			 "<img class=\"no-border\" src=\"" . URL_PICTURES . "/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF_G&prf_id=" . $Profile->prf_id .
			 "\"><img class=\"no-border\" src=\"" . URL_PICTURES . "/b_usrscr_2.png\" alt=\"" . $L_Groups_Associate . "\" title=\"" . $L_Groups_Associate . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"2\">Total : <span id=\"total\" class=\"green\">" . 
		 count( $List_Profiles ) . "</span>" . $Buttons . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	print( 
	 "     <div id=\"addProfile\" class=\"tableau_synthese hide modal\" style=\"top:35%;left:40%;\">\n".
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
	include( DIR_LABELS . '/' . $_SESSION['Language'] . '_SM-secrets.php');
	
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

			$Last_ID = $Profiles->LastInsertId;
		} catch( PDOException $e ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_ERR_CREA_Profile
				);

			$alert_message = $Profiles->getMessageForHistory( '', 'L_ERR_CREA_Profile' );

			$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 2, LOG_ERR );

			print( json_encode( $Resultat ) );

			exit();
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				$L_Message = 'L_ERR_DUPL_Profile';
				$Message = ${$L_Message};

				$Resultat = array(
					'Status' => 'error',
					'Title' => $L_Error,
					'Message' => $Message
					);
			} else {
				$L_Message = 'L_ERR_CREA_Profile';
				$Message = ${$L_Message};

				$Resultat = array(
					'Status' => 'error',
					'Title' => $L_Error,
					'Message' => $Message
					);
			}

			$alert_message = $Profiles->getMessageForHistory( '', $L_Message );

			$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 2, LOG_ERR );

			print( json_encode( $Resultat ) );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Created,
			'idProfile' => $Last_ID,
			'Label' => $Label,
			'URL_PICTURES' => URL_PICTURES,
			'L_Groups_Associate' => $L_Groups_Associate,
			'Script' => $Script,
			'L_Modify' => $L_Modify,
			'L_Delete' => $L_Delete,
			'L_Cancel' => $L_Cancel,
			'L_Warning' => $L_Warning,
			'L_Delete_Profile_Confirmation' => $L_Delete_Profile_Confirmation
			);

		$alert_message = $PageHTML->getTextCode( 'L_Profile_Created' ) . ' [' . $Last_ID . ']';

		if ( $verbosity_alert == 2 ) $alert_message .= $Profiles->getMessageForHistory( $Last_ID );

		$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 2, LOG_INFO );
	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);
	}

	print( json_encode( $Resultat ) );

	exit();


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
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				$L_Message = 'L_ERR_DUPL_Profile';
				$Message = ${$L_Message};
			} else {
				$L_Message = 'L_ERR_MODI_Profile';
				$Message = ${$L_Message};
			}

			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $Message
				);

			$alert_message = $PageHTML->getTextCode( $L_Message ) . ' [' . $prf_id . ']';

			if ( $verbosity_alert == 2 ) $alert_message .= $Profiles->getMessageForHistory( $prf_id );

			$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 3, LOG_ERR );

			print( json_encode( $Resultat ) );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Modified
			);

		print( json_encode( $Resultat ) );

		$alert_message = $PageHTML->getTextCode( 'L_Profile_Modified' ) . ' [' . $prf_id . ']';

		if ( $verbosity_alert == 2 ) $alert_message .= $Profiles->getMessageForHistory( $prf_id );

		$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 3, LOG_INFO );

		exit();
	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);

		print( json_encode( $Resultat ) );

		$Security->updateHistory( 'L_ALERT_PRF', $L_No_Authorize, 3 );

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
			$Profil = $Profiles->get( $prf_id );

			$Profiles->delete( $prf_id );
		} catch( PDOException $e ) {
			$Resultat = array(
				'Status' => 'error',
				'Title' => $L_Error,
				'Message' => $L_ERR_DELE_Profile
				);

			print( json_encode( $Resultat ) );

			$alert_message = $PageHTML->getTextCode( 'L_ERR_DELE_Profile' ) . ' [' . $prf_id . ']';

			if ( $verbosity_alert == 2 ) $alert_message .= $Profiles->getMessageForHistory( $prf_id, $Profil );

			$Security->updateHistory( 'L_ALERT_PRF', '[' . $prf_id . '] ' . $L_ERR_DELE_Profile, 4, LOG_ERR );

			exit();
		}

		$Resultat = array(
			'Status' => 'success',
			'Title' => $L_Success,
			'Message' => $L_Profile_Deleted
			);

		print( json_encode( $Resultat ) );

		$alert_message = $PageHTML->getTextCode( 'L_Profile_Deleted' ) . ' [' . $prf_id . ']';

		if ( $verbosity_alert == 2 ) $alert_message .= $Profiles->getMessageForHistory( $prf_id, $Profil );

		$Security->updateHistory( 'L_ALERT_PRF', $alert_message, 4, LOG_INFO );

		exit();
	} else {
		$Resultat = array(
			'Status' => 'error',
			'Title' => $L_Error,
			'Message' => $L_No_Authorize
			);

		print( json_encode( $Resultat ) );

		$Security->updateHistory( 'L_ALERT_PRF', $L_No_Authorize, 4 );

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

	if ( isset( $_GET['home'] ) ) {
		if ( $_GET['home'] == 'P' ) {
			$Return_Page = $Script . '?action=' . $_GET['home'] . '&idn_id=' . $_GET['idn_id'];
		}
	} else {
		$Return_Page = $_SERVER[ 'HTTP_REFERER' ];
	}
	

	$Groups = new IICA_Groups();

	$List_Groups = $Groups->listGroups();

	
	$Rights = new IICA_Referentials();

	$List_Rights = $Rights->listRights();


	if ( $Authentication->is_administrator() ) {
		print( "    <div id=\"dashboard\">\n" .
		 "    <form id=\"fAGSP\" method=\"post\" action=\"" . $Script .
		 "?action=PRF_GX&prf_id=" . $_GET[ 'prf_id' ] . "\">\n" .
		 "     <table class=\"table-bordered\" style=\"margin: 10px auto;width: 60%;\">\n" .
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

		//$manageGroups = "         <a class=\"button\" href=\"" . URL_BASE . "/SM-secrets.php?rp=users-prf_g&prf_id=" .
		$manageGroups = "         <a class=\"button\" href=\"javascript:putAddGroup();\" title=\"" . $L_Group_Create . "\">&nbsp;+&nbsp;</a>\n" ;
		
		print( "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Groups . "</td>\n" .
		 "        <td>\n" .
		 "         <table class=\"table-bordered\">\n" .
		 "          <thead>\n" .
		 "          <tr>\n" .
		 "           <th colspan=\"2\" class=\"align-right\">\n" .
		 $manageGroups .
		 "           </th>\n" .
		 "          </tr>\n" .
		 "          <tr class=\"pair\">\n" .
		 "           <td>" . $L_Label . "</td>\n" .
		 "           <td>" . $L_Rights . "</td>\n" .
		 "          </tr>\n" .
		 "          </thead>\n" .
		 "          <tbody id=\"listeSecrets\">\n" );
		

		foreach( $List_Groups as $Group ) {
			if ( array_key_exists( $Group->sgr_id, $List_Groups_Associated ) )
				$Status = ' checked ';
			else $Status = '';

			print( 
			 "          <tr>\n" .
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
		 "           <th colspan=\"2\" class=\"align-right\">\n" .
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
		 "<a class=\"button\" href=\"" . $Return_Page . "\">" . $L_Cancel .
		 "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "\n" .
		 "    </form>\n" .
		 "    </div>\n" );
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

	try {
		$Groups->deleteProfiles( '', $prf_id );
		
		$Store = '';
		
		if ( $_POST != array() ) {
			if ( $verbosity_alert == 2 ) {
				$tmp = $Identities->getProfile( $prf_id );
				$alert_message = $PageHTML->getTextCode( 'L_Association_Complited' ) . ' [' . $tmp->prf_label . ' => (';
			} else {
				$alert_message = $PageHTML->getTextCode( 'L_Association_Complited' ) . ' [' . $prf_id . ' => ';
			}

			foreach( $_POST as $Key => $Values ) {
				$Store_Key = explode( '_', $Key );
				$Store_Key = $Store_Key[ 1 ];

				if ( $verbosity_alert == 2 ) {
					$tmp = $Identities->getGroups( $Store_Key );
					$alert_message .= '(' . $tmp->sgr_label;
				} else {
					$alert_message .= '(' . $Store_Key;
				}

				$alert_message .= ' => ';

				$ListRights = '';

				foreach( $Values as $Value ) {
					$Groups->addProfile( $Store_Key, $prf_id, $Value );

					if ( $ListRights != '' ) $ListRights .= ', ';

					if ( $verbosity_alert == 2 ) $ListRights .= $PageHTML->getTextCode( 'L_Right_' . $Value );
					else $ListRights .= $Value;
				}

				$alert_message .= $ListRights .')';
			}

			$Security->updateHistory( 'L_ALERT_PRSG', $alert_message . ']', 2, LOG_INFO );
		}
	} catch( PDOException $e ) {
		$alert_message = $L_ERR_ASSO_Profile;
		
		$Security->updateHistory( 'L_ALERT_PRSG', $alert_message, 2, LOG_ERR );

		print( $PageHTML->returnPage( $L_Title, $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		break;
	}

	print( "<form method=\"post\" name=\"fInfoMessage\" action=\"" . $Return_Page . "\">\n" .
		" <input type=\"hidden\" name=\"infoMessage\" value=\"". $L_Association_Complited . "\" />\n" .
		"</form>\n" .
		"<script>document.fInfoMessage.submit();</script>\n" );

	break;


 case 'L_RIGHTS_X':
 	include( DIR_LIBRARIES . '/Class_IICA_Referentials_PDO.inc.php' );
 	include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );

 	$Referentials = new IICA_Referentials();

 	$Liste_Rights = $Referentials->listRights();
 	$Options = '';

 	foreach( $Liste_Rights as $Right ) {
 		$Options .= '<option value="' . $Right->rgh_id . '">' . ${$Right->rgh_name} . '</option>';
 	}

    echo json_encode( array(
        'liste_rights' => $Options
    ) ) ;
    
    exit();


 case 'L_ADD_PROFILE_X':
    echo json_encode( array(
        'Title' => $L_Profile_Create,
        'Label' => $L_Label,
        'Cancel' => $L_Cancel,
        'Create' => $L_Create
    ) ) ;
    
	exit();


 case 'L_MODIF_PROFILE_X':
    echo json_encode( array(
        'Cancel' => $L_Cancel,
        'Modify' => $L_Modify
    ) ) ;
    
    exit();


 case 'L_DELETE_PROFILE_X':
    echo json_encode( array(
        'Message' => $L_Delete_Profile_Confirmation,
        'Warning' => $L_Warning,
        'Cancel' => $L_Cancel,
        'Confirm' => $L_Confirm
    ) );
    
    exit();
}

print(  "   </div> <!-- fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( 1 ) .
 $PageHTML->piedPageHTML() );

?>
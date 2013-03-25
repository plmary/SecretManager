<?php

/**
* Ce script gère les utilisateurs.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.6
* @date 2013-02-18
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

if ( ! isset( $_SESSION[ 'idn_id' ] ) )
	header( 'Location: https://' . $Server . dirname( $Script ) . '/SM-login.php' );

if ( ! array_key_exists( 'HTTPS', $_SERVER ) )
	header( 'Location: https://' . $Server . $URI );

$Action = '';
$Choose_Language = 0;

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
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-login.php' );
include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_' . basename( $Script ) );

include( 'Libraries/Class_HTML.inc.php' );
include( 'Libraries/Config_Hash.inc.php' );
include( 'Libraries/Class_IICA_Identities_PDO.inc.php' );
include( 'Libraries/Class_IICA_Civilities_PDO.inc.php' );
include( 'Libraries/Class_IICA_Entities_PDO.inc.php' );
include( 'Libraries/Class_Security.inc.php' );
include( 'Libraries/Class_IICA_Parameters_PDO.inc.php' );


$PageHTML = new HTML();

$Identities = new IICA_Identities( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

$Civilities = new IICA_Civilities( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

$Entities = new IICA_Entities( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

$Security = new Security();

$Parameters = new IICA_Parameters( 
 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );


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

	
print( $PageHTML->enteteHTML( $L_Title, $Choose_Language ) .
 "   <!-- debut : zoneTitre -->\n" .
 "   <div id=\"zoneTitre\">\n" .
 "    <div id=\"icon-users\" class=\"icon36\"></div>\n" .
 "    <span id=\"titre\">" . $L_Title . "</span>\n" .
 $PageHTML->afficherActions( $Authentication->is_administrator() ) .
 "   </div> <!-- fin : zoneTitre -->\n" .
 "\n" .
 "   <!-- debut : zoneGauche -->\n" .
 "   <div id=\"zoneGauche\" >&nbsp;</div> <!-- fin : zoneGauche -->\n" .
 "\n" .
 "   <!-- debut : zoneMilieuComplet -->\n" .
 "   <div id=\"zoneMilieuComplet\">\n" .
 "\n" );


if ( array_key_exists( 'orderby', $_GET ) ) {
	$orderBy = $_GET[ 'orderby' ];
} else {
	$orderBy = 'entity';
}


switch( $Action ) {
 default:
	include( 'Libraries/Config_Authentication.inc.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	
	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right;"><a class="button" href="' . $Script . '?action=add">' . $L_Create . '</a></span>' ;

		if ( array_key_exists( 'rp', $_GET ) ) {
			switch( $_GET[ 'rp' ] ) {
			 case 'home':
				$returnButton = "<span style=\"float: right\">" .
				 "<a class=\"button\" href=\"SM-home.php\">" . $L_Return . "</a></span>";
				break;

			 default:
				$returnButton = '';
				break;
			}
			
			$Buttons = $addButton . $returnButton;
		} else {
			$Buttons = $addButton ;
		}
		
		print( "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"9\">" . $L_List_Users . $Buttons . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		 
		$List_Identities = $Identities->detailedListIdentities( $orderBy );
		
		print( "       <tr class=\"pair\">\n" );
	 

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
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Administrator . "</th>\n" );
		
		 
		if ( $orderBy == 'auditor' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'auditor-desc';
		} else {
			if ( $orderBy == 'auditor-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'auditor';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Auditor . "</th>\n" .
		 "        <th>" . $L_Status . "</th>\n" .
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
				$Flag_Admin = '<img class="no-border" src="Pictures/bouton_non_coche.gif" alt="Ko" />';
			} else {
				$Flag_Admin = '<img class="no-border" src="Pictures/bouton_coche.gif" alt="Ko" />';
		  	}


			if ( $Identity->idn_auditor == 0 ) {
				$Flag_Audit = '<img class="no-border" src="Pictures/bouton_non_coche.gif" alt="Ko" />';
			} else {
				$Flag_Audit = '<img class="no-border" src="Pictures/bouton_coche.gif" alt="Ko" />';
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
		
			if ( $Flag_Status == 1 ) {
				$Flag_Status = '<img src="Pictures/s_attention.png" class="no-border" alt="Ko" title="' . $Msg_Error . '" />';
			} else {
				$Flag_Status = '<img src="Pictures/s_okay.png" class="no-border" alt="Ok" title="Ok" />';
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
			 "        <td class=\"align-center align-middle\">" . $Flag_Audit . "</td>\n" .
			 "        <td class=\"align-center align-middle\">" . $Flag_Status . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=M&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_usredit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=D&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_usrdrop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=V&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_usrcheck.png\" alt=\"" . $L_Verify . "\" title=\"" . $L_Verify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=P&idn_id=" . $Identity->idn_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_usrlist.png\" alt=\"" .
			  $L_Profiles . "\" title=\"" . $L_Profiles_Associate . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"9\">Total : <span class=\"green\">" . 
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
	 "        <td>" . $L_Entity . "</td>\n" .
	 "        <td>\n" .

	 "         <table style=\"border: 1px solid grey;\">\n" .
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

	 "         <table style=\"border: 1px solid grey;\">\n" .
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
	 "        <td><input type=\"text\" name=\"Username\" size=\"20\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Rights . "</td>\n" .
	 "        <td>\n" .

	 "         <table style=\"border: 1px solid grey;\">\n" .
	 "          <tr>\n" .
	 "           <td><label for=\"iAdministrator\">" . $L_Administrator . "</label></td>\n" .
	 "           <td><input id=\"iAdministrator\" name=\"Administrator\" type=\"checkbox\" /></td>\n" .
	 "           <td><label for=\"iAuditor\">" . $L_Auditor . "</label></td>\n" .
	 "           <td><input id=\"iAuditor\" name=\"Auditor\" type=\"checkbox\" /></td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .

	 "        </td>\n" .
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
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Config_Authentication.inc.php' );
	
	if ( isset( $_POST[ 'Administrator' ] ) ) {
		if ( $_POST[ 'Administrator' ] == 'on' )
			$SuperAdmin = 1;
	} else {
		$SuperAdmin = 0;
	}
	
	if ( isset( $_POST[ 'Auditor' ] ) ) {
		if ( $_POST[ 'Auditor' ] == 'on' )
			$Auditor = 1;
	} else {
		$Auditor = 0;
	}


	// ===========================================================
	// Calcule un nouveau grain de sel spécifique à l'utilisateur.
	$size = 8;
	$complexity = 2; // Majuscules, Minuscules et Chiffres
		
	$Salt = $Security->passwordGeneration( $size, $complexity );
	
	$Authenticator = sha1( $_Default_Password . $Salt );


	if ( ! $Username = $Security->valueControl( $_POST[ 'Username' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Username)', $Return_Page, 1 ) );
		break;
	}

	if ( ($ent_id = $Security->valueControl( $_POST[ 'Id_Entity' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Id_Entity)', $Return_Page, 1 ) );
		break;
	}

	if ( ($cvl_id = $Security->valueControl( $_POST[ 'Id_Civility' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Id_Civility)', $Return_Page, 1 ) );
		break;
	}
	
	try {
		$Identities->set( '', $Username, $Authenticator, 1, 0,
		 $SuperAdmin, $Auditor, $ent_id, $cvl_id, $Salt );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_CREA_Identity, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->infoBox( $L_ERR_DUPL_Identity, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->infoBox( $L_ERR_CREA_Identity, $Return_Page, 1 ) );
		}
		break;
	}


	print( $PageHTML->infoBox( $L_User_Created, $Return_Page, 2 ) );
	break;


 case 'D':
	$Return_Page = 'https://' . $Server . $Script;
 
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
		$Flag_Administrator = "<img class=\"no-border\" src=\"Pictures/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Administrator = "<img class=\"no-border\" src=\"Pictures/bouton_non_coche.gif\" alt=\"Ko\" />";

	if ( $Identity->idn_auditor == 1 )
		$Flag_Auditor = "<img class=\"no-border\" src=\"Pictures/bouton_coche.gif\" alt=\"Ok\" />";
	else
		$Flag_Auditor = "<img class=\"no-border\" src=\"Pictures/bouton_non_coche.gif\" alt=\"Ko\" />";

	print( "     <form name=\"deleteEntity\" method=\"post\" action=\"" . $Script . 
	 "?action=DX&idn_id=" . $idn_id . "\">\n" .
	 "      <input type=\"hidden\" name=\"cvl_id\" value=\"" . $Identity->cvl_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 50%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_User_Delete . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Entity . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . ' - ' . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Civility . "</td>\n" .
	 "        <td class=\"bg-light-grey\">\n" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . ' ' .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Username . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Rights . "</td>\n" .
	 "        <td>\n" .

	 "         <table style=\"border: 1px solid grey;\">\n" .
	 "          <tr>\n" .
	 "           <td>" . $L_Administrator . "</td>\n" .
	 "           <td>" . $Flag_Administrator . "</td>\n" .
	 "           <td>" . $L_Auditor . "</td>\n" .
	 "           <td>" . $Flag_Auditor . "</td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .

	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input name=\"b_cancel\" type=\"submit\" class=\"button\" value=\"".
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
	$Return_Page = 'https://' . $Server . $Script;
 
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Identities->delete( $idn_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_DELE_Identity, $Return_Page, 1 ) );
		break;
	}

	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (cvl_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Civilities->delete( $cvl_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_DELE_Civility, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_User_Deleted, $Return_Page, 2 ) );
	break;


 case 'M':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Config_Authentication.inc.php' );

	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->get( $idn_id );
	
	if ( $Identity->idn_super_admin == 1 )
		$Flag_Check_Administrator = "checked";
	else
		$Flag_Check_Administrator = "";

	if ( $Identity->idn_auditor == 1 )
		$Flag_Check_Auditor = "checked";
	else
		$Flag_Check_Auditor = "";


	$T_Entities = $Entities->listEntities();
	$T_Civilities = $Civilities->listCivilities();

	
	print(
	 "     <form name=\"m_identity\" method=\"post\" action=\"" . $Script . "?action=MX\">\n" .
	 "      <input type=\"hidden\" name=\"idn_id\" value=\"" . $idn_id . 
	 "\" />\n" .
	 "      <table style=\"margin:10px auto;width:60%\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_User_Modify . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle\">" . $L_Entity . "</td>\n" .
	 "        <td>\n" .
	 "      	  <table style=\"border: 1px solid grey;\">\n" .
	 "          <tr>\n" .
	 "           <td><select name=\"ent_id\">\n" );

	foreach( $T_Entities as $Entity ) {
		if ( $Identity->ent_id == $Entity->ent_id )
			$Flag = "selected";
		else
			$Flag = "";

	 	print( "            <option value=\"" . $Entity->ent_id . "\" " . $Flag . ">" . 
	 	 $Security->XSS_Protection( $Entity->ent_code ) . " - " . 
	 	 $Security->XSS_Protection( $Entity->ent_label ) . "</option>\n" );
	}

	print( "           </select>\n" .
	 "           <a class=\"button\" href=\"" . $Script .
	 "?action=ENT_V&rp=users_m&idn_id=" . $idn_id . "\">" .
	 $L_Entities_Management . "</a></td>\n" .
	 "          </tr>\n" .
	 "      	</table>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle\">" . $L_Civility . "</td>\n" .
	 "        <td>\n" .
	 "         <table style=\"border: 1px solid grey;\">\n" .
	 "          <tr>\n" .
	 "           <td><select name=\"cvl_id\">\n" );
 	
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

	$Msg_Expiration_Date = '<span class="' . $Msg_Color . '">&nbsp;' .
	 $Identity->idn_expiration_date . '&nbsp;</span>';
	

	print( "           </select>\n" .
	 "           <a class=\"button\" href=\"" . $Script . "?action=CVL_V&rp=users_m&idn_id=" . $idn_id . "\">" .
	 $L_Civilities_Management . "</a></td>\n" .
 	"          </tr>\n" .
	 "         </table>\n" .

	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td><input name=\"Username\" type=\"text\" size=\"20\" value=\"" . 
	 $Security->XSS_Protection( $Identity->idn_login ). "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle\">" . $L_Rights . "</td>\n" .
	 "        <td>\n" .
	 "         <table style=\"border: 1px solid grey;\">\n" .
	 "          <tr>\n" .
	 "           <td><label for=\"iAdministrator\">" . $L_Administrator . "</label></td>\n" .
	 "           <td><input id=\"iAdministrator\" name=\"Administrator\" type=\"checkbox\" " . $Flag_Check_Administrator . " /></td>\n" .
	 "           <td><label for=\"iAuditor\">" . $L_Auditor . "</label></td>\n" .
	 "           <td><input id=\"iAuditor\" name=\"Auditor\" type=\"checkbox\" " . $Flag_Check_Auditor . " /></td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .
	 "        </td>\n" .
 	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Password . "</td>\n" .
	 "        <td><a class=\"button\" href=\"" . $Script. "?action=RST_PWD" .
	 "&idn_id=" . $idn_id . "\">" . $L_Authenticator_Reset . "</a></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Attempt . "</td>\n" .
	 "        <td><span class=\"" . $Attempt_Color . "\">&nbsp;" . $Identity->idn_attempt . "&nbsp;</span>&nbsp;/&nbsp; " .
	 $_Max_Attempt . " <a class=\"button\" href=\"" . $Script. "?action=RST_ATT" .
	 "&idn_id=" . $idn_id . "\">" . $L_Attempt_Reset . "</a></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Expiration_Date . "</td>\n" .
	 "        <td>" . $Msg_Expiration_Date . "&nbsp;<a class=\"button\" href=\"" . $Script. "?action=RST_EXP" .
	 "&idn_id=" . $idn_id . "\">" . $L_Expiration_Date_Reset . "</a></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Disabled . "</td>\n" .
	 "        <td><span class=\"" . $Disable_Color . "\">&nbsp;" . $Disable_Msg .
	 "&nbsp;</span>&nbsp;<a class=\"button\" href=\"" . $Script. "?action=RST_DIS" .
	 "&idn_id=" . $idn_id . "&status=" . $Disable_Status . "\">" . $Disable_Action . "</a></td>\n" .
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
	$Return_Page = 'https://' . $Server . $Script;

	if ( isset( $_POST[ 'Administrator' ] ) ) {
		if ( $_POST[ 'Administrator' ] == 'on' )
			$SuperAdmin = 1;
	} else {
		$SuperAdmin = 0;
	}
	
	if ( isset( $_POST[ 'Auditor' ] ) ) {
		if ( $_POST[ 'Auditor' ] == 'on' )
			$Auditor = 1;
	} else {
		$Auditor = 0;
	}


	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (ent_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (cvl_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $Username = $Security->valueControl( $_POST[ 'Username' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Username)', $Return_Page, 1 ) );
		break;
	}

	
	try {
		$Identities->set( $idn_id, $Username, '', 1, 0, $SuperAdmin, $Auditor, $ent_id,
		 $cvl_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_MODI_Identity, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->infoBox( $L_ERR_DUPL_Identity, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->infoBox( $L_ERR_MODI_Identity, $Return_Page, 1 ) );
		}
		break;
	}


	print( $PageHTML->infoBox( $L_User_Modified, $Return_Page, 2 ) );
	break;


 case 'V':
	$Return_Page = 'https://' . $Server . $Script;

	include( 'Libraries/Config_Authentication.inc.php' );
	
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );
	
	if ( $Identity->idn_super_admin == 1 )
		$Flag_Check_Administrator = "<img class=\"no-border\" src=\"Pictures/bouton_coche.gif\" alt=\"Ko\" />";
	else
		$Flag_Check_Administrator = "<img class=\"no-border\" src=\"Pictures/bouton_non_coche.gif\" alt=\"Ko\" />";


	if ( $Identity->idn_auditor == 1 )
		$Flag_Check_Auditor = "<img class=\"no-border\" src=\"Pictures/bouton_coche.gif\" alt=\"Ko\" />";
	else
		$Flag_Check_Auditor = "<img class=\"no-border\" src=\"Pictures/bouton_non_coche.gif\" alt=\"Ko\" />";


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
	 "     <table style=\"margin: 10px auto;width: 60%\">\n" .
	 "      <thead>\n" .
	 "      <tr>\n" .
	 "       <th colspan=\"2\">" . $L_User_View . "</th>\n" .
	 "      </tr>\n" .
	 "      </thead>\n" .
	 "      <tbody>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle\">" . $L_Entity . "</td>\n" .
	 "       <td>\n" .
	 "        <table style=\"border: 1px solid grey;width: 100%;\">\n" .
	 "         <tr><td class=\"pair blue1 bold\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . " - " . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td></tr>\n" .
	 "        </table>\n" .
	 "       </td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle\">" . $L_Civility . "</td>\n" .
	 "       <td>\n" .
	 "        <table style=\"border: 1px solid grey;width: 100%;\">\n" .
	 "         <tr>\n" .
	 "          <td>" . $L_First_Name . "</td>\n" .
	 "          <td class=\"pair green bold\">" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . "</td>\n" .
	 "          <td>" . $L_Last_Name . "</td>\n" .
	 "          <td class=\"pair green bold\">" . 
	 $Security->XSS_Protection( $Identity->cvl_last_name ) . "</td>\n" .
	 "          <td>" . $L_Sex . "</td>\n" .
	 "          <td class=\"pair green bold\">" . $Flag_Sex . "</td>\n" .
	 "         </tr>\n" .
	 "        </table>\n" .
	 "       </td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Change_Authenticator_Flag . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . $Flag_Change_Authenticator . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Attempt . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . $Identity->idn_attempt . ' / ' . $_Max_Attempt .
	 "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Disabled . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . $Flag_Disable . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Last_Connection . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_last_connection ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Expiration_Date . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_expiration_date ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right\">" . $L_Updated_Authentication . "</td>\n" .
	 "       <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_updated_authentication ) . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle\">" . $L_Administrator . "</td>\n" .
	 "       <td>" . $Flag_Check_Administrator . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td class=\"align-right align-middle\">" . $L_Auditor . "</td>\n" .
	 "       <td>" . $Flag_Check_Auditor . "</td>\n" .
	 "      </tr>\n" .
	 "      <tr>\n" .
	 "       <td>&nbsp;</td>\n" .
	 "       <td><input id=\"b_return\"type=\"submit\" class=\"button\" value=\"".
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
		$addButton = "<span style=\"float: right\"><a class=\"button\" href=\"" .
		 $Script . "?action=ENT_C\">" . $L_Create . "</a></span>" ;

		$returnButton = "<span style=\"float: right\"><a class=\"button\" href=\"" .
		 $Prev_Action . "\">" . $Return_Button . "</a></span>";
		
		print( "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_List_Entities . $addButton . $returnButton . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		 

		print( "       <tr class=\"pair\">\n" );


		if ( $orderBy == 'code' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'code-desc';
		} else {
			if ( $orderBy == 'code-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'code';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=ENT_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Code . "</th>\n" );

		 
		if ( $orderBy == 'label' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'label-desc';
		} else {
			if ( $orderBy == 'label-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'label';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=ENT_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Label . "</th>\n" );
		 

		print( "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );
		
		$List_Entities = $Entities->listEntities( 0, $orderBy );
		
		$BackGround = "pair";
		
		foreach( $List_Entities as $Entity ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";

			print( "       <tr class=\"" . $BackGround . "\">\n" .
			 "        <td>" . 
			 $Security->XSS_Protection( $Entity->ent_code ) . "</td>\n" .
			 "        <td>" . 
			 $Security->XSS_Protection( $Entity->ent_label ) . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=ENT_M&ent_id=" . $Entity->ent_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=ENT_D&ent_id=" . $Entity->ent_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"7\">Total : <span class=\"green\">" . 
		 count( $List_Entities ) . "</span>" . $addButton . $returnButton . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		$Return_Page = 'https://' . $Server . dirname( $Script ) . '/SM-home.php';

		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'ENT_C':
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';

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
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';

	if ( ! $Code = $Security->valueControl( $_POST[ 'Code' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Code)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Label)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Entities->set( '', $Code, $Label );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_CREA_Entity, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->infoBox( $L_ERR_DUPL_Entity, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->infoBox( $L_ERR_CREA_Entity, $Return_Page, 1 ) );
		}
		break;
	}


	print( $PageHTML->infoBox( $L_Entity_Created, $Return_Page, 2 ) );

	break;


 case 'ENT_M':
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';

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
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';
	
	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (ent_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $Code = $Security->valueControl( $_POST[ 'Code' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Code)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Label)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Entities->set( $ent_id, $Code, $Label );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_MODI_Entity, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->infoBox( $L_ERR_DUPL_Entity, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->infoBox( $L_ERR_MODI_Entity, $Return_Page, 1 ) );
		}
		break;
	}


	print( $PageHTML->infoBox( $L_Entity_Modified, $Return_Page, 2 ) );

	break;


 case 'ENT_D':
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';
	
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
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a class=\"button\" href=\"" . $Script . "?action=ENT_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'ENT_DX':
	$Return_Page = 'https://' . $Server . $Script . '?action=ENT_V';
 
	if ( ($ent_id = $Security->valueControl( $_POST[ 'ent_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (ent_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Entities->delete( $ent_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_DELE_Entity, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Entity_Deleted, $Return_Page, 2 ) );

	break;


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
		$addButton = "<span style=\"float: right\"><a class=\"button\" href=\"" . $Script . "?action=CVL_C\">" . $L_Create . "</a></span>" ;
		$returnButton = "<span style=\"float: right\"><a class=\"button\" href=\"" .
		 $Prev_Page . "\">" . $L_Return . "</a></span>"; // L_Users_List_Return
		
		print( "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"4\">" . $L_List_Civilities . $addButton . $returnButton . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		
		print( "       <tr class=\"pair\">\n" );

		if ( $orderBy == 'first_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'first_name-desc';
		} else {
			if ( $orderBy == 'first_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'first_name';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_First_Name . "</th>\n" );


		if ( $orderBy == 'last_name' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'last_name-desc';
		} else {
			if ( $orderBy == 'last_name-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'last_name';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Last_Name . "</th>\n" );


		if ( $orderBy == 'sex' ) {
			$tmpClass = 'order-select';
		
			$tmpSort = 'sex-desc';
		} else {
			if ( $orderBy == 'sex-desc' ) $tmpClass = 'order-select';
			else $tmpClass = 'order';
		
			$tmpSort = 'sex';
		}
		print( "        <th onclick=\"javascript:document.location='" . $Script . 
		 "?action=CVL_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Sex . "</th>\n" );

		print( "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );

		 
		$List_Civilities = $Civilities->listCivilities( 0, $orderBy );

		
		$BackGround = "pair";
		
		foreach( $List_Civilities as $Civility ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";

			if ( $Civility->cvl_sex == 0 )
				$Flag_Sex = $L_Man;
			else
				$Flag_Sex = $L_Woman;

			print( "       <tr class=\"" . $BackGround . "\">\n" .
			 "        <td>" . 
			 $Security->XSS_Protection( $Civility->cvl_first_name ) . "</td>\n" .
			 "        <td>" . 
			 $Security->XSS_Protection( $Civility->cvl_last_name ) . "</td>\n" .
			 "        <td>" . $Flag_Sex . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=CVL_M&id=" . $Civility->cvl_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=CVL_D&id=" . $Civility->cvl_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "        </td>\n" .
			 "       </tr>\n" );
		}
		
		print( "      </tbody>\n" .
		 "      <tfoot><tr><th colspan=\"4\">Total : <span class=\"green\">" . 
		 count( $List_Civilities ) . "</span>" . $addButton . $returnButton . "</th></tr></tfoot>\n" .
		 "     </table>\n" .
		 "\n" );
	} else {
		print( "<h1>" . $L_No_Authorize . "</h1>" );
	}

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'CVL_C':
	print( "     <form name=\"c_civility\" method=\"post\" action=\"" . $Script . "?action=CVL_CX\">\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Civility_Create . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_First_Name . "</td>\n" .
	 "        <td><input type=\"text\" name=\"First_Name\" size=\"25\" maxlength=\"25\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Last_Name . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Last_Name\" size=\"35\" maxlength=\"35\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Sex . "</td>\n" .
	 "        <td>\n" .
	 "         <select name=\"Sex\">\n" .
	 "          <option value=\"0\">" . $L_Man . "</option>\n" .
	 "          <option value=\"1\">" . $L_Woman . "</option>\n" .
	 "         </select>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Create . "\" /><a class=\"button\" href=\"" . $Script . "?action=CVL_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n" .
	 "     <script>\n" .
	 "document.c_civility.First_Name.focus();\n" .
	 "     </script>\n"
	);
   
	break;


 case 'CVL_CX':
	$Return_Page = 'https://' . $Server . $Script . '?action=CVL_V';
 
	if ( ! $Last_Name = $Security->valueControl( $_POST[ 'Last_Name' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Last_Name)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $First_Name = $Security->valueControl( $_POST[ 'First_Name' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (First_Name)', $Return_Page, 1 ) );
		break;
	}

	if ( ($Sex = $Security->valueControl( $_POST[ 'Sex' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Sex)', $Return_Page, 1 ) );
		break;
	}


	if ( $Civilities->deleted( $First_Name, $Last_Name ) ) {
		$Message = $L_Reactivated_Civility;
	} else {
		try {
			$Civilities->set( '', $Last_Name, $First_Name, $Sex, '', '' );
		} catch( PDOException $e ) {
			print( $PageHTML->infoBox( $L_ERR_CREA_Civility, $Return_Page, 1 ) );
			break;
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				print( $PageHTML->infoBox( $L_ERR_DUPL_Civility, $Return_Page, 1 ) );
			} else {
				print( $PageHTML->infoBox( $L_ERR_CREA_Civility, $Return_Page, 1 ) );
			}
			break;
		}

		$Message = $L_Civility_Created;
	}

	print( $PageHTML->infoBox( $Message, $Return_Page, 2 ) );

	break;


 case 'CVL_M':
	if ( ($cvl_id = $Security->valueControl( $_GET[ 'id' ], 'NUMERIC' )) == -1 ) {
		print( "    <!-- debut : dashboard -->" .
		 "    <div id=\"dashboard\">\n" .
		 "     <h1>" . $L_Invalid_Value . "</h1>" .
		 "    </div> <!-- fin : dashboard -->\n" );
		break;
	}

	$Civility = $Civilities->get( $cvl_id );
	
	$Flag_Man = '';
	$Flag_Woman = '';
	
	if ( $Civility->cvl_sex == 0 )
	  $Flag_Man = ' selected';
	else
		$Flag_Woman = ' selected';
	
	print( "     <form name=\"m_civility\" method=\"post\" action=\"" . $Script .
	 "?action=CVL_MX\">\n" .
	 "      <input type=\"hidden\" name=\"cvl_id\" value=\"" . $cvl_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Civility_Modify . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_First_Name . "</td>\n" .
	 "        <td><input type=\"text\" name=\"First_Name\" size=\"25\" maxlength=\"25\" value=\"" . $Security->XSS_Protection( $Civility->cvl_first_name ) . "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Last_Name . "</td>\n" .
	 "        <td><input type=\"text\" name=\"Last_Name\" size=\"35\" maxlength=\"35\" value=\"" . $Security->XSS_Protection( $Civility->cvl_last_name ) . "\" /></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Sex . "</td>\n" .
	 "        <td>\n" .
	 "         <select name=\"Sex\">\n" .
	 "          <option value=\"0\"" . $Flag_Man . ">" . $L_Man . "</option>\n" .
	 "          <option value=\"1\"" . $Flag_Woman . ">" . $L_Woman . "</option>\n" .
	 "         </select>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Modify . "\" /><a class=\"button\" href=\"" . $Script . "?action=CVL_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "      <script>\n" .
	 "document.m_civility.First_Name.focus();\n" .
	 "      </script>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'CVL_MX':
	$Return_Page = 'https://' . $Server . $Script . '?action=CVL_V';
 
	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (cvl_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $Last_Name = $Security->valueControl( $_POST[ 'Last_Name' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Last_Name)', $Return_Page, 1 ) );
		break;
	}

	if ( ! $First_Name = $Security->valueControl( $_POST[ 'First_Name' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (First_Name)', $Return_Page, 1 ) );
		break;
	}

	if ( ($Sex = $Security->valueControl( $_POST[ 'Sex' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (Sex)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Civilities->set( $cvl_id, $Last_Name, $First_Name, $Sex, '', '' );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_MODI_Civility, $Return_Page, 1 ) );
		break;
	} catch( Exception $e ) {
		if ( $e->getCode() == 1062 ) {
			print( $PageHTML->infoBox( $L_ERR_DUPL_Civility, $Return_Page, 1 ) );
		} else {
			print( $PageHTML->infoBox( $L_ERR_MODI_Civility, $Return_Page, 1 ) );
		}
		break;
	}


	print( $PageHTML->infoBox( $L_Civility_Modified, $Return_Page, 2 ) );

	break;


 case 'CVL_D':
	if ( ($cvl_id = $Security->valueControl( $_GET[ 'id' ], 'NUMERIC' )) == -1 ) {
		print( "    <!-- debut : dashboard -->" .
		 "    <div id=\"dashboard\">\n" .
		 "     <h1>" . $L_Invalid_Value . "</h1>" .
		 "    </div> <!-- fin : dashboard -->\n" );
		break;
	}

	$Civility = $Civilities->get( $cvl_id );
	
	if ( $Civility->cvl_sex == 0 )
	  $Flag_Sex = $L_Man;
	else
		$Flag_Sex = $L_Woman;
	
	print( "     <form method=\"post\" action=\"" . $Script . "?action=CVL_DX\">\n" .
	 "      <input type=\"hidden\" name=\"cvl_id\" value=\"" . $cvl_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 60%;\">\n" .
	 "       <thead>\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Civility_Delete . "</th>\n" .
	 "       </tr>\n" .
	 "       </thead>\n" .
	 "       <tbody>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_First_Name . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Civility->cvl_first_name ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Last_Name . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Civility->cvl_last_name ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>" . $L_Sex . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . $Flag_Sex . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Delete . "\" /><a class=\"button\" href=\"" . $Script . "?action=CVL_V\">" . $L_Cancel . "</a></td>\n" .
	 "       </tr>\n" .
	 "       </tbody>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'CVL_DX':
	$Return_Page = 'https://' . $Server . $Script . '?action=CVL_V';
 
	if ( ($cvl_id = $Security->valueControl( $_POST[ 'cvl_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (cvl_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Civilities->delete( $_POST[ 'cvl_id' ] );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_DELE_Civility, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Civility_Deleted, $Return_Page, 2 ) );

	break;


 case 'RST_PWD':
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Script, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );
	
	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=RST_PWDX\">\n" .
	 "      <input name=\"idn_id\" type=\"hidden\" value=\"" . $idn_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 50%\">\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Authenticator_Reset . "</th>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->ent_code . " - " . $Identity->ent_label .
	 "</td>" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->cvl_first_name . " " .
	 $Identity->cvl_last_name . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->idn_login . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Reset . "\" /><a href=\"" . $Script . "?action=M&idn_id=" . $idn_id . "\" class=\"button\">". $L_Return . "</a></td>\n" .
	 "       </tr>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'RST_PWDX':
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Script, 1 ) );
		break;
	}

	try {
		$Authentication->resetPassword( $idn_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_RST_Password, $Script, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Password_Reseted, $Script, 2 ) );
	break;


 case 'P':
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles( $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Script, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );
	
	$Action_Button = "<a class=\"button\" href=\"" . $Script. "?action=PRF_V" .
	 "&idn_id=" . $idn_id . "&store\">" . $L_Profiles_Management . "</a>" ;
	
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
	 "        <td class=\"align-right align-middle\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . " - " . 
	 $Security->XSS_Protection( $Identity->ent_label ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right align-middle\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . " " .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) .
	 "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td class=\"bg-light-grey\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td colspan=\"2\">&nbsp;</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Associated_Profiles . "</td>\n" .
	 "        <td>\n" .
	 "         " . $Action_Button . "\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td>\n" .
	 "         <table class=\"surline\" style=\"border: 1px solid grey;\">\n" );

	$List_Profiles = $Profiles->listProfiles();

	$List_Profiles_Associated = $Identities->listProfiles( $idn_id );
	
	$BackGround = 'pair';


	if ( $List_Profiles == array() ) {
			print( "          <tr>\n" .
			 "           <td class=\"bg-green\">" . $L_No_Profile . "</td>\n" .
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

			
			print( "          <tr class=\"" . $BackGround . " surline\">\n" .
			 "           <td><input type=\"checkbox\" name=\"" . $Profile->prf_id . 
			 "\" id=\"P_" . $Profile->prf_id . "\"" . $Validate . " /></td>\n" .
			 "           <td><label for=\"P_" . $Profile->prf_id . "\">" .
			 stripslashes( $Profile->prf_label ) . "</label></td>\n" .
			 "          </tr>\n" );
		}
	}

	print( "          <tr>\n" .
	 "           <td colspan=\"2\">Total : <span class=\"green bold\">" . 
	 count( $List_Profiles ) . "</span></td>\n" .
	 "          </tr>\n" .
	 "         </table>\n" .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td>\n" .
	 "         " . $Action_Button . "\n" .
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
	$Return_Page = 'https://' . $Server . $Script . '?action=P&idn_id=' .
	 $_GET[ 'idn_id' ];
 
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Identities->deleteProfiles( $idn_id );
		
		if ( $_POST != array() ) {
			foreach( $_POST as $Key => $Value ) {
				$Identities->addProfile( $idn_id, $Key );
			}
		}
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Association_Terminated, 'https://' . $Server . $Script, 2 ) );
	break;


 case 'RST_ATT':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Config_Authentication.inc.php' );
	
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );

	
	if ( $Identity->idn_attempt > $_Max_Attempt )
		$Attempt_Color = "bg-orange";
	else
		$Attempt_Color = "bg-green";


	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=RST_ATTX\">\n" .
	 "      <input name=\"idn_id\" type=\"hidden\" value=\"" . $idn_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 50%\">\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Attempt_Reset . "</th>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->ent_code . " - " . $Identity->ent_label .
	 "</td>" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->cvl_first_name . " " .
	 $Identity->cvl_last_name . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->idn_login . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Attempt . "</td>\n" .
	 "        <td><span class=\"" . $Attempt_Color . "\">&nbsp;" . $Identity->idn_attempt . "&nbsp;</span>&nbsp;/&nbsp; " .
	 $_Max_Attempt . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Reset . "\" /><a href=\"" . $Script . "?action=M&idn_id=" . $idn_id . "\" class=\"button\">". $L_Return . "</a></td>\n" .
	 "       </tr>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'RST_ATTX':
	$Return_Page = 'https://' . $Server . $Script;
 
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Authentication->resetAttempt( $idn_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_RST_Attempt, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Attempt_Reseted, $Return_Page, 2 ) );
	break;


 case 'RST_EXP':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Config_Authentication.inc.php' );
	
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );

	
	if ( $Identity->idn_attempt > $_Max_Attempt )
		$Attempt_Color = "bg-orange";
	else
		$Attempt_Color = "bg-green";


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

	$Msg_Expiration_Date = '<span class="' . $Msg_Color . '">&nbsp;' .
	 $Identity->idn_expiration_date . '&nbsp;</span>';


	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=RST_EXPX\">\n" .
	 "      <input name=\"idn_id\" type=\"hidden\" value=\"" . $idn_id . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 50%\">\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $L_Expiration_Date_Reset . "</th>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->ent_code . " - " . $Identity->ent_label .
	 "</td>" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->cvl_first_name . " " .
	 $Identity->cvl_last_name . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td class=\"pair\">" . $Identity->idn_login . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Expiration_Date . "</td>\n" .
	 "        <td>" . $Msg_Expiration_Date . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Reset . "\" /><a href=\"" . $Script . "?action=M&idn_id=" . $idn_id . "\" class=\"button\">". $L_Return . "</a></td>\n" .
	 "       </tr>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'RST_EXPX':
	$Return_Page = 'https://' . $Server . $Script;
 
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	try {
		$Authentication->resetExpirationDate( $idn_id );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_RST_Expiration, $Return_Page, 1 ) );
		break;
	}

	print( $PageHTML->infoBox( $L_Expiration_Date_Reseted, $Return_Page, 2 ) );
	break;


 case 'RST_DIS':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Config_Authentication.inc.php' );
	
	if ( ($idn_id = $Security->valueControl( $_GET[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	$Identity = $Identities->detailedGet( $idn_id );

	
	if ( $Identity->idn_disable == 1 ) {
		$Color = "bg-orange";
		$Msg = $L_Yes;
		$Title = $L_To_Activate_User;
		$Action = $L_Enabled;
	} else {
		$Color = "bg-green";
		$Msg = $L_No;
		$Title = $L_To_Deactivate_User;
		$Action = $L_Disabled;
	}

	if ( ($Status = $Security->valueControl( $_GET[ 'status' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (status)', $Return_Page, 1 ) );
		break;
	}


	print(
	 "     <form method=\"post\" action=\"" . $Script . "?action=RST_DISX\">\n" .
	 "      <input name=\"idn_id\" type=\"hidden\" value=\"" . $idn_id . "\" />\n" .
	 "      <input name=\"action\" type=\"hidden\" value=\"" . $Status . "\" />\n" .
	 "      <table style=\"margin: 10px auto;width: 50%\">\n" .
	 "       <tr>\n" .
	 "        <th colspan=\"2\">" . $Title . "</th>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Entity . "</td>\n" .
	 "        <td class=\"pair\">" . 
	 $Security->XSS_Protection( $Identity->ent_code ) . " - " . 
	 $Security->XSS_Protection( $Identity->ent_label ) .
	 "</td>" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Civility . "</td>\n" .
	 "        <td class=\"pair\">" . 
	 $Security->XSS_Protection( $Identity->cvl_first_name ) . " " .
	 $Security->XSS_Protection( $Identity->cvl_last_name ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Username . "</td>\n" .
	 "        <td class=\"pair\">" . 
	 $Security->XSS_Protection( $Identity->idn_login ) . "</td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td class=\"align-right\">" . $L_Disabled . "</td>\n" .
	 "        <td><span class=\"" . $Color . "\">&nbsp;" . $Msg . "&nbsp;</span></td>\n" .
	 "       </tr>\n" .
	 "       <tr>\n" .
	 "        <td>&nbsp;</td>\n" .
	 "        <td><input type=\"submit\" class=\"button\" value=\"". $Action . "\" /><a href=\"" . $Script . "?action=M&id=" . $idn_id . "\" class=\"button\">". $L_Return . "</a></td>\n" .
	 "       </tr>\n" .
	 "      </table>\n" .
	 "     </form>\n"
	);
	
	break;


 case 'RST_DISX':
	$Return_Page = 'https://' . $Server . $Script;
 
	if ( ($idn_id = $Security->valueControl( $_POST[ 'idn_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (idn_id)', $Return_Page, 1 ) );
		break;
	}

	if ( ($Action = $Security->valueControl( $_POST[ 'action' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (action)', $Return_Page, 1 ) );
		break;
	}


	try {
		$Authentication->setDisable( $idn_id, $Action );
	} catch( PDOException $e ) {
		print( $PageHTML->infoBox( $L_ERR_RST_Disable, $Return_Page, 1 ) );
		break;
	}

	if ( $Action == 1 ) 
		$Message = $L_User_Disabled;
	else
		$Message = $L_User_Enabled;

	print( $PageHTML->infoBox( $Message, $Return_Page, 2 ) );
	break;


 case 'PRF_V':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	
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

	if ( ! isset( $_SESSION[ 'p_action' ] ) ) {
		$_SESSION[ 'p_action' ] = 'SM-home.php';
	}

	if ( array_key_exists( 'orderby', $_GET ) ) {
		$orderBy = $_GET[ 'orderby' ];
	} else {
		$orderBy = 'label';
	}

	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	print( "    <div id=\"dashboard\">\n" );

	if ( $Authentication->is_administrator() ) {
		$listButtons = '<div id="view-switch-list-current" class="view-switch" style="float: right" title="' . $L_Group_List . '"></div>' .
		'<div id="view-switch-excerpt-current" class="view-switch" style="float: right" title="' . $L_Detail_List . '"></div>';
		
		$addButton = '<span style="float: right"><a class="button" href="' . $Script .
		 '?action=PRF_A">' . $L_Create . '</a></span>';
		$returnButton = '<span style="float: right"><a class="button" href="' . $_SESSION[ 'p_action' ] . '">' .
		 $L_Return . '</a></span>' ;
		
		$Buttons = $addButton . $returnButton;
		
		print( "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 95%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"2\">" . $L_List_Profiles . $Buttons . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" );
		
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
		 "?action=PRF_V&orderby=" . $tmpSort . "'\" class=\"" . $tmpClass . "\">" . 
		 $L_Label . "</th>\n" );

		print( "        <th>" . $L_Actions . "</th>\n" .
		 "       </tr>\n" );
		
		 
		$List_Profiles = $Profiles->listProfiles( $orderBy );

		$BackGround = "pair";
		
		foreach( $List_Profiles as $Profile ) {
			if ( $BackGround == "pair" )
				$BackGround = "impair";
			else
				$BackGround = "pair";

			print( "       <tr class=\"" . $BackGround . " surline\">\n" .
			 "        <td class=\"align-middle\">" . 
			 $Security->XSS_Protection( $Profile->prf_label ) . "</td>\n" .
			 "        <td>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF_M&prf_id=" . $Profile->prf_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_edit.png\" alt=\"" . $L_Modify . "\" title=\"" . $L_Modify . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF_D&prf_id=" . $Profile->prf_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_drop.png\" alt=\"" . $L_Delete . "\" title=\"" . $L_Delete . "\" /></a>\n" .
			 "         <a class=\"simple\" href=\"" . $Script .
			 "?action=PRF_G&prf_id=" . $Profile->prf_id .
			 "\"><img class=\"no-border\" src=\"Pictures/b_usrscr_2.png\" alt=\"" . $L_Groups_Associate . "\" title=\"" . $L_Groups_Associate . "\" /></a>\n" .
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

	print( "    </div> <!-- fin : dashboard -->\n" );

	break;


 case 'PRF_A':
	$Return_Page = 'https://' . $Server . $Script;
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	$P_Action = $_SERVER[ 'HTTP_REFERER' ];

	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	if ( $Authentication->is_administrator() ) {
		print( "    <form name=\"add_profil\" method=\"post\" action=\"" . $Script . "?action=PRF_AX\" />\n" .
		 "     <table cellspacing=\"0\" style=\"margin: 10px auto;width: 60%;\">\n" .
		 "      <thead>\n" .
		 "       <tr>\n" .
		 "        <th colspan=\"3\">" . $L_Profile_Create . "</th>\n" .
		 "       </tr>\n" .
		 "      </thead>\n" .
		 "      <tbody>\n" .
		 "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Label . "</th>\n" .
		 "        <td><input type=\"text\" name=\"Label\" size=\"60\" maxlength=\"60\" /></td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td><input type=\"submit\" class=\"button\" value=\"". $L_Create . "\" /><a class=\"button\" href=\"" . $P_Action . "\">" . $L_Cancel . "</a></td>\n" .
		 "       </tr>\n" .
		 "      </tbody>\n" .
		 "     </table>\n" .
		 "    </form>\n" .
		 "    <script>\n" .
		 "document.add_profil.Label.focus();\n" .
		 "    </script>\n" .
		 "\n" );
	} else {
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
		break;
	}

	break;


 case 'PRF_AX':
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	if ( $Authentication->is_administrator() ) {
		if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (Label)', $Return_Page, 1 ) );
			break;
		}

		try {
			$Profiles->set( '', $Label );
		} catch( PDOException $e ) {
			print( $PageHTML->infoBox( $L_ERR_CREA_Profile, $Return_Page, 1 ) );
			break;
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				print( $PageHTML->infoBox( $L_ERR_DUPL_Profile, $Return_Page, 1 ) );
			} else {
				print( $PageHTML->infoBox( $L_ERR_CREA_Profile, $Return_Page, 1 ) );
			}
			break;
		}

		print( $PageHTML->infoBox( $L_Profile_Created, $Return_Page, 2 ) );
	} else {
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
		break;
	}
	
	break;


 case 'PRF_M':
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	if ( ($prf_id = $Security->valueControl( $_GET[ 'prf_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	if ( array_key_exists( 'store', $_GET ) ) {
		$_SESSION[ 'p_action' ] = '?action=P&prf_id=' . $prf_id;
	}

	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );


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
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	if ( $Authentication->is_administrator() ) {
		if ( ($prf_id = $Security->valueControl( $_POST[ 'prf_id' ], 'NUMERIC' )) == -1
		 ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page,
			 1 ) );
			break;
		}

		if ( ! $Label = $Security->valueControl( $_POST[ 'Label' ] ) ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (Label)', $Return_Page,
			 1 ) );
			break;
		}

		try {
			$Profiles->set( $prf_id, $Label );
		} catch( PDOException $e ) {
			print( $PageHTML->infoBox( $L_ERR_MODI_Profile, $Return_Page, 1 ) );
			break;
		} catch( Exception $e ) {
			if ( $e->getCode() == 1062 ) {
				print( $PageHTML->infoBox( $L_ERR_DUPL_Profile, $Return_Page, 1 ) );
			} else {
				print( $PageHTML->infoBox( $L_ERR_MODI_Profile, $Return_Page, 1 ) );
			}
			break;
		}


		print( $PageHTML->infoBox( $L_Profile_Modified, $Return_Page, 2 ) );
	} else {
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}
	
	break;


 case 'PRF_D':
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	if ( ($prf_id = $Security->valueControl( $_GET[ 'prf_id' ], 'NUMERIC' )) == -1 ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );


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
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	
	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	if ( $Authentication->is_administrator() ) {
		if ( ($prf_id = $Security->valueControl( $_POST[ 'prf_id' ], 'NUMERIC' )) == -1
		 ) {
			print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1
			 ) );
			break;
		}

		try {
			$Profiles->delete( $prf_id );
		} catch( PDOException $e ) {
			print( $PageHTML->infoBox( $L_ERR_DELE_Profile, $Return_Page, 1
			 ) );
			break;
		}

		print( $PageHTML->infoBox( $L_Profile_Deleted, $Return_Page, 2 ) );
	} else {
		print( $PageHTML->infoBox( $L_No_Authorize, $Return_Page, 1 ) );
	}
	
	break;


 case 'PRF_G':
	include( 'Libraries/Class_IICA_Profiles_PDO.inc.php' );
	include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
	

	$Profiles = new IICA_Profiles( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	$Profile = $Profiles->get( $_GET[ 'prf_id' ] );

	$List_Groups_Associated = $Profiles->listGroups( $_GET[ 'prf_id' ] );
	

	$Groups = new IICA_Groups( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	$List_Groups = $Groups->listGroups();

	
	$Rights = new IICA_Referentials( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

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
		 "        <td>\n" .
		 "           <span style=\"border: 1px solid grey; padding: 3px;\" " .
		 "class=\"pair green bold\">" . stripslashes( $Profile->prf_label ) . "</span>\n" .
		 "        </td>\n" .
		 "       <tr>\n" .
		 "        <td colspan=\"2\">&nbsp;</td>\n" .
		 "       </tr>\n" 
		);
		
		$manageGroups = "         <a class=\"button\" href=\"https://" . $Server .
		 dirname( $Script ) . "/SM-secrets.php?rp=users-prf_g&prf_id=" .
		 $_GET[ 'prf_id' ] . "\">" . $L_Groups_Management . "</a>\n" ;
		
		print( "       <tr>\n" .
		 "        <td class=\"align-right\">" . $L_Groups . "</td>\n" .
		 "        <td>\n" .
		 $manageGroups .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td>\n" .
		 "         <table style=\"border: 1px solid grey;\">\n" .
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
			 "          <tr class=\"" . $BackGround . " surline\">\n" .
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
		
		print( "         </table>\n" .
		 "        </td>\n" .
		 "       </tr>\n" .
		 "       <tr>\n" .
		 "        <td>&nbsp;</td>\n" .
		 "        <td>\n" .
		 $manageGroups .
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
	$Return_Page = 'https://' . $Server . $Script . '?action=PRF_V';
 
	include( 'Libraries/Class_IICA_Secrets_PDO.inc.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_SM-secrets.php' );
	include( 'Libraries/Labels/' . $_SESSION[ 'Language' ] . '_labels_referentials.php' );
	

	$Groups = new IICA_Groups( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

	$Secrets = new IICA_Secrets( 
	 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );


	if ( ! $prf_id = $Security->valueControl( $_GET[ 'prf_id' ] ) ) {
		print( $PageHTML->infoBox( $L_Invalid_Value . ' (prf_id)', $Return_Page, 1 ) );
		break;
	}

	$Verbosity_Alert = $Parameters->get( 'verbosity_alert' );

	try {
		if ( $Verbosity_Alert == 2 ) {
			$alert_message = 'Groups->deleteProfiles( \'' . $prf_id . '\' )' ;
		} else {
			$alert_message = '[' . $prf_id . '] ' . $L_Profiles_Clean ;
		}
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message );

		$Groups->deleteProfiles( '', $prf_id );
		
		$Store = '';
		
		if ( $_POST != array() ) {
			foreach( $_POST as $Key => $Values ) {
				$Store_Key = explode( '_', $Key );
				$Store_Key = $Store_Key[ 1 ];

				foreach( $Values as $Value ) {
					if ( $Verbosity_Alert == 2 ) {
						$alert_message = 'Groups->addProfile( \'' . $Store_Key . '\', \'' .
						 $prf_id . '\', \'' . $Value . '\' )' ;
					} else {
						$alert_message = $L_Profiles_Associate . ' [' . $prf_id . ']' .
						 '[' . $Store_Key . ']' .
						 '[' . $Value . ']' ;
					}
		
					$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message );

					$Groups->addProfile( $Store_Key, $prf_id, $Value );
				}

			}
		}
	} catch( PDOException $e ) {
		$alert_message = $L_ERR_ASSO_Identity;
		
		$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message );

		print( $PageHTML->infoBox( $L_ERR_ASSO_Identity, $Return_Page, 1 ) );
		break;
	}

	$alert_message = $L_Association_Complited;
		
	$Secrets->updateHistory( '', $_SESSION[ 'idn_id' ], $alert_message );

	print( $PageHTML->infoBox( $L_Association_Complited, $Return_Page, 2 ) );
	break;
}


print(  "   </div> <!-- fin : zoneMilieuComplet -->\n" .
 $PageHTML->construireFooter( 1 ) .
 $PageHTML->piedPageHTML() );

?>
<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_Authentications_PDO.inc.php' );


class HTML extends IICA_Authentications {
/**
* Cette classe gère l'affichage des principales parties des écrans.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.2
* @date 2012-11-26
*/
public $Version; // Version de l'outil (précisé dans le constructeur)

public function __construct() {
/**
* Charge les variables d'environnements
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2013-02-19
*/
	if ( file_exists( DIR_LIBRARIES . '/Environnement.inc.php' ) ) {
		include( DIR_LIBRARIES . '/Environnement.inc.php' );
	}

	$this->Version = '0.6-3'; // Version de l'outil

	parent::__construct();
	
	return ;
}


public function enteteHTML( $Title = "", $Language_Zone = 0, $Fichiers_JavaScript = '', $innerJS = '' ) {
/**
* Standardisation de la construction des pages HTML et de l'affichage des hauts de page.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Title Titre à afficher dans la fenêtre des navigateurs
* @param[in] $Language_Zone Booléen permettant de gérer l'affichage de la zone de choix de langue
*
* @return Retourne une chaîne matérialisant le début d'une page HTML
*/
	$Date = date( 'd F Y' );
	$L_Subtitle = "";
	$Script = $_SERVER[ 'SCRIPT_NAME' ];
	
	if ( file_exists( DIR_LABELS . "/" . $_SESSION[ 'Language' ] .
	 "_labels_date.php" ) ) {
		include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_date.php" );
		
		$Date = date( 'd' ) . ' ' . $Month[ date( 'n' ) ] . ' ' . date( 'Y' );
	}
	
	if ( file_exists( DIR_LABELS . "/" . $_SESSION[ 'Language' ] .
	 "_labels_generic.php" ) ) {
		include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );
	}
	

	$Header = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" " .
	 " \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n\n" .
	 "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\" lang=\"fr\">\n" .
//	$Header = "<!DOCTYPE html>\n\n" .
//	 "<html lang=\"fr\">\n" .
	 " <head>\n" .
	 "  <meta name=\"Description\" content=\"Secret Management\" />\n" .
	 "  <meta http-equiv=\"Content-Type\" content=\"text/html; " .
	 "charset=utf-8\" />\n" . // iso-8859-15\" />\n" .
	 "  <meta name=\"Author\" content=\"Pierre-Luc MARY\" />\n" .
	 "  <link rel=\"icon\" type=\"image/png\" href=\"" . URL_PICTURES . "/Logo-SM-30x30.png\" />\n" .
	 "  <link rel=\"stylesheet\" href=\"bootstrap/css/bootstrap.css\" ".
	 "type=\"text/css\" />\n" .
	 "  <link rel=\"stylesheet\" href=\"" . URL_LIBRARIES . "/SecretManager.css\" ".
	 "type=\"text/css\" />\n" .
	 "  <link rel=\"stylesheet\" href=\"" . URL_LIBRARIES . "/SecretManager-icons.css\" " .
	 "type=\"text/css\" />\n" .
//	 "  <script  type=\"text/javascript\" src=\"" . URL_LIBRARIES . "/jquery-2.0.3.min.js\"></script>\n" ;
	 "  <script type=\"text/javascript\" src=\"" . URL_LIBRARIES . "/jquery-2.0.3.js\"></script>\n" ;

	if ( $Fichiers_JavaScript != '' ) {
        if ( is_array( $Fichiers_JavaScript ) ) {
            foreach( $Fichiers_JavaScript as $Fichier ) {
                $Header .= "  <script type=\"text/javascript\" src=\"" . URL_LIBRARIES . "/" . $Fichier . "\"></script>\n";
            }
        } else {
            $Header .= "  <script type=\"text/javascript\" src=\"" . URL_LIBRARIES . "/" . $Fichiers_JavaScript . "\"></script>\n";
        }
	}

	if ( $innerJS != '' ) {
		$Header .= "  <script type=\"text/javascript\">\n" .
			$innerJS .
			"  </script>\n";
	}

	$Header .= "  <title>" . $Title . "</title>\n" .
	 " </head>\n\n" .
	 " <body>\n\n" .
	 "  <!-- debut : enveloppe -->\n" .
	 "  <div id=\"enveloppe\">\n\n" .
	 "   <!-- debut : zoneEntete -->\n" .
	 "   <div id=\"zoneEntete\">\n" .
	 "    <h1 id=\"logo\"><span class=\"green\">Secret</span>" .
	 "<span class=\"blue1\">Manager</span> <span>v" . $this->Version . "</span></h1>\n" .
	 "    <h2 id=\"slogan\">" . $L_Subtitle . "</h2>\n" ;

	
	if ( isset( $_SESSION[ 'cvl_last_name' ] ) ) {
		$Header .= "    <h2 id=\"login\">" .
		 $_SESSION[ 'cvl_first_name' ] . " " . $_SESSION[ 'cvl_last_name' ] . "</h2>\n" ;
	}
	
	$Header .= "    <h2 id=\"date\">" . $Date . "</h2>\n" ;

	
	if ( $Language_Zone == 1 ) {
		$Header .= "    <!-- debut : zoneLangues -->\n" .
		 "    <div id=\"zoneLangues\">\n" .
		 "     <a href=\"" . $Script . "?Lang=en\">\n" .
		 "      <img src=\"" . URL_PICTURES . "/flag-eng.png\" alt=\"" . $L_Langue_en .
		 "\" title=\"" . $L_Langue_en . "\"  class=\"no-border\" />\n" .
		 "     </a>\n" .
		 "     <a href=\"" . $Script . "?Lang=fr\">\n" .
		 "      <img src=\"" . URL_PICTURES . "/flag-fra.png\" alt=\"" . $L_Langue_fr . 
		 "\" title=\"" . $L_Langue_fr . "\"  class=\"no-border\" />\n" .
		 "     </a>\n" .
		 "     <a href=\"" . $Script . "?Lang=de\">\n" .
		 "      <img src=\"" . URL_PICTURES . "/flag-deu.png\" alt=\"" . $L_Langue_de . 
		 "\" title=\"" . $L_Langue_de . "\"  class=\"no-border\" />\n" .
		 "     </a>\n" .
		 "    </div> <!-- fin : zoneLangues -->\n\n" ;
	} else {
		if ( array_key_exists( 'idn_login', $_SESSION ) ) {
			$Header .= "    <!-- debut : zoneUser -->\n" .
			 "    <div id=\"zoneUser\">\n" .
			 "     <span class=\"bg-green\">&nbsp;" . $_SESSION[ 'idn_login' ] .
			 "&nbsp;</span>\n" .
			 "    </div> <!-- fin : zoneUser -->\n\n" ;
		}
	}
	
	return $Header . "   </div> <!-- fin : zoneEntete -->\n\n" .
	 "   <!-- debut : contenu-enveloppe -->\n" .
	 "   <div id=\"contenu-enveloppe\">\n" ;
}


public function mini_HTMLHeader( $Title = "" ) {
/**
* Cet écran est une version minimaliste d'affichage. Il est dédié à l'affichage de petite
* fenêtre comme celle d'affichage des authentifiants.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Title Titre à afficher dans la fenêtre des navigateurs
*
* @return Retourne une chaîne matérialisant le début d'une page HTML
*/
	return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" " .
	 "\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n\n" .
	 "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\" lang=\"fr\">\n" .
	 " <head>\n" .
	 "  <meta name=\"Description\" content=\"Secret Management\" />\n" .
	 "  <meta http-equiv=\"Content-Type\" content=\"text/html; " .
	 "charset=utf8\" />\n" .
	 "  <meta name=\"Author\" content=\"Pierre-Luc MARY\" />\n" .
	 "  <link rel=\"stylesheet\" href=\"" . URL_LIBRARIES . "/SecretManager.css\" " .
	 "type=\"text/css\" />\n\n" .
	 "  <link rel=\"stylesheet\" href=\"" . URL_LIBRARIES . "/SecretManager-icons.css\" " .
	 "type=\"text/css\" />\n\n" .
	 "  <title>" . $Title . "</title>\n" .
	 " </head>\n\n" .
	 " <body>\n\n" .
	 "  <!-- debut : enveloppe -->\n" .
	 "  <div id=\"enveloppe\">\n\n" .
	 "   <!-- debut : contenu-enveloppe -->\n" .
	 "   <div id=\"contenu-enveloppe\">\n" ;
}


public function construireFooter( $Buttons = 0, $Previous = '' ) {
/**
* Standardisation de l'affichage des bas de page.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Buttons Booléen gérant l'affichage des boutons en bas de page
* @param[in] $Previous Permet de spécifier un code de page retour pour la suite
*
* @return Retourne une chaîne matérialisant le bas des pages HTML
*/
	include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );

	$Text = "   </div> <!-- fin : contenu-enveloppe -->\n" .
	 "    <!-- debut : zonePiedPage -->\n" .
	 "    <div id=\"zonePiedPage\">\n" .
	 "     <div class=\"zonePiedPage-left\">\n" .
	 "      <p class=\"align-left\">" .
	 "<img src=\"" . URL_PICTURES . "/copy_left-15x15.jpg\" alt=\"Copyleft\" class=\"no-border\" />" .
	 " Copyleft " . date( "Y" ) . " <strong>" .
	 "<a class=\"white\" href=\"http://www.orasys.fr\" target=\"_blank\">Orasys</a>" .
	 "</strong></p>\n" .
	 "     </div>\n\n" ;

	if ( $Buttons == 1 ) {
		if ( $Previous != '' ) {
			$Previous = '&rp=' . $Previous;
		}
		
		$Text .= "     <div class=\"zonePiedPage-right\">\n" .
		 "      <p class=\"align-right\">" .
		 "<a class=\"button\" href=\"" . URL_BASE . "/SM-login.php?action=CMDP" . $Previous . "\">" . 
		 $L_Changed_Password . "</a>" .
		 "<a class=\"button\" href=\"" . URL_BASE . "/SM-login.php?action=DCNX\">" .
		 $L_Deconnexion . "</a>\n" .
		 "      </p>\n" .
		 "     </div>\n" ;
	}
	
	return $Text . "    </div> <!-- Fin : zonePiedPage -->\n" ;
}


public function piedPageHTML() {
/**
* Standardisation des fin de page HTML.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @return Retourne une chaîne matérialisant les fins de page HTML
*/
	return 
	 "  </div> <!-- fin : enveloppe -->\n" .
	 " </body>\n" .
	 "</html>\n" ;
}


public function afficherActions( $Administrator ) {
/**
* Gère l'affichage des icônes actions.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Administrator Booléen permettant de gérer les icônes des administrateurs
*
* @return Retourne une chaîne matérialisant l'affichage des options à l'écran
*/
	if ( file_exists( DIR_LABELS . "/" . $_SESSION[ 'Language' ] .
	 "_labels_generic.php" ) ) {
		include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );
	}
	
	$Actions = "    <span id=\"menu-icon-actions\" class=\"icon28\" title=\"Actions\" onMouseOver=\"javascript:document.getElementById('actions').style.visibility='visible';\"></span>\n" .
	 "    <!-- debut : actions -->\n" .
	 "    <div id=\"actions\" class=\"hidden\" onMouseOut=\"javascript:document.getElementById('actions').style.visibility='hidden';\" onMouseOver=\"javascript:document.getElementById('actions').style.visibility='visible';\">\n" .
	 "     <span id=\"menu-icon-home\" class=\"icon28\" title=\"" . $L_Dashboard . "\" onClick=\"javascript:document.location='SM-home.php'\"></span>\n" ;

	if ( $Administrator ) {
	 	$Actions .= "     <span id=\"menu-icon-access\" class=\"icon28\" title=\"" .
	 	 $L_Secrets_Management . "\"  onClick=\"javascript:document.location='SM-secrets.php'\"></span>\n" .
		"     <span id=\"menu-icon-users\" class=\"icon28\" title=\"" . $L_Users_Management . "\" onClick=\"javascript:document.location='SM-users.php'\"></span>\n" .
		"     <span id=\"menu-icon-options\" class=\"icon28\" title=\"" . $L_Preferences_Management . "\" onClick=\"javascript:document.location='SM-preferences.php'\"></span>\n";
	}

	$Actions .= "    </div> <!-- fin : actions -->\n";
	
	return $Actions;
}


public function drawBox( $Title = "", $Text = "" ) {
/**
* Affiche une boîte d'information.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Title Titre du tableau
* @param[in] $Text Corps du tableau
*
* @return Retourne une chaîne matérialisant l'affichage des options à l'écran
*/

	return "     <table cellspacing=\"0\" style=\"float: left\">\n" .
	 "      <thead>\n" .
	 "       <tr>\n" .
	 "        <th>" . $Title . "</th>\n" .
	 "       </tr>\n" .
	 "      </thead>\n" .
	 "      <tbody>\n" .
	 "       <tr class=\"pair\">\n" .
	 "        <td>\n" .
	 $Text .
	 "        </td>\n" .
	 "       </tr>\n" .
	 "      </tbody>\n" .
	 "      <tfoot>\n" .
	 "       <tr>\n" .
	 "        <th>&nbsp;</th>\n" .
	 "       </tr>\n" .
	 "      </tfoot>\n" .
	 "     </table>\n" ;
}

public function infoBox( $Message, $Script, $Alert = 1 ) {
/**
* Affiche une boîte d'information.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-18
*
* @param[in] $Message Message à afficher
* @param[in] $Script Script à exécuter sur le clique du bouton retour
* @param[in] $Alert Type de message avertissement ou de succès (influence la couleur et l'icône du message)
*
* @return Retourne une chaîne HTML matérialisant l'affichage de cette boîte
*/
	include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );
	
	switch( $Alert ) {
	 case 1:
		$Type_Message = 'id="alert"';
		$Icon_Name = 'minus';
		break;

	 case 2:
		$Type_Message = 'id="success"';
		$Icon_Name = 's_success';
		break;
		
	 case 3:
		$Type_Message = 'class="alert alert-block align-center"';
		$Icon_Name = 's_warn';
		break;
	}
	
	return "     <div " . $Type_Message . "\">\n" .
	 "<img class=\"no-border\" src=\"" . URL_PICTURES . "/" . $Icon_Name . ".png\" alt=\"" .
	 $Type_Message . "\" />\n" . $Message . "<br/><br/>" .
	 "<a id=\"b_return\" href=\"" . $Script . "\" class=\"button\">" .
	 $L_Return . "</a>" .
	 "      <script>\n" .
	 "document.getElementById( 'b_return' ).focus();\n" .
	 "      </script>\n";
	 "     </div>\n";
}


public function returnPage( $Title, $Message, $Script, $Alert = 1 ) {
/**
* Affiche d'une page avec un titre et un message et permettant un retour sur une page
* spécifique.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Title Titre du navigateur et du haut de page
* @param[in] $Message Message à afficher
* @param[in] $Script Script à exécuter sur le clique du bouton retour
* @param[in] $Alert Type de message avertissement ou de succès (influence la couleur et l'icône du message)
*
* @return Retourne une chaîne matérialisant l'affichage de cet écran d'information
*/
	include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );
	
	if ( $Alert == 1 ) {
		$Type_Message = 'alert';
		$Icon_Name = 'minus';
	} else {
		$Type_Message = 'success';
		$Icon_Name = 's_success';
	}
	
	return $this->enteteHTML( $Title, 0 ) .
	 "    <div id=\"icon-users\" class=\"icon36\" style=\"float: left; margin: 3px 9px 3px 3px;\"></div>\n" .
	 "    <h1 style=\"padding-top: 12px;\">" . $Title . "</h1>\n" .
//	 "    <div id=\"zoneGauche\" >&nbsp;</div>\n" .
//	 "    <!-- debut : zoneMilieuComplet -->\n" .
	 "    <div id=\"zoneMilieuComplet\">\n" .
	 "     <div id=\"" . $Type_Message . "\"><img class=\"no-border\" src=\"Pictures/" .
	 $Icon_Name . ".png\" alt=\"KO\" />\n" .
	 $Message .
	 "<br/><br/><a id=\"b_return\" href=\"" . $Script . "\" class=\"button\">" . 
	 $L_Return . "</a>" .
	 "     </div>\n" .
	 "    </div> <!-- fin : zoneMilieuComplet -->\n" .
	 "    <script>\n" .
	 "document.getElementById( 'b_return' ).focus();" .
	 "    </script>\n" .
	 $this->construireFooter() .
	 $this->piedPageHTML();
}


public function page( $Title, $Message, $Language_Zone = 1, $Buttons = 1 ) {
/**
* Affiche d'une page avec un titre et un message et permettant un retour sur une page
* spécifique.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-12
*
* @param[in] $Title Titre du navigateur et du haut de page
* @param[in] $Message Message à afficher
* @param[in] $Script Script à exécuter sur le clique du bouton retour
* @param[in] $Alert Type de message avertissement ou de succès (influence la couleur et l'icône du message)
*
* @return Retourne une chaîne matérialisant l'affichage de cet écran d'information
*/
	include( DIR_LABELS . "/" . $_SESSION[ 'Language' ] . "_labels_generic.php" );

	return $this->enteteHTML( $Title, $Language_Zone ) .
	 "   <!-- debut : zoneTitre -->\n" .
	 "   <div id=\"zoneTitre\">\n" .
	 "    <div id=\"" . $Icon . "\" class=\"icon36\"></div>\n" .
	 "    <span id=\"titre\">" . $Title . "</span>\n" .
	 $this->afficherActions( $_SESSION['idn_super_admin'] ) .
	 "   </div> <!-- fin : zoneTitre -->\n" .
	 "\n" .
//	 "   <!-- debut : zoneGauche -->\n" .
//	 "   <div id=\"zoneGauche\" >&nbsp;</div> <!-- fin : zoneGauche -->\n" .
	 "\n" .
	 "   <!-- debut : zoneMilieuComplet -->\n" .
	 "   <div id=\"zoneMilieuComplet\">\n" .
	 "\n" .
	 "    <!-- debut : dashboard -->\n" .
	 "    <div id=\"dashboard\">\n" .
	 $Message .
	 "    </div> <!-- fin : dashboard -->\n" .
	 "   </div> <!-- debut : zoneMilieuComplet -->\n" .
	 $this->construireFooter( $Buttons ) .
	 $this->piedPageHTML();
}

} // Fin class HTML.

?>
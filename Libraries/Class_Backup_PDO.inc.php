<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

class Backup extends IICA_Parameters {
/**
* Cette classe gère les sauvegardes du SecretManager.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
*/
	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-11-25
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		parent::__construct();
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Sauvegardes
	*/
	
	public function backup_secrets() {
	/**
	* Sauvegarde les Secrets (scr_secrets, sgr_secrets_groups, stp_secret_types, env_environments).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-11-25
	*
	* @return Renvoi la date de sauvegarde c'est bien passé, sinon "FALSE".
	*/
		$Save_Date = date( 'Y-m-d_H.i.s' );
		$Save_Date_1 = str_replace( '_', ' ', $Save_Date );
		$Save_Date_1 = str_replace( '.', ':', $Save_Date_1 );

		$Save_Filename = DIR_BACKUP . '/secrets_' . $Save_Date . '.bck';
		
		// Création du fichier cible.
		if ( ! $Save_File = @fopen( $Save_Filename, 'w' ) ) {
		    throw new Exception( '% L_ERROR_OPEN, create file error "' . $Save_Filename . '"', -10 );
		}
		
        // Ecriture de l'entête.
		fwrite( $Save_File, '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
		    '<secrets date="' . $Save_Date_1 . '">' . "\n" ); 
		    

        // ============================================
        // Traitement de la table "Goupes de Secrets".		    
        if ( ! $Result = $this->prepare( 'SELECT sgr_id, sgr_label, sgr_alert ' .
            'FROM sgr_secrets_groups' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		fwrite( $Save_File, ' <table id="sgr" name="sgr_secrets_groups">' . "\n" );
		        
        $Row_Count = 0;
        
		while ( $Occurrence = $Result->fetchObject() ) {
		    $Row_Count += 1;
		    
		    $Out_Occurrence = '  <row id="sgr-' . $Row_Count . '">' . "\n" .
		        '   <column name="sgr_id">' . $Occurrence->sgr_id . '</column>' . "\n" .
		        '   <column name="sgr_label">' . $Occurrence->sgr_label . '</column>' . "\n" .
		        '   <column name="sgr_alert">' . $Occurrence->sgr_alert . '</column>' . "\n" .
                '  </row>' . "\n" ;

            fwrite( $Save_File, $Out_Occurrence );
		}

        fwrite( $Save_File, ' </table>' . "\n" );


        // ============================================
        // Traitement de la table "Types de Secrets".		    
        if ( ! $Result = $this->prepare( 'SELECT stp_id, stp_name ' .
            'FROM stp_secret_types' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		fwrite( $Save_File, "\n" . ' <table id="stp" name="stp_secret_types">' . "\n" );
		        
        $Row_Count = 0;
        
		while ( $Occurrence = $Result->fetchObject() ) {
		    $Row_Count += 1;
		    
		    $Out_Occurrence = '  <row id="stp-' . $Row_Count . '">' . "\n" .
		        '   <column name="stp_id">' . $Occurrence->stp_id . '</column>' . "\n" .
		        '   <column name="stp_name">' . $Occurrence->stp_name . '</column>' . "\n" .
                '  </row>' . "\n" ;

            fwrite( $Save_File, $Out_Occurrence );
		}

        fwrite( $Save_File, ' </table>' . "\n" );


        // ============================================
        // Traitement de la table "Environnements d'un Secret".		    
        if ( ! $Result = $this->prepare( 'SELECT env_id, env_name ' .
            'FROM env_environments' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		fwrite( $Save_File, "\n" . ' <table id="env" name="env_environments">' . "\n" );
		        
        $Row_Count = 0;
        
		while ( $Occurrence = $Result->fetchObject() ) {
		    $Row_Count += 1;
		    
		    $Out_Occurrence = '  <row id="env-' . $Row_Count . '">' . "\n" .
		        '   <column name="env_id">' . $Occurrence->env_id . '</column>' . "\n" .
		        '   <column name="env_name">' . $Occurrence->env_name . '</column>' . "\n" .
                '  </row>' . "\n" ;

            fwrite( $Save_File, $Out_Occurrence );
		}

        fwrite( $Save_File, ' </table>' . "\n" );


        // ============================================
        // Traitement de la table des "Secrets".		    
        if ( ! $Result = $this->prepare( 'SELECT ' . 
            'scr_id, sgr_id, stp_id, env_id, ' .
            'scr_host, scr_user, scr_password, scr_application, scr_comment, scr_alert, ' .
            'scr_creation_date, scr_modification_date, scr_expiration_date ' .
            'FROM scr_secrets' ) ) {
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
        }

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		fwrite( $Save_File, "\n" . ' <table id="scr" name="scr_secrets">' . "\n" .
		    '  <key id="mother_key">' . file_get_contents( DIR_LIBRARIES . '/secret.dat' ) . '</key>' . "\n" );
		        
        $Row_Count = 0;
        
		while ( $Occurrence = $Result->fetchObject() ) {
		    $Row_Count += 1;
		    
		    $Out_Occurrence = '  <row id="scr-' . $Row_Count . '">' . "\n" .
		        '   <column name="scr_id">' . $Occurrence->scr_id . '</column>' . "\n" .
		        '   <column name="stp_id">' . $Occurrence->stp_id . '</column>' . "\n" .
		        '   <column name="env_id">' . $Occurrence->env_id . '</column>' . "\n" .
		        '   <column name="scr_host">' . $Occurrence->scr_host . '</column>' . "\n" .
		        '   <column name="scr_user">' . $Occurrence->scr_user . '</column>' . "\n" .
		        '   <column name="scr_password">' . $Occurrence->scr_password . '</column>' . "\n" .
		        '   <column name="scr_application">' . $Occurrence->scr_application . '</column>' . "\n" .
		        '   <column name="scr_comment">' . $Occurrence->scr_comment . '</column>' . "\n" .
		        '   <column name="scr_alert">' . $Occurrence->scr_alert . '</column>' . "\n" .
		        '   <column name="scr_creation_date">' . $Occurrence->scr_creation_date . '</column>' . "\n" .
		        '   <column name="scr_modification_date">' . $Occurrence->scr_modification_date . '</column>' . "\n" .
		        '   <column name="scr_expiration_date">' . $Occurrence->scr_expiration_date . '</column>' . "\n" .
                '  </row>' . "\n" ;

            fwrite( $Save_File, $Out_Occurrence );
		}

        fwrite( $Save_File, ' </table>' . "\n" );


        // =======================================
        // Pied de page du fichier de sauvegarde.
        fwrite( $Save_File, '</secrets>' . "\n" );

        fclose( $Save_File );
		
		return $Save_Date_1;
	}

} // Fin class Backup

?>
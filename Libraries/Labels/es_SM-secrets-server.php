<?php
/**
* Libellé spécifique à la gestion du SecretServer.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/

	$L_ERR_SERVER_NOT_LOADED = 'No secretServer cargado';
	$L_ERR_SERVER_NOT_STARTED = 'No iniciado secretServer';
	$L_ERR_MOTHER_KEY_CORRUPTED = 'Clave Madre dañado o no válido';
	$L_ERR_NO_CMD_SEND = 'No hay un comando enviado';
	$L_ERR_NO_SESSION = 'No ID de sesión';
	$L_ERR_NO_VALUE = 'Se desconoce el valor';
	$L_ERR_INVALID_SESSION = 'Sesión no válido o no encontrado sesión';
	$L_ERR_SESSION_EXPIRED = 'Sesión expirada';
	$L_ERR_USER_NOT_CONNECTED = 'Usuario no está conectado';
	$L_ERR_USER_NOT_ADMIN = 'Este usuario no es un administrador';
	$L_ERR_OPERATOR_KEY_EMPTY = 'Error, clave Operador está vacía';
	$L_ERR_MOTHER_KEY_EMPTY = 'Error, clave Madre está vacía';
	$L_ERR_INVALID_OPERATOR_KEY = 'Clave Madre del fichero que contiene o Clave Opérator no válida';
	$L_ERR_MOTHER_KEY_NOT_LOADED = 'Clave Madre Descargada';
	$L_ERR_TRANSPORT_FILE_OPEN = 'Error al abrir el archivo que contiene la clave de Transporte';
	$L_ERR_TRANSPORT_FILE_READ = 'Error al leer el archivo que contiene la clave de Transporte';
	$L_ERR_TRANSPORT_FILE_CREATION = 'Error al crear el archivo que contiene la clave de Transporte';
	$L_ERR_MISSING_KEY = 'Una clave omitido';
	$L_ERR_SECRET_FILE_OPEN = 'Error al abrir el archivo que contiene la clave Madre';
	$L_ERR_SECRET_FILE_READ = 'Error al leer el archivo que contiene la clave Madre';
	$L_ERR_SECRET_FILE_CREATION = 'Error al crear el archivo que contiene la Clave Madre';
	$L_ERR_TRANSCRYPT = 'Error al base de transcryption';
	$L_ERR_INVALID_OPERATOR_KEY_BACKUP = 'El clave Operador no se abre Restaurar el archivo de clave Madre';
	$L_ERR_MASTER_INTEGRITY_ALERT = 'Alerta Integridad en el Archivo Principal (SecretManager)';
	$L_ERR_SECRETSERVER_INTEGRITY_ALERT = 'Alerta Integridad en el archivo Secondairy (SecretServer)';
	
	$L_MOTHER_KEY_LOADED = 'Clave Madre cargado';
	$L_MOTHER_KEY_AUTOMATICALLY_CREATED = 'Clave Madre crea automáticamente';
	$L_MOTHER_KEY_MANUALLY_CREATED = 'Clave Madre creado manualmente';
	$L_MOTHER_KEY_TRANSCRYPTED = 'Clave Madre transcrypted';

	$L_Operator = 'Operador';
	$L_Load_Mother_Key = 'Cargue la clave Madre';
	$L_Creation_Mother_Key = 'Creación de clave Madre';
	$L_Operator_Key = 'Clave Operador';
	$L_Mother_Key = 'Clave Madre';
	$L_Insert_Operator_Key = 'Introduzca el valor de la clave Operador';
	$L_Insert_Mother_Key = 'Introduzca el valor de la clave Madre';
	$L_Use_SecretServer = 'Utilice SecretServer';
	$L_Create_New_Keys = 'Crear nuevas claves';
	
	$L_New_Keys_Created = 'Nuevos claves de cifrado creadas';
	$L_Confidentials = 'Información Confidencial';
	
	$L_Success_Page = "<h1>" . $L_Confidentials . "</h1>\n" .
	 "<p>Importante 1 : <span class=\"normal\">Esta página no se regenerará, asegúrese de guardarlo en un lugar seguro.</span></p>" .
	 "<p>Importante 2 : <span class=\"normal\">el archivo anterior 'secret.dat' ha cambiado de nombre.</span></p>";

	$L_Shutdown_SecretServer = 'SecretServer apagado';

?>
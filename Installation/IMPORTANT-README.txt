Date : 2014-06-03
Author : Pierre-Luc MARY

Beta Software Code : https://github.com/plmary/SecretManager
Contact me : mailto:pl.mary(at)orasys.fr
Bug track ticket : https://sourceforge.net/p/secretmanager/tickets/?source=navbar


[ FR ]
Quelles nouvelles :
Cette version implémente la gestion des secrets Personnels. Secrets que l'on ne partage pas (d'où leur nom).
Plusieurs petites modifications :
- Corrections sur la notion de doublon lors des Créations et des Modifications de Secrets ;
- Possibilité d'utiliser la touche Entrée lors des Modifications de Secrets.

Première installation :
Déziper "SecretManager_v0.8-6.zip" dans le "DocumentRoot" de votre Serveur Apache.
Ensuite, lire le "Guide d'installation" dans le répertoire "Documentations" dans l'arborescence de SecretManager.

Mise à jour de v0.8-5 à v0.8-6 :
Déziper "upd_SecretManager_v0.8-6.zip" dans le précédent répertoire d'installation de votre "SecretManager".
Attention : il faut mettre à jour la base de données "Secret_Manager" par l'exécution du script suivant :
- upd-1-v0.8-6-SecretManager.sql


[ EN ]
What news:
This version implements the management of Personal secrets. This Secrets that are not shared (hence their name).
Several small changes:
- Corrections on Secrets duplication during Creating and Changing;
- Possibility to use the Enter key when modifications Secrets.

First installation:
Unzip "SecretManager_v0.8-6.zip" in the "DocumentRoot" your Apache Server.
Then read the "Installation Guide" in the "Documents" folder in the tree SecretManager.

Update v0.8-5 to v0.8-6:
Unzip "upd_SecretManager_v0.8-6.zip" in your previous installation "SecretManager" directory.
Warning: you must update the "Secret_Manager" database by executing the following script:
- Upd-1-v0.8-6-SecretManager.sql


* ==========================================================
* Important

"hosts" file must contain the following line :
127.0.0.1	secretmanager.localhost

"httpd-vhosts.conf" file must contain the following lines (carefull, it's an exemple. Adjust the locations 'D:/xampp/htdocs' as your context) :
<VirtualHost secretmanager.localhost:443>
    ServerName secretmanager.localhost
    ServerAlias secretmanager
    DocumentRoot D:/xampp/htdocs/SecretManager
    SSLEngine on
    SSLCertificateFile D:\xampp\apache\conf\ssl.crt\server.crt
    SSLCertificateKeyFile D:\xampp\apache\conf\ssl.key\server.key
    ServerAdmin orasys@orasys.fr
    <Directory D:/xampp/htdocs/SecretManager>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
    CustomLog logs/ssl_request_log "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b"
</VirtualHost>

Start SecretServer in background :
Windows users, I've initiate "SM-secrets-server.vbs" and "SM-secrets-server.bat" (ajust them)
Linux users, you can start SecretServer whith a simple "sudo -b /Applications/xampp/htdocs/SecretManager/SM-secrets-server.php"

If it's your SecretManager first start, in "Preferences management" and in "SecretServer" tab, you can load the mother key and using the default operator key "CleO".
After, you must change "operator key" and "mother key" on your product computer.

Have fun !!!
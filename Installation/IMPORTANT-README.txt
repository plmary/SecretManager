Date : 2013-xx-xx
Author : Pierre-Luc MARY

Software Code : https://github.com/plmary/SecretManager

[ FR ]
Quelles nouvelles :
Cette version continue son implémentation Ajax (voir l'écran de gestion des utilisateurs).
Pour répondre à un besoin, j'ai implémenté une date d'expiration sur les secrets. Effectivement, cela peut servir.

Première installation :
Déziper "SecretManager_v0.7-0.zip" dans le "DocumentRoot" de votre Serveur Apache.
Ensuite, lire le "Guide d'installation" dans le répertoire "Documentations" dans l'arborescence de SecretManager (par encore fini, j'y travaille).

Mise à jour de v0.6-3 à v0.7-0 :
Déziper "upd_SecretManager_v0.7-0.zip" dans le précédent répertoire d'installation de votre "SecretManager".
Attention : il faut mettre à jour la base de données par l'exécution du script : "upgrade_scr_secrets.sql".


[ EN ]
What's news :
This version fixe many little problems in SecretManager and SecretServer.
A new design it's proposal (with jQuery and Ajax). Say me if it's plaisant.

First Installation :
Unzip "SecretManager_v0.7-0.zip" in your Apache Server "DocumentRoot".
Read the "intallation guide" in "Documentation" directory

Upgrade v0.6-3 to v0.7-0:
Unzip "SecretManager_upd_v0.7-0.zip" in your "SecretManager" directory (precedent install).
Warning: you must execute an update on your database. Use the script "upgrade_scr_secrets.sql"


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
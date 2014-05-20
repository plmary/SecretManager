Date : 2014-05-20
Author : Pierre-Luc MARY

Beta Software Code : https://github.com/plmary/SecretManager
Contact me : mailto:pl.mary(at)orasys.fr
Bug track ticket : https://sourceforge.net/p/secretmanager/tickets/?source=navbar


[ FR ]
Quelles nouvelles :
Cette version implémente une nouvelle gestion de l'historique :
- Toutes les actions sur tous les objets sont désormais historisées ;
- Le paramétrage des alertes est désormais facilité (notamment les remontés par courriel) ;
- L'écran de suivi de l'historique a été modifié également.

Première installation :
Déziper "SecretManager_v0.8-5.zip" dans le "DocumentRoot" de votre Serveur Apache.
Ensuite, lire le "Guide d'installation" dans le répertoire "Documentations" dans l'arborescence de SecretManager.

Mise à jour de v0.8-4 à v0.8-5 :
Déziper "upd_SecretManager_v0.8-5.zip" dans le précédent répertoire d'installation de votre "SecretManager".
Attention : il faut mettre à jour la base de données par l'exécution des scripts suivants :
- upd-1-v0.8-5-SecretManager.sql
- upd-2-v0.8-5-SecretManager.sql


[ EN ]
What's news :
This version implements a new history management:
- All actions on all objects are now historized;
- The setting alerts is now easier (especially reassembled by email);
- Screen history tracking has also been modified.

First installation:
Unzip "SecretManager_v0.8-5.zip" in the "DocumentRoot" your Apache Server.
Read the "Installation Guide" in the "Documentations" folder in the tree SecretManager.

Update v0.8 v0.8-4-5:
Unzip "upd_SecretManager_v0.8-5.zip" in your previous installation "SecretManager" directory.
Warning: you must update the database by executing the following scripts:
- upd-1-v0.8-5-SecretManager.sql
- upd-2-v0.8-5-SecretManager.sql


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
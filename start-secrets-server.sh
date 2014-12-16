#!/bin/sh
env > Temp/secret_server.log
export PATH="/Library/Internet Plug-Ins/JavaAppletPlugin.plugin/Contents/Home/bin:/Library/Frameworks/Python.framework/Versions/2.7/bin:/opt/local/bin:/opt/local/sbin:/Applications/XAMPP/xamppfiles/bin:/Library/Internet Plug-Ins/JavaAppletPlugin.plugin/Contents/Home/bin:/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/usr/local/git/bin"
cd /Applications/XAMPP/xamppfiles/htdocs/SecretManager
nohup php SM-secrets-server.php >> Temp/secret_server.log 2>&1 &
exit 0
$(document).ready( function() {
    $(document).keyup(function(e){
        if(e.which == 27){ //|| e.which == 13){
            hideConfirmMessage();
            hideInfoMessage();
        }
    });

    $("#iSearchSecret").on('click', function(){
        var recherche = $(this).val();
        
        $('tbody#listeSecrets').html("");

        $.ajax({
            url: '../SM-home.php?action=R',
            type: 'POST',
            data: $.param({'Search_Secrets': recherche}),
            success: function(reponse){
                if(reponse){
                    $('tbody#listeSecrets').html(reponse);

                    var total = $('tbody#listeSecrets tr').attr('data-total');

                    $('#total').text( total );
                }
            },
            error: function(reponse) {
                alert('Erreur sur serveur : ' + reponse['responseText']);
            }
        }); 
    });
    
    
    // Sauvegarde le cas d'utilisation du SecretServer.
    $("#iSaveUseServer").click(function(){
        var UseSecretServer = $('#Use_SecretServer').val();

        $.ajax({
            url: '../SM-preferences.php?action=SUX',
            type: 'POST',
            data: $.param({'UseSecretServer': UseSecretServer}),
            dataType: 'json',
            success: function(reponse){
                if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                } else {
                    alert('Erreur sur serveur : ' + reponse);
                }
            },
            error: function(reponse) {
                alert('Erreur sur serveur : ' + reponse['responseText']);
            }
        }); 
    });


    // Sauve les propriétés des clés.    
    $("#iSaveKeysProperties").click(function(){
        var Operator_Key_Size = $('#Operator_Key_Size').val();
        var Operator_Key_Complexity = $('#Operator_Key_Complexity').val();

        var Mother_Key_Size = $('#Mother_Key_Size').val();
        var Mother_Key_Complexity = $('#Mother_Key_Complexity').val();

        $.ajax({
            url: '../SM-preferences.php?action=SKX',
            type: 'POST',
            data: $.param({
                'Operator_Key_Size': Operator_Key_Size,
                'Operator_Key_Complexity' : Operator_Key_Complexity,
                'Mother_Key_Size' : Mother_Key_Size,
                'Mother_Key_Complexity' : Mother_Key_Complexity
                }),
            dataType: 'json',
            success: function(reponse){
                if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                } else {
                    alert('Erreur sur serveur : ' + reponse);
                }
            },
            error: function(reponse) {
                document.write( reponse['responseText'] );
            }
        }); 
    });    
});


function hideConfirmMessage() {
    $('#confirm_message').remove();
}


function confirmCreateMotherKey() {
    var Operator_Key = $('#iNew_Operator_Key_2').val();
    var Mother_Key = $('#iNew_Mother_Key').val();
    
    if ( Operator_Key == '' || Mother_Key == '' ) {
        var Cancel_Operation = $('#iCreateMotherKey').attr('data-cancel-operation');
        
        showInfoMessage( 'error', Cancel_Operation ); // SecretManager.js
        return;
    }
        
    var Warning = $("#iSaveNewOeratorKey_1").attr('data-warning');
    var Confirm = $("#iSaveNewOeratorKey_1").attr('data-confirm');
    var Cancel = $("#iSaveNewOeratorKey_1").attr('data-cancel');
    
    var Text_1 = $("#iCreateMotherKey").attr('data-text');
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">'+Warning+'</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     '<p>' + Text_1 + '</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="cancel_confirm_message" href="javascript:hideConfirmMessage();">' +
     Cancel+'</a>&nbsp;<a class="button" href="javascript:createMotherKey();">'+Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#cancel_confirm_message').focus();
}


function createMotherKey(){
    var Operator_Key = $('#iNew_Operator_Key_2').val();
    var Mother_Key = $('#iNew_Mother_Key').val();
    
    if ( Operator_Key == '' || Mother_Key == '' ) {
        var Cancel_Operation = $('#iCreateMotherKey').attr('data-cancel-operation');
        
        showInfoMessage( 'error', Cancel_Operation ); // SecretManager.js
        return;
    }

    $.ajax({
        url: '../SM-admin.php?action=CRMKX',
        type: 'POST',
        data: $.param({
            'Operator_Key': Operator_Key,
            'Mother_Key' : Mother_Key
            }),
        dataType: 'json',
        success: function(reponse){
            if ( reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            } else if ( reponse['Status'] == 'success' ) {
                hideConfirmMessage();

                $('#iNew_Operator_Key_2').val('');
                $('#iNew_Mother_Key').val('');
                
                w = open('','popup','width=700,height=400,toolbar=no,scrollbars=no,resizable=yes');
                w.document.write( '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '+
                    '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n' +
                    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">\n' +
                    ' <head>\n' +
                    '  <meta name="Description" content="Secret Management" />\n' +
                    '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n' +
                    '  <meta name="Author" content="Pierre-Luc MARY" />\n' +
                    '  <link rel="icon" type="image/png" href="https://secretmanager.localhost/Pictures/Logo-SM-30x30.png" />\n' +
                    '  <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" />\n' +
                    '  <link rel="stylesheet" href="https://secretmanager.localhost/Libraries/SecretManager.css" type="text/css" />\n' +
                    '  <link rel="stylesheet" href="https://secretmanager.localhost/Libraries/SecretManager-icons.css" type="text/css" />\n' +
                    '<style>\n' +
                    ' body {\n' +
                    '  background-color: white;\n' +
                    '  padding: 6px;\n' +
                    '  margin: 12px;\n' +
                    ' }\n' +
                    ' button.btn:focus {\n' +
                    '  outline-style: none;\n' +
                    ' }\n' +
                    '</style>\n' +
                    '  <title>Secret</title>\n' +
                    ' </head>\n' +
                    ' <body bgcolor="white">\n' +
                    reponse['Message'] +
                    '<p class="align-center tbrl_margin_12">' +
                    '<button id="iPrint" class="btn tbrl_margin_3" onClick="javascript:window.print();">' +
                    reponse['L_Print'] + '</button>' +
                    '<button class="btn tbrl_margin_3" onClick="javascript:window.close();">' +
                    reponse['L_Close'] + '</button>' +
                    '</p>\n' +
                    '<script>document.getElementById("iPrint").focus();</script>\n' +
                    ' </body>\n' +
                    '</html>\n' );
                w.document.close();
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write( reponse['responseText'] );
        }
    }); 
}


function confirmChangeMotherKey(){
    var Operator_Key = $('#iNew_Operator_Key_2').val();
    var Mother_Key = $('#iNew_Mother_Key').val();
    
    if ( Operator_Key == '' || Mother_Key == '' ) {
        var Cancel_Operation = $('#iCreateMotherKey').attr('data-cancel-operation');
        
        showInfoMessage( 'error', Cancel_Operation ); // SecretManager.js
        return;
    }

    var Warning = $("#iSaveNewOeratorKey_1").attr('data-warning');
    var Confirm = $("#iSaveNewOeratorKey_1").attr('data-confirm');
    var Cancel = $("#iSaveNewOeratorKey_1").attr('data-cancel');

    var Text_1 = $("#iChangeMotherKey").attr('data-text');
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">'+Warning+'</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     '<p>' + Text_1 + '</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancelWindow" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:changeMotherKey();">'+Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );
    
    $('#iCancelWindow').focus();
}


// ==========================================================
// Change la clé Mère, transchiffre les Secrets dans la base et stocke la nouvelle clé Mère avec la clé Opérateur précisée.
function changeMotherKey() {
    var Operator_Key = $('#iNew_Operator_Key_2').val();
    var Mother_Key = $('#iNew_Mother_Key').val();
    
    if ( Operator_Key == '' || Mother_Key == '' ) {
        var Cancel_Operation = $('#iCreateMotherKey').attr('data-cancel-operation');
        
        showInfoMessage( 'error', Cancel_Operation ); // SecretManager.js
        return;
    }

    $.ajax({
        url: '../SM-admin.php?action=CMKX',
        type: 'POST',
        data: $.param({
            'Operator_Key': Operator_Key,
            'Mother_Key' : Mother_Key
            }),
        dataType: 'json',
        success: function(reponse){
            hideConfirmMessage();
            
            if ( reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            } else if ( reponse['Status'] == 'success' ) {
                $('#iNew_Operator_Key_2').val('');
                $('#iNew_Mother_Key').val('');

                w = open('','popup','width=700,height=400,toolbar=no,scrollbars=no,resizable=yes');
                w.document.write( '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '+
                    '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n' +
                    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">\n' +
                    ' <head>\n' +
                    '  <meta name="Description" content="Secret Management" />\n' +
                    '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n' +
                    '  <meta name="Author" content="Pierre-Luc MARY" />\n' +
                    '  <link rel="icon" type="image/png" href="https://secretmanager.localhost/Pictures/Logo-SM-30x30.png" />\n' +
                    '  <link rel="stylesheet" href="bootstrap/css/bootstrap.css" type="text/css" />\n' +
                    '  <link rel="stylesheet" href="https://secretmanager.localhost/Libraries/SecretManager.css" type="text/css" />\n' +
                    '  <link rel="stylesheet" href="https://secretmanager.localhost/Libraries/SecretManager-icons.css" type="text/css" />\n' +
                    '<style>\n' +
                    ' body {\n' +
                    '  background-color: white;\n' +
                    '  padding: 6px;\n' +
                    '  margin: 12px;\n' +
                    ' }\n' +
                    ' button.btn:focus {\n' +
                    '  outline-style: none;\n' +
                    ' }\n' +
                    '</style>\n' +
                    '  <title>Secret</title>\n' +
                    ' </head>\n' +
                    ' <body bgcolor="white">\n' +
                    reponse['Message'] +
                    '<p class="align-center tbrl_margin_12">' +
                    '<button id="iPrint" class="btn tbrl_margin_3" onClick="javascript:window.print();">' +
                    reponse['L_Print'] + '</button>' +
                    '<button class="btn tbrl_margin_3" onClick="javascript:window.close();">' +
                    reponse['L_Close'] + '</button>' +
                    '</p>\n' +
                    '<script>document.getElementById("iPrint").focus();</script>\n' +
                    ' </body>\n' +
                    '</html>\n' );
                w.document.close();
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write( reponse['responseText'] );
        }
    }); 
}


// Sauvegarde la valeur de la nouvelle clé Mère ainsi que sa clé Opérateur.
function confirmTranscryptMotherKey(){
    if ( $("#iNew_Operator_Key_1").val() == '' ) {
        var Warning = $("#iSaveNewOeratorKey_1").attr('data-cancel-op');
        showInfoMessage( 'error', Warning ); // SecretManager.js
        return;
    }
    
    var Warning = $("#iSaveNewOeratorKey_1").attr('data-warning');
    var Confirm = $("#iSaveNewOeratorKey_1").attr('data-confirm');
    var Cancel = $("#iSaveNewOeratorKey_1").attr('data-cancel');
    var Text_1 = $("#iSaveNewOeratorKey_1").attr('data-text-1');
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">'+Warning+'</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     '<p>' + Text_1 + '</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id=\"iConfirmSaveNewOeratorKey_1\" href="javascript:hideConfirmMessage();">'+
     Cancel+'</a>&nbsp;<a class="button" href="javascript:transcryptMotherKey();">'+Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#iConfirmSaveNewOeratorKey_1').focus();
}


// Sauvegarde la clé mère actuelle du SecretServer avec la clé Opérateur.
function transcryptMotherKey() {
    var Operator_Key = $('#iNew_Operator_Key_1').val();

    $.ajax({
        url: '../SM-admin.php?action=TMKX',
        type: 'POST',
        data: $.param({
            'Operator_Key': Operator_Key
            }),
        dataType: 'json',
        success: function(reponse){
            if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                $('#iNew_Operator_Key_1').val('');
                hideConfirmMessage();
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write( reponse['responseText'] );
        }
    }); 
}


function LoadMotherKey() {
    var Operator_Key = $('#iOperator_Key').val();

    if ( Operator_Key == '' ) {
        var Warning = $("#iSaveNewOeratorKey_1").attr('data-cancel-op');
        showInfoMessage( 'error', Warning ); // SecretManager.js
        return;
    }

    $.ajax({
        url: '../SM-admin.php?action=LKX',
        type: 'POST',
        data: $.param({
            'Operator_Key': Operator_Key
            }),
        dataType: 'json',
        success: function(reponse){
            var L_Operator = $('#iSecretServerStatus').attr('data-operator');
            var L_Creation_Date = $('#iSecretServerStatus').attr('data-date-crea');
            var L_Mother_Loaded = $('#iSecretServerStatus').attr('data-mk-loaded');
        
            if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                $('#iOperator_Key').val('');

                $('#iSecretServerStatus').html( "         <table>\n" +
                 "          <tr>\n" +
                 "           <td class=\"bold green\" colspan=\"2\">" + reponse['Message'] + "</td>\n" +
                 "          </tr>\n" +
                 "          <tr>\n" +
                 "           <td class=\"pair\">" + L_Operator + "</td>\n" +
                 "           <td class=\"pair bold\">" + reponse['Operator'] + "</td>\n" +
                 "          </tr>\n" +
                 "          <tr>\n" +
                 "           <td class=\"pair\">" + L_Creation_Date + "</td>\n" +
                 "           <td class=\"pair bold\">" + reponse['Date'] + "</td>\n" +
                 "          </tr>\n" +
                 "         </table>\n" );

            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write( reponse['responseText'] );
        }
    }); 
}


function shutdownSecretServer() {
    $.ajax({
        url: '../SM-admin.php?action=SHUTX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                $('#iSecretServerStatus').html(
                    '         <span class="bold bg-orange">&nbsp;' +
                    $('#iShutdownSecretServer').attr('data-text') +
                    "&nbsp;</span>\n"
                );
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
}


function backupSecrets() {
    $.ajax({
        url: '../SM-admin.php?action=STOR_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                $('#iDateBackup').text( reponse['Date'] );
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
}


function backupTotal() {
    $.ajax({
        url: '../SM-admin.php?action=STOR_TX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                $('#iTotalDateBackup').text( reponse['Date'] );
            } else {
                alert('Erreur sur serveur : ' + reponse);
            }
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
}


function resetEmptyField( Field_Name, Image_Name ) {
    if ( $('#'+Field_Name).val() == '' ) {
        $('#'+Image_Name).attr( 'src', Parameters['URL_PICTURES'] + '/blank.gif' );
    }
}


function noEnterKey( evt ) {
    if ( evt.which == 13 ) return false;
}
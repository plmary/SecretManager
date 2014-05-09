$(document).ready( function() {
    $(document).keyup(function(e){
        if(e.which == 27){ //|| e.which == 13){
            hideConfirmMessage();
            hideInfoMessage();
        }
    });

    $('#iOperator_Key').keyup(function(e){
        if(e.which == 13){
            LoadMotherKey();
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


    // Gère l'affichage d'une image d'attente durant un traitement
    $(document).ajaxStart(function(){
        $('<div id="wait_message" class="modal" role="dialog" tabindex="-1">' +
         '<div class="modal-body">' +
         '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
         '<p><img src="' + Parameters["URL_PICTURES"] + '/wpspin_light.gif" /> Working...</p>' +
         '</div>' +
         '</div>' +
         '</div>\n' ).prependTo( 'body' );

    }).ajaxStop(function(){
        $('#wait_message').remove();
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

                var Message;
                
                if ( reponse['Status'] == 'success' ) {
                    Message = "         <table>\n" +
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
                     "         </table>\n";
                } else {
                    Message = "         <span class=\"bold bg-orange\">&nbsp;" +
                        reponse['Message'] + "&nbsp;</span>\n";
                }

                $('#iSecretServerStatus').html( Message );

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
                $('#i_deleteSecretsDateRestore').prepend( '<option value="'+ reponse['Date1'] +'">'+ reponse['Date'] + '</option>');
                $('#i_secretsDateRestore').prepend( '<option value="'+ reponse['Date1'] +'">'+ reponse['Date'] + '</option>');
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
                $('#i_deleteFullDateRestore').prepend( '<option value="'+ reponse['Date1'] +'">'+ reponse['Date'] + '</option>');
                $('#i_fullDateRestore').prepend( '<option value="'+ reponse['Date1'] +'">'+ reponse['Date'] + '</option>');
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


function confirmRestoreSecrets() {
    var Message, Message_1, Confirm, Cancel, Warning;

    var Restore_Date = $('#i_secretsDateRestore option:selected').text();
    var Restore_Date_ID = $('#i_secretsDateRestore option:selected').val();

    $.ajax({
        async: false,
        url: '../SM-admin.php?action=L_RESTORE_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            Warning = reponse['L_Warning'];
            Message_1 = reponse['Message_1'];
            Message_2 = reponse['Message_3'];
            Cancel = reponse['L_Cancel'];
            Confirm = reponse['L_Confirm'];
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p>' + Message_2 + '</p>' +
     '<p>' + Message_1 + ' <span class="green bold">' + Restore_Date + '</span> ?</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:prepareRestoreBackup( 1, \'' + Restore_Date_ID + '\' );">' + Confirm + '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#i_cancel_m').focus();
}


function confirmRestoreFull() {
    var Message, Message_1, Confirm, Cancel, Warning;

    var Restore_Date = $('#i_fullDateRestore option:selected').text();
    var Restore_Date_ID = $('#i_fullDateRestore option:selected').val();

    $.ajax({
        async: false,
        url: '../SM-admin.php?action=L_RESTORE_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            Warning = reponse['L_Warning'];
            Message_1 = reponse['Message_2'];
            Message_2 = reponse['Message_3'];
            Cancel = reponse['L_Cancel'];
            Confirm = reponse['L_Confirm'];
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p>' + Message_2 + '</p>' +
     '<p>' + Message_1 + ' <span class="green bold">' + Restore_Date + '</span> ?</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:prepareRestoreBackup( 2, \'' + Restore_Date_ID + '\' );">' + Confirm + '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#i_cancel_m').focus();

    return;
}


function prepareRestoreBackup( Type_Backup, Restore_Date_ID ) {
    var Message, Confirm, Cancel, Warning, FileName;

    $("#confirm_message").remove();

    $.ajax({
        async: false,
        url: '../SM-admin.php?action=L_RESTORE_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            Warning = reponse['L_Warning'];
            Message_1 = reponse['Message_4'];
            Message_2 = reponse['Message_5'];
            Cancel = reponse['L_Cancel'];
            Confirm = reponse['L_Confirm'];
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    });

    if ( Type_Backup == 1 ) FileName = 'secrets_' + Restore_Date_ID + '.xml';
    else FileName = 'total_' + Restore_Date_ID + '.xml';

    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p>' + Message_2 + ' : <span class="bg-green bold rl_padding">' + FileName + '<span></p>' +
     '<p>' + Message_1 + '&nbsp;<input id="op_key"></p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:restoreBackup( \'' + FileName + '\' );">' + Confirm + '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $("#op_key").keyup(function(e){
        if(e.which == 13){
            if ( $("#op_key").val() != '' ) {
                restoreBackup( FileName );
            }
        }
    });

    $('#op_key').focus();
}


function restoreBackup( FileName ) {
    var Operator_Key = $("#op_key").val();

    if ( Operator_Key == '' ) {
        $('#op_key').focus();
        return;
    }

    $("#confirm_message").remove();

    $.ajax({
        url: '../SM-admin.php?action=LOAD_BACKUP_X',
        type: 'POST',
        dataType: 'json',
        data: $.param({'File_Name': FileName,'Operator_Key': Operator_Key}),
        success: function(response){
            showInfoMessage( response['status'], response['message'] ); // SecretManager.js

            if ( response['status'] == 'success' ) {
                hideConfirmMessage();
            }            
        },
        error: function(response) {
            document.write(response['responseText']);
        }
    }); 
}


function confirmDeleteBackupSecrets() {
    var Message, Confirm, Cancel, Warning;

    var Restore_Date = $('#i_deleteSecretsDateRestore option:selected').text();

    var i_Restore_Date = $('#i_deleteSecretsDateRestore').val();

    $.ajax({
        async: false,
        url: '../SM-admin.php?action=L_DELE_RESTORE_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            Warning = reponse['L_Warning'];
            Message = reponse['Message'];
            Cancel = reponse['L_Cancel'];
            Confirm = reponse['L_Confirm'];
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p>' + Message + ' <span class="green bold">' + Restore_Date + '</span> ?</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:deleteBackupSecrets(\'' + i_Restore_Date + '\');">' + Confirm +
     '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#i_cancel_m').focus();
}

function deleteBackupSecrets( Restore_Date ) {
    $.ajax({
        url: '../SM-admin.php?action=DELE_RESTORE_SX',
        type: 'POST',
        data: $.param({
            'Restore_Date' : Restore_Date,
            'Type': 'S'
            }),
        dataType: 'json',
        success: function(response){
            hideConfirmMessage();
            
            showInfoMessage( response['status'], response['message'] ); // SecretManager.js
            
            if ( response['status'] == 'success' ) {
                $.each( response['list'].split(','), function( index, value ) {
                    $("#i_deleteSecretsDateRestore option[value='" + value + "']").remove();
                    $("#i_secretsDateRestore option[value='" + value + "']").remove();
                } );
            }
        },
        error: function(response) {
            document.write(response['responseText']);
        }
    }); 
}


function confirmDeleteBackupTotal() {
    var Message, Confirm, Cancel, Warning;

    var Restore_Date = $('#i_deleteFullDateRestore option:selected').text();

    var i_Restore_Date = $('#i_deleteFullDateRestore').val();

    $.ajax({
        async: false,
        url: '../SM-admin.php?action=L_DELE_RESTORE_SX',
        type: 'POST',
        dataType: 'json',
        success: function(reponse){
            Warning = reponse['L_Warning'];
            Message = reponse['Message_2'];
            Cancel = reponse['L_Cancel'];
            Confirm = reponse['L_Confirm'];
        },
        error: function(reponse) {
            document.write(reponse['responseText']);
        }
    }); 
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p>' + Message + ' <span class="green bold">' + Restore_Date + '</span> ?</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:deleteBackupTotal(\'' + i_Restore_Date + '\');">' + Confirm +
     '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#i_cancel_m').focus();
}

function deleteBackupTotal( Restore_Date ) {
    $.ajax({
        url: '../SM-admin.php?action=DELE_RESTORE_SX',
        type: 'POST',
        data: $.param({
            'Restore_Date' : Restore_Date,
            'Type': 'T'
            }),
        dataType: 'json',
        success: function(response){
            hideConfirmMessage();
            
            showInfoMessage( response['status'], response['message'] ); // SecretManager.js
            
            if ( response['status'] == 'success' ) {
                $.each( response['list'].split(','), function( index, value ) {
                    $("#i_deleteFullDateRestore option[value='" + value + "']").remove();
                    $("#i_fullDateRestore option[value='" + value + "']").remove();
                } );
            }
        },
        error: function(response) {
            document.write(response['responseText']);
        }
    });
}


function confirmLoadSecretsBackup() {
    var Restore_Date = $('#i_secretsDateRestore option:selected').text();
    var i_Restore_Date = $('#i_secretsDateRestore').val();
    
    notYetImplemented();
}


function confirmLoadTotalBackup() {
    var Restore_Date = $('#i_fullDateRestore option:selected').text();
    var i_Restore_Date = $('#i_fullDateRestore').val();
    
    notYetImplemented();
}


function putModalMessage( Warning, Message, Cancel ) {
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">' + Warning + '</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:100%; margin-top:8px;">' +
     '<p class="bg-green align-center">' + Message + '</p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="i_cancel_m" href="javascript:hideConfirmMessage();">' + Cancel + '</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );
}


// =========================
// Gestion des Applications
function putCreateApplication() {
    var Title, Label, Cancel, ButtonName;
    $.ajax({
        async: false,
        url: '../../SM-secrets.php?action=L_ADD_APP_X',
        type: 'POST',
        //data: $.param({'sgr_id': Id, 'Alert': Alert, 'Label': Label}),
        dataType: 'json',
        success: function(reponse) {
            Title = reponse['Title'];
            Label = reponse['Label'];
            Cancel = reponse['Cancel'];
            ButtonName = reponse['ButtonName'];
        }
    });

    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + Label + "</span>"+
     "<span  class=\"td-aere\">"+
     "<input id=\"iApplicationLabel\" type=\"text\" class=\"obligatoire input-xlarge\" name=\"Label\" " +
     "size=\"60\" maxlength=\"60\" /></span></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:addApplication();">'+
     ButtonName+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iApplicationLabel').focus();

    $('#iApplicationLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iApplicationLabel').val() != '' ) addApplication();
        }
    });
}


function addApplication(){
    // Créé une nouvelle Application.
    if ( $('#iApplicationLabel').val() != '' ) {
        var Label = $('#iApplicationLabel').val()

        $.ajax({
            url: 'SM-secrets.php?action=ADD_APPX',
            type: 'POST',
            data: $.param({'Label': Label}),
            dataType: 'json',
            success: function(reponse) {
                // Récupère le statut de l'appel Ajax
                $('#confirm_message').hide();

                $('#iApplicationLabel').val('');

                var statut = reponse['Status'];

                if (statut == 'success') {
                    var Id = reponse['IdApplication'];
                    var Script = reponse['Script'];
                    var URL_PICTURES = reponse['URL_PICTURES'];
                    var L_Modify = reponse['L_Modify'];
                    var L_Delete = reponse['L_Delete'];
                    var L_Cancel = reponse['L_Cancel'];
                    
                    $('#liste').prepend(
                     '<tr id="app_id-'+Id+'" class="surline">'+
                     '<td class="align-middle">'+Label+'</td>'+
                     '<td style="width:40%;">'+
                     '<a id="app_mod_'+Id+'" class="simple" href="javascript:editApplication(\''+Id+'\');">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" /></a>\n'+
                     '<a class="simple" href="javascript:confirmDeleteApplication(\''+Id+'\');">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" /></a>\n'+
                     '</td>'+
                     '</tr>'
                    );
                    
                    var Total = $('#total').text();
                    Total = Number(Total) + 1;
                    $('#total').text( Total );
                    
                    hideConfirmMessage();
                }
                
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            },
            error: function(reponse) {
                alert('Erreur sur serveur : ' + reponse['responseText']);
            }
        });
    }
}


// ============================================
// Modification des Groupes de secrets en ligne.
function editApplication( Id ){
    hideAllEditApplication();

    var ApplicationName = $('#app_'+Id).text();
    
    var CancelButton, ModifyButton;
    
    $.ajax({
        async: false,
        url: '../../SM-secrets.php?action=L_EDIT_FIELDS_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            CancelButton = reponse['Cancel'];
            ModifyButton = reponse['Modify'];
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });


    $('#occ_app_'+Id).hide();
    
    $( "       <tr id=\"MOD_" + Id + "\" class=\"pair\" data-line-open=\"1\">\n" +
        "        <td class=\"align-middle blue-border-line\"><input id=\"iApplicationName\" class=\"input-xxlarge\" value=\"" + ApplicationName + "\" /></td>\n" +
        "        <td class=\"align-middle blue-border-line\"><a class=\"button tbrl_margin_6\" href=\"javascript:hideEditApplication('" + Id + "');\">" + CancelButton + "</a>" +
        "&nbsp;<a class=\"button tbrl_margin_6\" href=\"javascript:saveEditApplication('" + Id + "');\">" + ModifyButton + "</a></td>\n" +
        "       </tr>\n"
    ).insertAfter('#occ_app_'+Id);
    
    // Met le focus sur le champ.
    document.getElementById('iApplicationName').focus();

    // Place le curseur après la dernière lettre
    document.getElementById('iApplicationName').selectionStart = ApplicationName.length;

    $('#iApplicationName').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iApplicationName').val() != '' ) saveEditApplication( Id );
        }
        if (e.which == 27) {
            hideEditApplication( Id );
        }
    });
}


function hideEditApplication( Id ) {
    $('#MOD_'+Id).remove();
    $('#occ_app_'+Id).show();
}

function hideAllEditApplication() {
    $('tr[data-line-open="1"]').each( function(index) {
        var L_Id = $(this).attr("id");
        var T_Id = L_Id.split('_');
        hideEditApplication( T_Id[1] );
    } );
}


// Traite l'affichage d'un secret.
function saveEditApplication( Id ){
    var Name = $('#iApplicationName').val();
    
    if (Name != '') {
        $.ajax({
            url: '../../SM-secrets.php?action=MOD_APPX',
            type: 'POST',
            data: $.param({'app_id': Id, 'app_name': Name}),
            dataType: 'json',
            success: function(reponse) {
                var statut = reponse['Status'];

                if (statut == 'success') {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                    $('#MOD_'+Id).remove();
                                        
                    $('#app_'+Id).text( Name );

                    $('#occ_app_'+Id).show();                    
                }
                else if (statut == 'erreur') {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                }

            },
            error: function(reponse) {
                alert('Erreur serveur : ' + reponse['responseText']);
            }
        });
    }
}


function confirmDeleteApplication( Id ){
    // Demande la confirmation de suppression d'une Application.
    var Title, Label, Cancel, ButtonName;
    $.ajax({
        async: false,
        url: '../../SM-secrets.php?action=L_DEL_APP_X',
        type: 'POST',
        //data: $.param({'sgr_id': Id, 'Alert': Alert, 'Label': Label}),
        dataType: 'json',
        success: function(reponse) {
            Title = reponse['Title'];
            Label = reponse['Label'];
            Cancel = reponse['Cancel'];
            ButtonName = reponse['ButtonName'];
        }
    });

    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     "       <p>" + Label + "&nbsp;<b>" + $("#app_"+Id).text()+"</b></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:deleteApplication('+Id+');">'+
     ButtonName+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le bouton Cancel.
    $('#iCancel').focus();

    $('#iApplicationLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iApplicationLabel').val() != '' ) addApplication();
        }
    });
}


function deleteApplication( Id ){
    // Supprime une Application.
    $.ajax({
        url: 'SM-secrets.php?action=DEL_APPX',
        type: 'POST',
        data: $.param({'Id': Id}),
        dataType: 'json',
        success: function(reponse) {
            // Récupère le statut de l'appel Ajax
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                $('#occ_app_'+Id).remove();
                
                hideConfirmMessage();
            }
                    
            var Total = $('#total').text();
            Total = Number(Total) - 1;
            $('#total').text( Total );
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
}

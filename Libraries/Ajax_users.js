$(document).keyup(function(e){
    if(e.which == 27) { // || e.which == 13){
        $('#addEntity').hide();
        $('#addCivility').hide();
    }
});

$(document).ready(function(){
    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        $('#addEntity').hide();
        $('#addCivility').hide();
    });

    // Change couleur si vide ou plein des champs obligatoires
    $('input.obligatoire').focusout(function(){
        if ($(this).val() != '') {
            $(this).css("border", "1px solid #cdcdcd");
        } else {
            $(this).css("border", "2px solid #00608d");
        }
    });

    $('#iEntityLabel').keyup(function(e){
        if (e.which == 13) {
            addEntity();
        }
    });

    $('#iEntityCode').keyup(function(e){
        if (e.which == 13) {
            addEntity();
        }
    });

    $('#iButtonCreateEntity').on('click', function(){
        addEntity();
    });

    // ===================

    $('#iCivilityLastName').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iCivilityFirstName').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iCivilitySex').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iButtonCreateCivility').on('click', function(){
        addCivility();
    });

});


// Gestion des créations de Civilité à la volée.
function putAddEntity(){
    $('#addEntity').show('slow');
    $('#iEntityCode').focus();
}

function addEntity(){
    if ($('#iEntityLabel').val() != '' && $('#iEntityCode').val() != '') {
        $.ajax({
            url: 'SM-users.php?action=ENT_CX',
            type: 'POST',
            data: $.param({'Code': $('#iEntityCode').val(), 'Label': $('#iEntityLabel').val()}),
            dataType: 'json',
            success: function(reponse) {
                $('#addEntity').hide();

                var Code = $('#iEntityCode').val();
                var Label = $('#iEntityLabel').val();

                $('#iEntityLabel').val('');
                $('#iEntityCode').val('');

                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var Id = resultat['IdEntity'];
                    var Script = resultat['Script'];
                    var URL_PICTURES = resultat['URL_PICTURES'];
                    var L_Modify = resultat['L_Modify'];
                    var L_Delete = resultat['L_Delete'];

                    if ( $('#dashboard').length == 0 ) {
                        $('#iSelectEntity option').attr('selected','off')

                        $('#iSelectEntity').prepend('<option value="'+Id+'">'+Code+' - '+Label+'</option>');
                    } else {
                        $('#iListProfiles').prepend(
                         '<tr class="pair">'+
                         '<td>'+Code+'</td>'+
                         '<td>'+Label+'</td>'+
                         '<td>'+
                         '<a class="simple" href="'+Script+'?action=ENT_M&ent_id='+Id+'">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" />'+
                         '</a> '+
                         '<a class="simple" href="'+Script+'?action=ENT_D&ent_id="'+Id+'">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" />'+
                         '</a>'+
                         '</td>'+
                         '</tr>'
                        );
                    }

                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'success',
                        timeout: 2000});
                } else if (statut == 'error') {
                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'error'});
                }
            },
            error: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur serveur : ' + resultat['responseText']);
            }
        });
    }
}


// Gestion des suppressions d'Entité à la volée.
function deleteEntity( Id ){
    $.ajax({
        url: 'SM-users.php?action=ENT_DX',
        type: 'POST',
        data: $.param({'ent_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            var statut = resultat['Status'];

            if (statut == 'success') {
                $('#entity_'+Id).remove();

                $('body').notif({title: resultat['Title'],
                    content: resultat['Message'],
                    cls: 'success',
                    timeout: 2000});
            } else if (statut == 'error') {
                $('body').notif({title: resultat['Title'],
                    content: resultat['Message'],
                    cls: 'error'});
            }
        },
        error: function(reponse) {
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

            alert('Erreur sur serveur : ' + resultat['responseText']);
        }
    });
}


// Gestion des créations de Civilité à la volée.
function putAddCivility(){
    $('#addCivility').show('slow');
    $('#iCivilityFirstName').focus();
}

function addCivility(){
    if ($('#iCivilityLastName').val() != '' && $('#iCivilityFirstName').val() != '' && $('#iCivilitySex').val() != '') {
        $.ajax({
            url: 'SM-users.php?action=CVL_CX',
            type: 'POST',
            data: $.param({'Last_Name': $('#iCivilityLastName').val(), 'First_Name': $('#iCivilityFirstName').val(),
                'Sex': $('#iCivilitySex').val()}),
            dataType: 'json',
            success: function(reponse) {
                $('#addCivility').hide();

                var First_Name = $('#iCivilityFirstName').val();
                var Last_Name = $('#iCivilityLastName').val();
                var Sex = $('#iCivilitySex').val();

                $('#iCivilityFirstName').val('');
                $('#iCivilityLastName').val('');
                $('#iCivilitySex').val('');

                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var Id = resultat['IdCivility'];

                    $('#iSelectCivility option').attr('selected','off')

                    $('#iSelectCivility').prepend('<option value="'+Id+'" selected>'+First_Name+' '+Last_Name+'</option>');
                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'success',
                        timeout: 2000});
                } else if (statut == 'error') {
                    $('body').notif({title: resultat['Title'],
                        content: resultat['Message'],
                        cls: 'error'});
                }
            },
            error: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur serveur : ' + resultat['responseText']);
            }
        });
    }
}

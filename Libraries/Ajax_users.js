/**
* Ce script gère une partie des fonctions Ajax disponible pour le script "SM-users.php.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-20
*/


// Active les fonctions ci-dessous quand le DOM de la page HTML est fini de charger.
$(document).ready(function(){
    // Gère les touches du clavier sur l'ensemble de la page HTML.
    $(document).keyup(function(e){ // Gère l'utilisation de la touche "Echap".
        if(e.which == 27) {
            $('#addCivility').hide();
        }
    });


    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
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


    // Gère les touches du clavier sur les objets identifiés comme "iEntityLabel" et "iEntityCode".
    $('#iEntityLabel, #iEntityCode').keyup(function(e){
        if (e.which == 13) { // Gère le cas de la touche "Enter".
            addEntity();
        }
    });

    // Gère le click gauche sur l'objet identifié comme "iButtonCreateEntity".
    $('#iButtonCreateEntity').on('click', function(){
        addEntity();
    });


    // Gère les touches du clavier sur l'objet identifié comme "iCivilityLastName".
    $('#iCivilityLastName, #iCivilityFirstName, #iCivilitySex').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    // Gère les touches du clavier sur l'objet identifié comme "iEntityCode".
    $('#iButtonCreateCivility').on('click', function(){
        addCivility();
    });

});


// Remet le mot de passe par défaut à l'utilisateur.
// Le Mot de passe par défaut est défini dant les préférences.
function resetPassword( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_PWDX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


// Remet le compteur des tentatives de l'utilisateur à zéro.
function resetAttempt( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_ATTX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            var CurrentClass = $("#total-attempt").attr("class");
            $("#total-attempt").removeClass(CurrentClass).addClass("bg-green").html('&nbsp;0&nbsp;');
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


// Recalcul la prochaine date d'expiration de l'utilisateur.
// Le nombre de mois à ajouter à la date du jour est défini dant les préférences.
function resetExpirationDate( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_EXPX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            var CurrentClass = $("#expiration-date").attr("class");
            $("#expiration-date").removeClass(CurrentClass).addClass("bg-green").html('&nbsp;'+
                reponse['Expiration_Date']+'&nbsp;00:00:00&nbsp;');
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


// Active ou désactive un utilisateur.
function enableDisableUser( Id, Status ) {
    $.ajax({
        url: 'SM-users.php?action=RST_DISX',
        type: 'POST',
        data: $.param({'idn_id': Id, 'Status': Status}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            var CurrentClass = $("#disabled-user").attr("class");
            $("#disabled-user").removeClass(CurrentClass).addClass(reponse['Disable_Color']).html('&nbsp;'+
                reponse['Disable_Msg']+'&nbsp;');
            
            $('#action-button').attr('href',"javascript:enableDisableUser('" + Id + "','" +
                reponse['Disable_Status'] + "');").text( reponse['Disable_Action'] );
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}

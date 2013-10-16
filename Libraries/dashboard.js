$(function(){
    $("div.tableau_synthese p.titre#users").on('click', function() {
        $('div.corps#c_users').css('display','block');
    });

    $("div.tableau_synthese p.titre#groups").on('click', function() {
        $('div.corps#c_groups').css('display','block');
    });

    $("div.tableau_synthese p.titre#profiles").on('click', function() {
        $('div.corps#c_profiles').css('display','block');
    });

    $("div.tableau_synthese p.titre#entities").on('click', function() {
        $('div.corps#c_entities').css('display','block');
    });

    $("div.tableau_synthese p.titre#civilities").on('click', function() {
        $('div.corps#c_civilities').css('display','block');
    });

    $("div.tableau_synthese").on('mouseleave', function() {
        $('div.corps').css('display','');
    });
});

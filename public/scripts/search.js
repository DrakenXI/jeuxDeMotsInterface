/*
    Gestion des recherche par ajax
    Affichage des résultats
 */

var zoneResult = $("#zone_result");
var term = $("#term");
var searchMode = $("#search-mode");
var relationSelect = $('#relations');

var rechercheEnCours = false;

var elementClicked = null;

var phraseErreur = "<p>Erreur : Aucuns résultat ou erreur serveur !</p>";

function searchOnJDM(button){
    elementClicked = $(button);
    elementClicked.attr("disabled", true);
    if(term.val() != "" && term.val() != null){
        if(searchMode.val() != "" && searchMode.val() != null){
            switch (searchMode.val()) {
                case'exacte':
                    searchExact();
                    break;
                case "approximative":
                    searchApproximative();
                    break;
                case "relation":
                    searchRelation();
                    break;
                default:
                    zoneResult.html(phraseErreur);
                    break;
            }
        }
    }
}

function searchExact(){
    $.ajax({
        url: 'search/'+term.val(),
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}


function searchApproximative(){
    $.ajax({
        url: 'search-approx/'+term.val(),
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

function searchRelation(){
    $.ajax({
        url: 'search-relations/'+relationSelect.val()+'/'+term.val(),
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

function searchDone(){
    rechercheEnCours = false;
    elementClicked.attr("disabled", false);
}
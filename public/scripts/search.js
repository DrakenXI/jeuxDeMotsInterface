/*
    Gestion des recherche par ajax
    Affichage des résultats
 */

var zoneResult = $("#zone_result");
var termBarre = $("#term");
var searchMode = $("#search-mode");
var relationSelect = $('#relations');

var rechercheEnCours = false;

var submitButton = $("#search-submit-button");

var phraseErreur = "<p>Erreur : Aucun résultat ou erreur serveur !</p>";

var relationClicked = [];

var resultDefZone = $("#result_raff");
var sectionRaff = $("#section_raff");
var titleBalise = $("#titre_resultat");

function searchStart(){
    rechercheEnCours = true;
    submitButton.attr("disabled", true);
    resultDefZone.html(""); //clear du contenue
}

function searchDone(){
    rechercheEnCours = false;
    submitButton.attr("disabled", false);
    sectionRaff.removeClass("default-hiden");
}

function searchOnJDM(button){
    if(rechercheEnCours){
        return null;
    }
    if(termBarre.val() != "" && termBarre.val() != null){
        if(searchMode.val() != "" && searchMode.val() != null){
            searchStart();
            zoneResult.html("<h1>Recherche en cours !</h1><img src='/assets/rechercheEnCours.gif' alt='recherche en cours'/>");
            titleBalise.html("Résultat pour : " + termBarre.val());
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

function searchExact(termInLink){
    if( typeof(termInLink) == 'undefined' ){
        termInLink = termBarre.val();
    }
    else{
        termInLink.replace(" ", "+");
    }
    $.ajax({
        url: 'search/'+termInLink,
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
            searchRaffinementList();
            searchDone();
        }
    });
}


function searchApproximative(){
    $.ajax({
        url: 'search-approx/'+termBarre.val(),
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

/* IMPORTANT : ne pas toucher le split, la route se base sur l'ID de la relation uniquement */
function searchRelation(){
    $.ajax({
        url: 'search-relations/'+split(relationSelect.val(),"_")[0]+'/'+termBarre.val(),
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

function searchEntriesForTermByRelation(relation,term, iddiv){
    var zoneResultEntries = document.getElementById(iddiv);

    if(rechercheEnCours){
        zoneResultEntries.innerHTML = "<p>D'autres recherches sont déjà en cours</p>";
        searchDone();
        return null;
    }

    searchStart();
    zoneResultEntries.innerHTML = "<p>Recherche en cours !</p>";

    relationClicked[relation] = relation;
    var buttonRelationClicked =  $("#buttonDisplay_".relation)
    buttonRelationClicked.attr("disabled", false);
    $.ajax({
        url: '/search-entries-for-term-by-relation/'+relation+'/'+term,
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResultEntries.innerHTML = code_html;
        },
        error : function(resultat, statut, erreur){
            zoneResultEntries.innerHTML = phraseErreur;
        },
        complete : function(resultat, statut){
            searchDone();
            buttonRelationClicked.attr("disabled", true);
        }
    });
}

function getFirstDef(term ,callback){
    $.ajax({
        url: 'search-first-definition/'+term,
        type: 'GET',
        dataType : 'json',
        success : function(result, statut){
            callback(JSON.parse(result));
        },
        error : function(resultat, statut, erreur){
        },
        complete : function(resultat, statut){
        }
    });
    callback(null);
}

function searchRaffinementList(){
    $.ajax({
        url: 'search-raffinement-list/'+termBarre.val(),
        type: 'GET',
        dataType : 'json',
        success : function(result, statut){

            result = JSON.parse(result);
            if(result !== null){
                rechercheEnCours = true;
                var nbResult = 0;

                result.entries.forEach(function(e){
                    getFirstDef(e.nodeOut, function(def){
                        if(def !== null){

                            let htmlResult = "<tr><td>"+nbResult+"</td><td>"+def[0].def;
                            def[0].examples.forEach(function(ex){
                                htmlResult = htmlResult+"<br/><small><i>"+ex+"</i></small>";
                            });
                            htmlResult = htmlResult + "</td></tr>";

                            resultDefZone.append(htmlResult);
                        }
                        nbResult++;
                    });
                });
            }else{
                resultDefZone.append("Acune définition par raffinement trouvé.");
            }
        },
        error : function(resultat, statut, erreur){
            resultDefZone.append(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

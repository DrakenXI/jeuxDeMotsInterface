/**
 *
 */

var dejaCharger = [];

function resetCharger(){
    dejaCharger = [];
}

function displayEntries(id, j){
    let iddiv = "re-"+j;
    let entry = document.getElementById(iddiv);
    let termName = document.getElementById("term-name").value;
    let buttonEntry = document.getElementById("buttonDisplay_"+id);
    if(entry.style.display == "block") {
        entry.style.display = "none";
        buttonEntry.classList.remove("green-button");
    }else{
        entry.style.display = "block";
        if(dejaCharger[id] != true){
            searchEntriesForTermByRelation(id,termName,iddiv);
            dejaCharger[id] = true;
        }
        buttonEntry.classList.add("green-button");
    }
}

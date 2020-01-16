/**
 *
 */

var dejaCharger = [];

function displayEntries(id){
    console.log(id);
    let entry = document.getElementById(id);
    let termName = document.getElementById("term-name").value;
    let buttonEntry = document.getElementById("buttonDisplay_"+id);
    if(entry.style.display == "block") {
        entry.style.display = "none";
        buttonEntry.classList.remove("green-button");
    }else{
        entry.style.display = "block";
        if(dejaCharger[id] != true){
            searchEntriesForTermByRelation(id,termName);
            dejaCharger[id] = true;
        }
        buttonEntry.classList.add("green-button");
    }
}

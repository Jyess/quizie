const $ = require("jquery");

/**
 * Ajoute un texte dans le presse papier
 * @param text
 */
function copyToClipboard(text) {
  let $temp = $("<input>"); //crée une input temp
  $("body").append($temp); //ajoute au body
  $temp.val(text).select(); //ajoute et selectionne son texte
  document.execCommand("copy"); //copie
  $temp.remove(); //supprime l'input
}

/*Copie de la clé d'accès*/
$(document).on("click", ".fa-copy", function () {
  copyToClipboard($(".code").html());
});

/*Ajout champ réponse*/
$(document).on("click", ".fa-plus-square", function () {
  console.log("a");
});

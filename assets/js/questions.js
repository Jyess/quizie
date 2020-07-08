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

//ajoute un id unique au premier formulaire
let idQuestion = 1;
$("#questionsContainer > form ")
  .last() //dernier formulaire ajouté
  .attr("id", "question" + idQuestion);
idQuestion++;

/*Ajout du formulaire question*/
$(document).on("click", "#addQuestion", function (e) {
  //enlève le bouton 'Ajouter une question' qui vient d'être cliqué
  $("#addQuestion").parent().remove();

  //ajoute une icone de chargement
  $("body").append(
    '<div class="col text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
  );

  //requete ajax pour afficher le formulaire d'une question
  $.ajax({
    url: "/form-question",
  })
    .done(function (view) {
      $(".loading").parent().remove(); //on enlève l'icone de chargement
      $("#questionsContainer").append(view); //on ajoute la vue

      $("#questionsContainer > form ")
        .last() //dernier formulaire ajouté
        .attr("id", "question" + idQuestion);

      idQuestion++; // incrémente l'id de la question

      $("html, body").animate({ scrollTop: $(document).height() }, 1000); //scroll vers le bas
    })
    .fail(function (error) {
      alert("Une erreur est survenue. Merci de réessayer.");
    });
});

/*Ajout du formulaire réponse*/
// $(document).on("click", "#addReponse", function (e) {
//   //ajoute une icone de chargement
//   $(e.target).append(
//     '<div class="col-10 py-3 text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
//   );
//
//   //requete ajax pour afficher le formulaire d'une reponse
//   $.ajax({
//     url: "/form-reponse",
//   })
//     .done(function (view) {
//       $(".loading").parent().remove(); //on enlève l'icone de chargement
//       $(".reponsesContainer").append(view); //on ajoute la vue
//     })
//     .fail(function (error) {
//       alert("Une erreur est survenue. Merci de réessayer.");
//     });
// });

function addReponseForm($collectionHolder, $newLinkLi) {
  // Get the data-prototype explained earlier
  var prototype = $collectionHolder.data("prototype");

  // get the new index
  var index = $collectionHolder.data("index");

  var newForm = prototype;
  // You need this only if you didn't set 'label' => false in your tags field in TaskType
  // Replace '__name__label__' in the prototype's HTML to
  // instead be a number based on how many items we have
  // newForm = newForm.replace(/__name__label__/g, index);

  // Replace '__name__' in the prototype's HTML to
  // instead be a number based on how many items we have
  newForm = newForm.replace(/__name__/g, index);

  // increase the index with one for the next item
  $collectionHolder.data("index", index + 1);

  // Display the form in the page in an li, before the "Add a tag" link li
  var $newFormLi = $("<li></li>").append(newForm);
  $newLinkLi.before($newFormLi);
}

var $collectionHolder;
var $addTagButton = $(
  '<button type="button" class="add_tag_link">Add a reponse</button>'
);
var $newLinkLi = $("<li></li>").append($addTagButton);

// Get the ul that holds the collection of tags
$collectionHolder = $("ul.reponses");

// add the "add a tag" anchor and li to the tags ul
$collectionHolder.append($newLinkLi);

// count the current form inputs we have (e.g. 2), use that as the new
// index when inserting a new item (e.g. 2)
$collectionHolder.data("index", $collectionHolder.find("input").length);

$addTagButton.on("click", function (e) {
  // add a new tag form (see next code block)
  addReponseForm($collectionHolder, $newLinkLi);
});

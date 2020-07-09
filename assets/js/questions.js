const $ = require("jquery");

/*
initialise une variable correspondant à l'id d'une question
 */
let $idQuestion = 1;

/**
 * Ajoute du texte dans le presse papier
 * @param text
 */
function copyToClipboard(text) {
  let $temp = $("<input>"); //crée une input temp
  $("body").append($temp); //ajoute au body
  $temp.val(text).select(); //ajoute et selectionne son texte
  document.execCommand("copy"); //copie
  $temp.remove(); //supprime l'input
}

/**
 * Génère le formulaire de réponse
 * @param $reponsesContainer
 * @param $buttonContainer
 */
function ajoutFormulaireReponse($reponsesContainer, $buttonContainer) {
  // recup le data-prototype
  let prototype = $reponsesContainer.data("prototype");

  // récupère le nouvel index
  let index = $reponsesContainer.data("index");

  let formulaireReponse = prototype;

  // Replace '__name__' in the prototype's HTML to
  // instead be a number based on how many items we have
  formulaireReponse = formulaireReponse.replace(/__name__label__/g, index);

  // increase the index with one for the next item
  $reponsesContainer.data("index", index + 1);

  // Display the form in the page in an li, before the "Add a tag" link li
  let nouveauFormulaire = $("<div class='reponse'></div>").append(
    formulaireReponse
  );
  $buttonContainer.before(nouveauFormulaire);

  //css
  $(".reponse textarea").addClass("p-2 rounded border-0 w-100 mr-4");
}

/**
 * Ajoute un bouton avec un listener au bon bloc question
 * @param idQuestion id de la question (#question1)
 */
function boutonAjoutReponses(idQuestion) {
  let $reponsesContainer;
  let $boutonAjoutReponse = $(
    '<button type="button" class="btn btn-primary">Ajouter une réponse</button>'
  );
  let $buttonContainer = $("<div></div>").append($boutonAjoutReponse);

  // Get the ul that holds the collection of tags
  $reponsesContainer = $(idQuestion + " div.reponses");

  // add the "add a tag" anchor and li to the tags ul
  $reponsesContainer.append($buttonContainer);

  // count the current form inputs we have (e.g. 2), use that as the new
  // index when inserting a new item (e.g. 2)
  $reponsesContainer.data("index", $reponsesContainer.find("input").length);

  $boutonAjoutReponse.on("click", function (e) {
    ajoutFormulaireReponse($reponsesContainer, $buttonContainer);
    if ($(idQuestion + " .reponses").children().length - 1 >= 4) {
      $(this).remove();
    }
  });
}

function changeOrdreQuestion() {
  // met le numéro de chaque question dans l'ordre
  // pour tous les formulaires de question créés
  $("#questionsContainer > div.form").each(function (index) {
    index++;
    $(this).find(".questionNum").html(index);
  });
}

/**
 * Créer un formulaire pour ajouter une question
 */
function ajoutFormulaireQuestion() {
  //enlève le bouton 'Ajouter une question' qui vient d'être cliqué
  // $("#addQuestion").parent().remove();

  //ajoute une icone de chargement
  $("body").append(
    '<div class="col text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
  );

  //requete ajax pour afficher le formulaire d'une question
  $.ajax({
    url: "/form-question", //route qui genere la view
  })
    .done(function (view) {
      $(".loading").parent().remove(); //on enlève l'icone de chargement
      $("#questionsContainer").append(view); //on ajoute la vue
      changeOrdreQuestion();

      //ajout de l'id du bloc question
      $("#questionsContainer > div.form")
        .last() //au dernier formulaire ajouté
        .attr("id", "question" + $idQuestion);

      //ajoute le bouton "Ajouter une reponse"
      boutonAjoutReponses("#question" + $idQuestion);

      // incrémente l'id de la question
      $idQuestion++;

      //scroll vers le bas pour afficher le bloc question
      $("html, body").animate({ scrollTop: $(document).height() }, 1000);
    })
    .fail(function (error) {
      alert("Une erreur est survenue. Merci de réessayer.");
    });
}

$(document).ready(function () {
  /*
  Copie la clé d'accès dans le presse papier au clic du bouton "copier"
  */
  $(document).on("click", ".fa-copy", function () {
    copyToClipboard($(".code").html());
  });

  /*
  Listener sur le bouton "Ajouter une question" qui ajoute un nouveau formulaire question
  */
  $(document).on("click", "#addQuestion", function (e) {
    ajoutFormulaireQuestion();
  });

  /*submit question*/
  $("body").on("submit", "form", function (e) {
    e.preventDefault();

    //l'element fomrmulaire actuel
    let $submittedForm = $(this);

    //recup l'id du quiz dans l'htlm
    let $quizIdHolder = $(".js-quiz-id");
    let $quizId = $quizIdHolder.data("quizId");

    //data du formulaire
    let $formData = $submittedForm.serialize();

    //envoie les data
    $.ajax({
      type: "POST",
      data: $formData,
      // url: Routing.generate("quiz_enregistrerQuestion"), //route qui va recup les data et enregistrer la question dans la bd
      url: "/save-question/" + $quizId, //route qui va recup les data et enregistrer la question dans la bd,
      success: function (data, textStatus, xhr) {
        if (xhr.status === 200) {
          let $formHolder = $submittedForm.parent();
          $submittedForm.remove(); //enleve le formulaire
          $($formHolder).append(data); //on ajoute le nouveau formulaire avec erreurs au holder

          //met dans le bon ordre des questions
          changeOrdreQuestion();

          //ajoute le bouton "Ajouter une reponse"
          boutonAjoutReponses($($formHolder).attr("id"));
        }
      },
    })
      .done(function (data) {
        console.log("done");
      })
      .fail(function (error) {
        console.log("error");
      });
  });

  /* nb points aleatoire bonne reponse */
  $(document).on("click", "#js-random-point-1", function (e) {
    $(this)
      .siblings("input")
      .val(Math.ceil(Math.random() * 20));
  });

  /* nb points aleatoire mauvaise reponse */
  $(document).on("click", "#js-random-point-2", function (e) {
    $(this)
      .siblings("input")
      .val(Math.floor(Math.random() * -20));
  });

  // $(window).on("beforeunload", function () {
  //   return ""; //inutile car pas afficher
  // });
});

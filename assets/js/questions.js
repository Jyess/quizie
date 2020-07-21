// const $ = require("jquery");

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

function labelsRightWrong($formHolder) {
  $formHolder.find(".reponses label").each(function () {
    $(this).html("Réponse fausse"); //d'abord tous réponse fausse
  });

  $formHolder.find(".reponses label").first().html("Réponse juste"); //premier label de chaque question
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
  formulaireReponse = formulaireReponse.replace(/__name__/g, index);

  // increase the index with one for the next item
  $reponsesContainer.data("index", index + 1);

  // Display the form in the page in an li, before the "Add a tag" link li
  let $nouveauFormulaire = $("<div class='reponse'></div>").append(
    formulaireReponse
  );
  $buttonContainer.before($nouveauFormulaire);

  //change les labels des réponses
  let $formHolder = $reponsesContainer.closest("form").parent();
  labelsRightWrong($formHolder);
  vraiFauxValues($formHolder);
}

/**
 * Gère l'affichage et la suppression des boutons ajouter et supprimer reponse.s
 * @param nbReponses
 * @param $boutonAjoutReponse
 * @param $boutonDeleteReponse
 */
function addDeleteReponse(
  nbReponses,
  $boutonAjoutReponse,
  $boutonDeleteReponse
) {
  if (nbReponses === 0) {
    //ajouter
    $boutonAjoutReponse.show();
    $boutonDeleteReponse.hide();
  } else if (nbReponses > 0 && nbReponses < 4) {
    //ajouter et suppr
    $boutonAjoutReponse.show();
    $boutonDeleteReponse.show();
  } else {
    //suppr
    $boutonAjoutReponse.hide();
    $boutonDeleteReponse.show();
  }
}

/**
 * Ajoute un bouton avec un listener au bon bloc question
 * @param idQuestion id de la question (ex: #question1)
 */
function boutonAjoutReponsesEtFormulaires(idQuestion) {
  let $reponsesContainer;
  let $boutonAjoutReponse = $(
    '<button type="button" class="col-lg btn btn-outline-secondary mx-2 my-lg-0 my-2 addReponse">Ajouter une réponse</button>'
  );
  let $boutonDeleteReponse = $(
    '<button type="button" class="col-lg btn btn-outline-danger mx-2 my-lg-0 my-2 deleteReponse">Supprimer une réponse</button>'
  );
  let $buttonContainer = $("<div class='row'></div>").append(
    $boutonAjoutReponse
  );
  $buttonContainer.append($boutonDeleteReponse);

  // container des reponses
  $reponsesContainer = $(idQuestion + " div.reponses");

  // ajoute les boutons au container
  $reponsesContainer.append($buttonContainer);

  // count the current form inputs we have (e.g. 2), use that as the new
  // index when inserting a new item (e.g. 2)
  $reponsesContainer.data("index", $reponsesContainer.find("textarea").length);

  let nbReponses = $(idQuestion).find(".reponses").children().length - 1;
  addDeleteReponse(nbReponses, $boutonAjoutReponse, $boutonDeleteReponse);

  $boutonAjoutReponse.on("click", function (e) {
    if ($(idQuestion).find(".mandatoryReponses")) {
      $(idQuestion).find(".mandatoryReponses").remove();
    }

    ajoutFormulaireReponse($reponsesContainer, $buttonContainer);
    nbReponses = $(idQuestion).find(".reponses").children().length - 1;
    addDeleteReponse(nbReponses, $boutonAjoutReponse, $boutonDeleteReponse);
    vraiFauxValues($reponsesContainer);
  });

  $boutonDeleteReponse.on("click", function (e) {
    $(this).parent().prev().remove();
    nbReponses = $(idQuestion).find(".reponses").children().length - 1;
    addDeleteReponse(nbReponses, $boutonAjoutReponse, $boutonDeleteReponse);
    vraiFauxValues($reponsesContainer);
  });
}

/**
 * Modifie le numéro des questions pour qu'il soit toujours régulier
 */
function changeOrdreQuestion() {
  // met le numéro de chaque question dans l'ordre
  // pour tous les formulaires de question créés
  $("#questionsContainer > div.form").each(function (index) {
    index++;
    $(this).find(".questionNum").html(index);
  });
}

/**
 * Crée un formulaire pour ajouter une question
 */
function ajoutFormulaireQuestion() {
  //enlève le bouton 'Ajouter une question' qui vient d'être cliqué
  // $("#addQuestion").parent().remove();

  let $quizIdHolder = $(".js-quiz-id");
  let $quizId = $quizIdHolder.data("quizId");

  //requete ajax pour afficher le formulaire d'une question
  $.ajax({
    type: "POST",
    url: "/manage-question/" + $quizId, //route qui genere la view
  })
    .done(function (view) {
      $(".loading").parent().remove(); //on enlève l'icone de chargement

      let $formHolder = $("<div class='form'></div>");
      $("#questionsContainer").append($formHolder);
      $formHolder.append(view);

      changeOrdreQuestion();

      //ajout de l'id du bloc question
      $("#questionsContainer > div.form")
        .last() //au dernier formulaire ajouté
        .attr("id", "question" + $idQuestion);

      //ajoute les formulaires des reponses
      boutonAjoutReponsesEtFormulaires("#question" + $idQuestion);

      // incrémente l'id de la question
      $idQuestion++;

      //scroll vers le bas pour afficher le bloc question
      $("html, body").animate({ scrollTop: $(document).height() }, 1000);

      $("#addQuestion").attr("disabled", false);
    })
    .fail(function (error) {
      alert("Une erreur est survenue. Merci de réessayer.");
    });
}

/**
 * Affiche les questions deja creees d'un quiz
 * @param $quizId
 */
function afficherQuestionsDejaCreees($quizId) {
  $.ajax({
    type: "POST",
    url: "/recuperer-questions/" + $quizId,
    success: function (data, textStatus, xhr) {
      if (data.idsQuestions.length > 0) {
        $.each(data.idsQuestions, function (index, value) {
          $.ajax({
            type: "POST",
            url: "/manage-question/" + $quizId + "/" + value.id,
            success: function (data, textStatus, xhr) {
              let $formHolder = $("<div class='form'></div>");
              $("#questionsContainer").append($formHolder);
              $formHolder.append(data);

              changeOrdreQuestion();

              //ajout de l'id du bloc question
              $("#questionsContainer > div.form")
                .last() //au dernier formulaire ajouté
                .attr("id", "question" + $idQuestion);

              //ajoute les formulaires des reponses
              boutonAjoutReponsesEtFormulaires("#question" + $idQuestion);

              $formHolder.find("button.saveQuestion").attr("disabled", true);
              $formHolder.find("button.editQuestion").attr("disabled", false);
              $formHolder.find("button.deleteQuestion").attr("disabled", false);
              $formHolder.find("input, textarea").attr("disabled", true);
              $formHolder.find(".reponses button").remove();
              $formHolder.find(".random").remove();
            },
            complete: function () {
              $(".loading").parent().remove();
              labelsRightWrong($("#question" + $idQuestion));
              $("#addQuestion").show();

              // incrémente l'id de la question
              $idQuestion++;
            },
          });
        });
      } else {
        $(".loading").parent().remove();
        $("#addQuestion").show();
      }
    },
  });
}

/**
 * Attribue les valeurs vrai/faux au input
 * @param $questionHolder
 */
function vraiFauxValues($questionHolder) {
  // met l'attribut vraiFaux à faux pour les réponses fausses
  $questionHolder.find(".reponses input[id$='vraiFaux']").each(function () {
    $(this).val("0"); //d'abord tous réponse fausse
  });

  $questionHolder.find(".reponses input[id$='vraiFaux']").first().val("1"); //premier label de chaque question
}

$(document).ready(function () {
  // Copie la clé d'accès dans le presse papier au clic du bouton "copier"
  $(document).on("click", ".fa-copy", function () {
    copyToClipboard($(".code").html());
  });

  // Listener sur le bouton "Ajouter une question" qui ajoute un nouveau formulaire question
  $(document).on("click", "#addQuestion", function (e) {
    $(this).attr("disabled", true);
    ajoutFormulaireQuestion();
  });

  //id du quiz
  let $quizIdHolder = $(".js-quiz-id");
  let $quizId = $quizIdHolder.data("quizId");

  //ajoute une icone de chargement
  $("body").append(
    '<div class="col text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
  );

  afficherQuestionsDejaCreees($quizId);

  // enregistrement ou modification d'une question dans la base de données
  $(document).on("submit", "form", function (e) {
    $(window).on("beforeunload", function () {
      return ""; //inutile car pas afficher
    });

    e.preventDefault();

    if ($(".error_form")) $(".error_form").remove();

    let $questionHolder = $(e.target).parent();
    let $currentButton = $(document.activeElement);
    $currentButton.attr("disabled", true);

    // l'element fomrmulaire actuel
    let $submittedForm = $currentButton.closest("form");

    // affiche une icone de chargement
    $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');

    // id de la question si présent (modification)
    let $idQuestion = $submittedForm.find(".js-question-id").data("questionId");

    let $url = "/manage-question/" + $quizId;
    if ($idQuestion) {
      $url += "/" + $idQuestion;
    }

    //data du formulaire
    let $formData = $submittedForm.serialize();

    if ($submittedForm.find(".reponses").children().length >= 3) {
      // envoie les data
      $.ajax({
        type: "POST",
        data: $formData,
        // route qui va recup les data et enregistrer la question dans la bd
        // url: Routing.generate("quiz_enregistrerQuestion"),
        url: $url, //route qui va recup les data et enregistrer la question dans la bd,
        success: function (data, textStatus, xhr) {
          //enleve l'icone de chargement du submit de la question
          $currentButton.find("svg").remove();

          //met l'id de la question dans un champ caché
          // $($submittedForm).find(".js-question-id").val(data.idQuestion);
          $submittedForm
            .find(".js-question-id")
            .data("questionId", data.idQuestion);

          //si on revoie un code 200, la requete n'a pas mené à la creation d'une ressource
          //et on raffiche le form avec les erreurs
          if (xhr.status === 200) {
            let $formHolder = $submittedForm.parent();
            $submittedForm.remove(); //enleve le formulaire actuel
            $formHolder.append(data); //ajoute au holder le nouveau formulaire avec les erreurs

            //met dans le bon ordre les questions
            changeOrdreQuestion();

            //ajoute le bouton "Ajouter une reponse"
            boutonAjoutReponsesEtFormulaires("#" + $($formHolder).attr("id"));
          } else {
            //si on revoie un code 201, la requete a mené à la creation d'une ressource
            $currentButton.prop("disabled", true);
            $($submittedForm)
              .find("button.editQuestion")
              .prop("disabled", false);
            $($submittedForm)
              .find("button.deleteQuestion")
              .prop("disabled", false);
            $($submittedForm).find("input, textarea").prop("disabled", true);
            $($submittedForm).find(".reponses button").remove();
            $submittedForm.find(".random").remove();
          }
        },
        complete: function () {
          vraiFauxValues($questionHolder);
          labelsRightWrong($questionHolder);
        },
      })
        .done(function (data) {
          // let $quizIdHolder = $(".js-quiz-id");
          // let $quizId = $quizIdHolder.data("quizId");
          //faire div avec l'id question
        })
        .fail(function (error) {
          console.log("error");
          $($submittedForm).find("button[type='submit'] svg").remove();
        });
    } else {
      $currentButton.find("i").remove(); //i car pas encore svg (pas eu le temps i guess)

      if ($submittedForm.find(".mandatoryReponses")) {
        $submittedForm.find(".mandatoryReponses").remove();
      }

      $submittedForm
        .find(".reponses")
        .prepend(
          '<div class="mandatoryReponses alert alert-danger" role="alert">Deux réponses sont obligatoires.</div>'
        )
        .show();

      $currentButton.attr("disabled", false);
    }
  });

  // suppression d'une question dans la base de données
  $(document).on("click", ".deleteQuestion", function (e) {
    let confirmation = confirm(
      "Êtes-vous sûr de vouloir supprimer cette question ?"
    );

    if (!confirmation) {
      return;
    }

    let $formHolder = $(this).closest(".form");
    let $currentButton = $(this);

    // affiche une icone de chargement
    $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');
    $currentButton.attr("disabled", true);

    //id du quiz
    let $quizIdHolder = $(".js-quiz-id");
    let $idQuiz = $quizIdHolder.data("quizId");

    // id de la question
    // let $idQuestion = $formHolder.find(".js-question-id").val();
    let $idQuestion = $formHolder.find(".js-question-id").data("questionId");

    $.ajax({
      type: "DELETE",
      // route qui va recup les data et enregistrer la question dans la bd
      // url: Routing.generate("quiz_enregistrerQuestion"),
      url: "/delete-question/" + $idQuiz + "/" + $idQuestion, //route qui va recup les data et enregistrer la question dans la bd,
      success: function (data, textStatus, xhr) {
        $formHolder.remove();
        changeOrdreQuestion();
      },
    }).fail(function () {
      console.log("error");
      $($formHolder).find("button[type='button'] svg").remove();
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

  if ($("#copy")) {
    $(function () {
      $('[data-toggle="popover"]').popover();
    });

    $("#copy").on("click", function () {
      $("#element").popover("show");
    });
  }
});

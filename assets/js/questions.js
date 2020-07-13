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
  let $nouveauFormulaire = $("<div class='reponse'></div>").append(
    formulaireReponse
  );
  $buttonContainer.before($nouveauFormulaire);

  //change les labels des réponses
  $(".reponse label").each(function (index) {
    $(this).html("Réponse fausse"); //d'abord tous réponse fausse
    $(".reponses .reponse:first-child label").html("Réponse juste"); //premier label de chaque question
  });
}

/**
 * Ajoute un bouton avec un listener au bon bloc question
 * @param idQuestion id de la question (ex: #question1)
 */
function boutonAjoutReponsesEtFormulaires(idQuestion) {
  let $reponsesContainer;
  let $boutonAjoutReponse = $(
    '<button type="button" class="btn btn-secondary mx-2">Ajouter une réponse</button>'
  );
  let $boutonDeleteReponse = $(
    '<button type="button" class="btn btn-danger mx-2">Supprimer une réponse</button>'
  );
  let $buttonContainer = $("<div></div>").append($boutonAjoutReponse);
  $buttonContainer.append($boutonDeleteReponse);

  // container des reponses
  $reponsesContainer = $(idQuestion + " div.reponses");

  // ajoute les boutons au container
  $reponsesContainer.append($buttonContainer);

  // count the current form inputs we have (e.g. 2), use that as the new
  // index when inserting a new item (e.g. 2)
  $reponsesContainer.data("index", $reponsesContainer.find("textarea").length);

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
    })
    .fail(function (error) {
      alert("Une erreur est survenue. Merci de réessayer.");
    });
}

function afficherQuestionsDejaCreees($quizId) {
  $.ajax({
    type: "POST",
    url: "/recuperer-questions/" + $quizId,
    success: function (data, textStatus, xhr) {
      if (data.idsQuestions.length > 0) {
        $("body").append(
          '<div class="col text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
        );

        $.each(data.idsQuestions, function (index, value) {
          $.ajax({
            type: "POST",
            url: "/manage-question/" + $quizId + "/" + value,
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

              // incrémente l'id de la question
              $idQuestion++;

              $formHolder.find("button.addQuestion").prop("disabled", true);
              $formHolder.find("button.editQuestion").prop("disabled", false);
              $formHolder.find("button.deleteQuestion").prop("disabled", false);
              $formHolder.find("input, textarea").prop("disabled", true);
              $formHolder.find(".reponses button").remove();
              $formHolder.find(".random").remove();
            },
            complete: function () {
              $(".loading").parent().remove();
            },
          });
        });
      } else {
        $(".loading").parent().remove();
      }
    },
  });
}

$(document).ready(function () {
  // Copie la clé d'accès dans le presse papier au clic du bouton "copier"
  $(document).on("click", ".fa-copy", function () {
    copyToClipboard($(".code").html());
  });

  // Listener sur le bouton "Ajouter une question" qui ajoute un nouveau formulaire question
  $(document).on("click", "#addQuestion", function (e) {
    ajoutFormulaireQuestion();
  });

  //id du quiz
  let $quizIdHolder = $(".js-quiz-id");
  let $quizId = $quizIdHolder.data("quizId");

  afficherQuestionsDejaCreees($quizId);

  // enregistrement d'une question dans la base de données
  $(document).on("submit", $(".addQuestion").closest("form"), function (e) {
    e.preventDefault();

    // met l'attribut vraiFaux à faux pour les réponses fausses
    $(".reponse input[name='question[reponses][__name__][vraiFaux]']").val(0);

    // met la première réponse à vrai
    $(
      ".reponse:first-child input[name='question[reponses][__name__][vraiFaux]']"
    ).val(1);

    let $currentButton = $(document.activeElement);

    // l'element fomrmulaire actuel
    let $submittedForm = $currentButton.closest("form");

    // affiche une icone de chargement
    $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');

    // id de la question si présent (modification)
    let $idQuestion = $submittedForm.find(".js-question-id").val();

    let $url = "/manage-question/" + $quizId;
    if ($idQuestion) {
      $url += "/" + $idQuestion;
    }

    //data du formulaire
    let $formData = $submittedForm.serializeArray();
    console.log($formData);

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
        $($submittedForm).find(".js-question-id").val(data.idQuestion);

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
          $($submittedForm).find("button.editQuestion").prop("disabled", false);
          $($submittedForm)
            .find("button.deleteQuestion")
            .prop("disabled", false);
          $($submittedForm).find("input, textarea").prop("disabled", true);
          $($submittedForm).find(".reponses button").remove();
          $submittedForm.find(".random").remove();
        }
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

  //TODO
  /*supprime le textarea reponse*/
  /*TODO penser aussi a verifier que le user a pas deja écrit une reponse avant de suppr, le prevenir*/
  $(document).on("click", ".delete", function (e) {
    $(this).parent().remove();
    //
    // console.log($(this).parent().parent());
    // if ($(this).parent().children().length - 1 === 4) {
    //   console.log("ok");
    //   boutonAjoutReponses($(this).closest("div.form").attr("id"));
    // }
  });

  // $(window).on("beforeunload", function () {
  //   return ""; //inutile car pas afficher
  // });
});

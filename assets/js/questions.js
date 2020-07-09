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
 * @param idQuestion identifie l'id de la question
 */
function boutonAjoutReponses(idQuestion) {
  let $reponsesContainer;
  let $boutonAjoutReponse = $(
    '<button type="button" class="btn btn-primary">Ajouter une réponse</button>'
  );
  let $buttonContainer = $("<div></div>").append($boutonAjoutReponse);

  // Get the ul that holds the collection of tags
  $reponsesContainer = $("#question" + idQuestion + " div.reponses");

  // add the "add a tag" anchor and li to the tags ul
  $reponsesContainer.append($buttonContainer);

  // count the current form inputs we have (e.g. 2), use that as the new
  // index when inserting a new item (e.g. 2)
  $reponsesContainer.data("index", $reponsesContainer.find("input").length);

  $boutonAjoutReponse.on("click", function (e) {
    ajoutFormulaireReponse($reponsesContainer, $buttonContainer);
    if ($("#question" + idQuestion + " .reponses").children().length - 1 >= 4) {
      $(this).remove();
    }
  });
}

function changeOrdreQuestion() {
  /*lors de la suppression d'une question*/
  // met le bon numéro de question en cas de suppression
  // pour tous les formulaires de question créés
  $("#questionsContainer > form").each(function (index) {
    //pour commencer à 1
    index++;

    //change le html du numéro de la question (commence à 1) par son index
    $("#question" + index + " .questionNum").html(index);
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
    //enlève le bouton 'Ajouter une question' qui vient d'être cliqué
    $("#addQuestion").parent().remove();

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

        //ajout de l'id du bloc question
        $("#questionsContainer > form ")
          .last() //au dernier formulaire ajouté
          .attr("id", "question" + $idQuestion);

        //ajoute le bouton "Ajouter une reponse"
        boutonAjoutReponses($idQuestion);

        changeOrdreQuestion();

        // incrémente l'id de la question
        $idQuestion++;

        //scroll vers le bas pour afficher le bloc question
        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
      })
      .fail(function (error) {
        alert("Une erreur est survenue. Merci de réessayer.");
      });
  });

  /*submit question*/
  $("body").on("submit", "form", function (e) {
    e.preventDefault();

    //recup l'id du quiz dans l'htlm
    let $quizIdHolder = $(".js-quiz-id");
    let $quizId = $quizIdHolder.data("quizId");

    //rajoute l'id dans le formulaire
    let $form = $("form").serializeArray();
    $.each($form, function (index) {
      if ($form[index].name === "question[quiz]") {
        $form[index].value = $quizId;
      }
    });

    let $formulaireActuel = $(this);

    //envoie les data
    $.ajax({
      type: "POST",
      data: $form,
      dataType: "json",
      url: "/save-question", //route qui va recup les data et enregistrer la question dans la bd
    })
      .done(function (view) {
        if (view) {
          $($formulaireActuel)
            .find(".errorContainer")
            .append("<div class='errors alert alert-danger'></div>");
          $(".errors").html(view.msg);
        } else {
          $(".errors").hide();
        }
      })
      .fail(function (error) {
        console.log(error);
      });
  });

  /* nb points aleatoire bonne reponse */
  $(document).on("click", "#js-random-point-1", function (e) {
    console.log(
      $(this)
        .siblings("input")
        .val(Math.ceil(Math.random() * 20))
    );
  });

  /* nb points aleatoire mauvaise reponse */
  $(document).on("click", "#js-random-point-2", function (e) {
    console.log(
      $(this)
        .siblings("input")
        .val(Math.floor(Math.random() * -20))
    );
  });
});

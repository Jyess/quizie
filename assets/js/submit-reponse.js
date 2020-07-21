// const $ = require("jquery");

$(document).on("submit", "form", function (e) {
  e.preventDefault();
  e.stopImmediatePropagation(); //aucun autre submit est appelé mais form submit twice sans ca

  let $quizIdHolder = $(".js-quiz-id");
  let $idQuiz = $quizIdHolder.data("quizId");

  let $currentButton = $("#validerQuiz");
  $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');
  $currentButton.attr("disabled", true);

  // le formulaire
  let $submittedForm = $currentButton.closest("form");
  let $formData = $submittedForm.serialize();

  $.ajax({
    type: "POST",
    data: $formData,
    url: "/verif-quiz/" + $idQuiz,
    success: function (data, textStatus, xhr) {
      if (data.error) {
        console.log("error verif");
      } else {
        $("input").attr("disabled", true);
        $currentButton.remove();
        $("#quizSubmitted").append(
          '<div class="alert alert-success" role="alert">Vos réponses ont été enregistrées !</div>' +
            `<a class="btn btn-secondary" href="/quiz/page" role="button">Faire un autre quiz</a>`
        );
      }
    },
  });
});

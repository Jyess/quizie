const $ = require("jquery");

$(document).one("submit", "form", function (e) {
  e.preventDefault();
  // e.stopImmediatePropagation(); //aucun autre submit est appelé mais form submit twice sans ca

  let $quizIdHolder = $(".js-quiz-id");
  let $idQuiz = $quizIdHolder.data("quizId");

  let $currentButton = $(document.activeElement);

  // le formulaire
  let $submittedForm = $currentButton.closest("form");

  let $formData = $submittedForm.serialize();

  $.ajax({
    type: "POST",
    data: $formData,
    url: "/verif-quiz/" + $idQuiz,
    success: function (data, textStatus, xhr) {
      if (data.error) {
        console.log("error euh voila");
      } else {
        $("label").prop("disabled", true);
        $currentButton.remove();
        $("#quizSubmitted").append(
          '<div class="alert alert-success" role="alert">Vos réponses ont été enregistrées !</div>' +
            `<a class="btn btn-secondary" href="/quiz" role="button">Faire un autre quiz</a>`
        );
      }
    },
  });
});

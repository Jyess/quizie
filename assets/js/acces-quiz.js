// const $ = require("jquery");

$(document).on("submit", "form", function (e) {
  e.preventDefault();

  let $currentButton = $("#verifCode");

  $currentButton.attr("disabled", true);

  // le formulaire
  let $submittedForm = $currentButton.closest("form");

  // affiche une icone de chargement
  $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');

  // id du quiz
  let $idQuiz = $submittedForm.find("#idQuiz").val();

  //data du formulaire
  let $formData = $submittedForm.serialize();

  // envoie les data
  $.ajax({
    type: "POST",
    data: $formData,
    // route qui va verifier le code acces du quiz
    url: "/quiz/" + $idQuiz,
    success: function (data, textStatus, xhr) {
      if (data.error) {
        $("#error").html(
          '<div class="alert alert-danger" role="alert">La clé d\'accès saisie est incorrecte. Veuillez réessayer.</div>'
        );
        $submittedForm.find("button[type='submit'] svg").remove();
      } else {
        $("#content").html(data);
      }

      $currentButton.attr("disabled", false);
    },
  }).fail(function (error) {
    console.log("error");
    $submittedForm.find("button[type='submit'] svg").remove();
    $currentButton.attr("disabled", false);
  });
});

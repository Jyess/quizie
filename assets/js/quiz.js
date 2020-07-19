const $ = require("jquery");

function displayOrHideOptionalFields() {
  let selected_option_value = $("#quiz_etat").val(); //valeur de la sélection de l'état du quiz

  //si l'état est privé
  if (selected_option_value === "1") {
    $(".optional").show();
  } else {
    $(".optional").hide();
    $("input[type=datetime-local]").val(""); //reset value si on passe en public
  }
}

$(document).ready(function () {
  //de base tout est caché mais si le user fait une erreur et que l'état était privé, il faut réafficher de suite les fields
  if ($("#quiz_etat")) {
    displayOrHideOptionalFields();
  }

  //quand on change d'état
  $("#quiz_etat").change(function () {
    displayOrHideOptionalFields();
  });

  $(".supprimerQuiz").on("click", function () {
    return confirm("Êtes-vous sûr de vouloir supprimer ce quiz ?");
  });

  $(document).one("click", "#quiz_envoyer", function (e) {
    let $currentButton = $("#quiz_envoyer");
    $currentButton.attr("disabled", true);
    $currentButton.append('<i class="ml-2 fas fa-circle-notch fa-spin">');

    let $submittedForm = $currentButton.closest("form");
    $submittedForm.submit();
  });
});

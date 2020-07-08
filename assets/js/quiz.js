const $ = require("jquery");

/**
 * Ajoute et/ou enlève une classe d'un array d'éléments
 * @param children
 * @param classToAdd
 * @param classToRemove
 */
function addRemoveClassesFromChildren(
  children,
  classToAdd = "",
  classToRemove = ""
) {
  $(children).each(function (i, obj) {
    $(obj).removeClass(classToRemove);
    $(obj).addClass(classToAdd);
  });
}

function displayOrHideOptionalFields() {
  let selected_option = $("#create_quiz_etat").val(); //valeur de la sélection de l'état du quiz à sa création

  //si l'état est privé
  if (selected_option === "1") {
    addRemoveClassesFromChildren(".optional", "d-block", "d-none");
  } else {
    addRemoveClassesFromChildren(".optional", "d-none", "d-block");
    $("input[type=datetime-local]").val(""); //reset value si on passe en public
  }
}

function displayOrHideOptionalFields() {
  let selected_option = $("#create_quiz_etat").val(); //valeur de la sélection de l'état du quiz à sa création

  //si l'état est privé
  if (selected_option === "1") {
    addRemoveClassesFromChildren(".optional", "d-block", "d-none");
  } else {
    addRemoveClassesFromChildren(".optional", "d-none", "d-block");
    $("input[type=datetime-local]").val(""); //reset value si on passe en public
  }
}

//de base tout est caché mais si le user fait une erreur et que l'état était privé, il faut réafficher de suite les fields
displayOrHideOptionalFields();

//quand on change d'état
$("#create_quiz_etat").change(function () {
  displayOrHideOptionalFields();
});

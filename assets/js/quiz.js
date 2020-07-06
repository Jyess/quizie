const $ = require("jquery");

/**
 * Add and/or remove classes from an array of elements.
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

  //if state is private
  if (selected_option === "1") {
    addRemoveClassesFromChildren(".optional", "d-block", "d-none");
  } else {
    addRemoveClassesFromChildren(".optional", "d-none", "d-block");
    $("input[type=datetime-local]").val("");
  }
}

$(document).ready(function () {
  //de base tout est caché mais si le user fait une erreur et que l'état était privé, il faut réafficher de suite les fields
  displayOrHideOptionalFields();
  //when changing state
  $("#create_quiz_etat").change(function () {
    displayOrHideOptionalFields();
  });

  //TODO : A MODIFIER !!!!!!!!!!
  $("#create_quiz_plageHoraireDebut_date").addClass("row");
  $("#create_quiz_plageHoraireDebut_time").addClass("row");
  $("#create_quiz_plageHoraireFin_date").addClass("row");
  $("#create_quiz_plageHoraireFin_time").addClass("row");

  let debutDateChildren = $("#create_quiz_plageHoraireDebut_date").children();
  let debutHeureChildren = $("#create_quiz_plageHoraireDebut_time").children();
  let finDateChildren = $("#create_quiz_plageHoraireFin_date").children();
  let finHeureChildren = $("#create_quiz_plageHoraireFin_time").children();

  addRemoveClassesFromChildren(debutDateChildren, "col-2");
  addRemoveClassesFromChildren(debutHeureChildren, "col-2");
  addRemoveClassesFromChildren(finDateChildren, "col-2");
  addRemoveClassesFromChildren(finHeureChildren, "col-2");
});

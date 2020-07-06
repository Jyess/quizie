const $ = require("jquery");

$(document).ready(function () {
  $("#create_quiz_etat").change(function () {
    let selected_option = $("#create_quiz_etat").val();

    //if state is private
    if (selected_option == 1) {
      toggleOptionalFields("d-block", "d-none");
    } else {
      toggleOptionalFields("d-none", "d-block");
    }
  });
});

/**
 * Display or remove the optional fields when switching between public and private state.
 * @param classToAdd
 * @param classToRemove
 */
function toggleOptionalFields(classToAdd, classToRemove) {
  $(".optional").each(function (i, obj) {
    $(obj).removeClass(classToRemove);
    $(obj).addClass(classToAdd);
  });
}

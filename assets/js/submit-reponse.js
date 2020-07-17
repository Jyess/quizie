const $ = require("jquery");

$(document).on("submit", "form", function (e) {
  e.preventDefault();
  e.stopImmediatePropagation(); //aucun autre submit est appelé mais form submit twice sans ca

  let $currentButton = $(document.activeElement);

  // le formulaire
  let $submittedForm = $currentButton.closest("form");

  console.log($submittedForm.serializeArray());
});

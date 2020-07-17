const $ = require("jquery");

$(window).on("load", function () {
  // id du quiz
  let $idQuiz = $("body").find("#idQuiz").val();

  //ajoute une icone de chargement
  $("body").append(
    '<div class="col text-center"><i class="loading fas fa-circle-notch fa-spin"></i></div>'
  );

  $.ajax({
    type: "POST",
    url: "/quiz/" + $idQuiz,
    success: function (data, textStatus, xhr) {
      $("#content").html(data);
      $(".loading").parent().remove(); //on enlève l'icone de chargement
    },
  }).fail(function (error) {
    console.log("error");
  });
});

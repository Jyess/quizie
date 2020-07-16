const $ = require("jquery");

$(window).on("load", function () {
  // id du quiz
  let $idQuiz = $("body").find("#idQuiz").val();

  $.ajax({
    type: "POST",
    url: "/quiz/" + $idQuiz,
    success: function (data, textStatus, xhr) {
      $("#content").html(data);
    },
  }).fail(function (error) {
    console.log("error");
  });
});

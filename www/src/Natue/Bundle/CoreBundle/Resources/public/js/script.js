$(function() {
  ZenstruckFormHelper.initialize();
});
$(document).ready(function() {
  $("#natue_userbundle_group_roles").select2({placeholder: "Select a Role"});

  /* Bloqueio do botão validate em conference(picking list) */
  // $("#button-validate").click(function() {
  //   setTimeout(function () {
  //     $('html').bind('keypress', function(e)
  //     {
  //       if(e.keyCode == 13)
  //       {
  //         return false;
  //       }
  //     });
  //     $('#button-validate').hide();
  //     $('input').prop("disabled", true);
  //   }, 500);
  // });
});

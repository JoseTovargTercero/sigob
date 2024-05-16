
/**
 * Sets the view for the registration section.
 */
function setVistaRegistro(param = null) {
  if ($("#section_registro").hasClass("hide")) {
    $("#section_registro").removeClass("hide");
    $("#btn-svr").text("Cancelar registro");
  } else {
    $("#section_registro").addClass("hide");
    $("#btn-svr").text("Nuevo Concepto");
  }
  
  if (param == 'hide-s' ) {
    $("#section-registro").hide();
    $("#section-tabla").show(300);
  }
}

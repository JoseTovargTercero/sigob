
/**
 * Sets the view for the registration section.
 */
function setVistaRegistro() {
  if ($("#section_registro").hasClass("hide")) {
    $("#section_registro").removeClass("hide");
    $("#btn-svr").text("Cancelar registro");
  } else {
    $("#section_registro").addClass("hide");
    $("#btn-svr").text("Nuevo Concepto");
  }
}

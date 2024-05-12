/**
 * Initializes the DataTable.
 */
$(document).ready(function () {
  var DataTable = $("#table").DataTable({
    language: {
      decimal: "",
      emptyTable: "No hay información",
      info: "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
      infoEmpty: "Mostrando 0 to 0 of 0 Entradas",
      infoFiltered: "(Filtrado de _MAX_ total entradas)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: "Mostrar _MENU_ Entradas",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      search: "Buscar:",
      zeroRecords: "Sin resultados encontrados",
      paginate: {
        first: "Primero",
        last: "Ultimo",
        next: "Siguiente",
        previous: "Anterior",
      },
    },
    ordering: false,
    //desactiva data-dt-column
    info: false,
    columnDefs: [
      {
        targets: [0, 1],
        className: "text-start",
      },
    ],
  });
});

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

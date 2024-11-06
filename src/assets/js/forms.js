document
  .getElementById("data_form_standar")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const form = event.target;
    let errors = false;

    // Recorrer automáticamente cada campo del formulario y validar
    for (let element of form.elements) {
      // Validar solo los elementos de entrada que tengan un nombre (excluye botones y otros)
      if (
        element.tagName === "INPUT" ||
        element.tagName === "SELECT" ||
        element.tagName === "TEXTAREA"
      ) {
        if (element.name != "") {
          if (!validarCampo(element.name)) {
            errors = true;
          }
        }
      }
    }

    if (errors) {
      toast_s("error", "Todos los campos son obligatorios.");
      return;
    }

    const formData = new FormData(form);
    const formObject = {};

    // Convertir FormData a un objeto para enviarlo como JSON
    formData.forEach((value, key) => {
      formObject[key] = value;
    });

    if (id_ejercicio) {
      formObject["id_ejercicio"] = id_ejercicio;
    }

    if (accion === "actualizar") {
      formObject["id"] = edt;
    }

    $.ajax({
      url: url_back,
      type: "json",
      contentType: "application/json",
      data: JSON.stringify({
        accion: accion,
        info: formObject,
      }),
      success: function (response) {
        if (response.success) {
          get_tabla();
          toggleDialogs();
          $("#data_form_standar")[0].reset();
          $(".chosen-select").chosen().trigger("chosen:updated");
          toast_s("success", "Se ha agregado con éxito.");
        } else {
          toast_s("error", response.error);
        }
      },
      error: function (xhr, status, error) {
        toast_s(
          "error",
          "Ocurrió un error al ejecutar la orden " + xhr.responseText
        );
      },
    });
  });

/**
 * Sets the view for the registration section.
 */
function setVistaRegistro(param = null) {
  if (param == null) {
    if ($("#section_registro").hasClass("hide")) {
      $("#section_registro").removeClass("hide");
      $("#btn-svr").text("Cancelar registro");
    } else {
      $("#section_registro").addClass("hide");
      $("#btn-svr").text("Nuevo Concepto");
    }
  }

  if (param == "hide-s") {
    $("#section-registro").addClass("hide");
    $("#section-editar").addClass("hide");
    $("#section_registro").addClass("hide");
    $("#section-tabla").removeClass("hide");
  }
}

/**
 * Adds a condition to a textarea based on the provided field, operator, and value.
 *
 * @param {string} campo - The field to be used in the condition.
 * @param {string} operador - The operator to be used in the condition.
 * @param {string} valor - The value to be used in the condition.
 */

const space_areas = {
  "result-em_nomina": "t_area-2",
  result: "t_area-1",
};

function addCondicion(campo, operador, valor, div = null) {
  let t_area = document.getElementById(textarea);
  if (div != "null") {
    t_area = document.getElementById(space_areas[div]);
  }

  let areaValue = t_area.value;
  let value = valor;

  if (operador != ">" && operador != "<") {
    value = `'` + valor + `'`;
  }
  // verifies if t_area has text, if true, using a swal, asks if the user wants to use OR or AND before the condition, depending on the selection, adds them before
  // if it doesn't have text, adds the condition directly
  if (areaValue != "" && areaValue != null) {
    Swal.fire({
      title: "Operador lógico",
      text: "¿Qué operador lógico quieres usar?",
      icon: "question",
      showCancelButton: false,
      showDenyButton: true,
      confirmButtonColor: "#04a9f5",
      denyButtonColor: "#d33",
      confirmButtonText: "AND",
      denyButtonText: "OR",
    }).then((result) => {
      if (result.isConfirmed) {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value =
          textBefore + ` AND ` + campo + operador + value + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart =
          cursor + 5 + campo.length + operador.length + value.length;
        t_area.selectionEnd =
          cursor + 5 + campo.length + operador.length + value.length;
      } else if (result.isDenied) {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value =
          textBefore + ` OR ` + campo + operador + value + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart =
          cursor + 4 + campo.length + operador.length + value.length;
        t_area.selectionEnd =
          cursor + 4 + campo.length + operador.length + value.length;
      }
    });
  } else {
    let cursor = t_area.selectionStart;
    let text = t_area.value;
    let textBefore = text.substring(0, cursor);
    let textAfter = text.substring(cursor, text.length);
    t_area.value = textBefore + campo + operador + value + textAfter + ` `;
    t_area.focus();
    t_area.selectionStart =
      cursor + campo.length + operador.length + value.length;
    t_area.selectionEnd =
      cursor + campo.length + operador.length + value.length;
  }
}

/**
 * Sets the condition for the given value.
 *
 * @param {string} value - The value to set the condition for.
 */
const booleans = {
  1: "Si",
  0: "No",
};

function setCondicionante(condicionante, div = null) {
  if (condicionante == "") {
    return;
  }
  const resultDiv =
    div == null
      ? document.getElementById("result")
      : document.getElementById(div);

  if (condicionante == "antiguedad" || condicionante == "antiguedad_total") {
    resultDiv.innerHTML =
      `<p>` +
      (condicionante == "antiguedad"
        ? "Antiguedad (desde la fecha de ingreso)"
        : "Antiguedad (Sumando años anteriores)") +
      `:</p>`;

    resultDiv.innerHTML +=
      `<li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="ms-2 me-auto">
        <div class="fw-bold">` +
      condicionante +
      `</div>
      </div>
      <button onclick="addCondicion('` +
      condicionante +
      `', '<', 'N', ` +
      div +
      `)" type="button" class="btn btn-sm btn-info  me-2" title="Menor"><</button>
      <button onclick="addCondicion('` +
      condicionante +
      `', '>', 'N', ` +
      div +
      `)" type="button" class="btn btn-sm btn-success  me-2" title="Mayor">></button>
      <button onclick="addCondicion('` +
      condicionante +
      `', '=', 'N', ` +
      div +
      `)" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
      <button onclick="addCondicion('` +
      condicionante +
      `', '!=', 'N', ` +
      div +
      `)" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
    </li>`;

    return;
  }

  fetch("../../back/modulo_nomina/nom_columnas_return.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      columna: condicionante,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
      } else {
        // toma el html del option seleccionado en el campo tipo_calculo
        let html =
          document.getElementById("campo_condiciona").options[
            document.getElementById("campo_condiciona").selectedIndex
          ].innerHTML;

        resultDiv.innerHTML = `<p>` + html + `:</p>`;

        // recorre 'data' y verifica si es igual a 1 o 0 remplazas con si y no, sino imprimes el resultado normal
        data = data.map((value) => {
          let val = value;
          // agrega al resutdiv
          resultDiv.innerHTML +=
            `<li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">${val}</div>
                    </div>
                    <button onclick="addCondicion('` +
            condicionante +
            `', '<', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Menor"><</button>
                    <button onclick="addCondicion('` +
            condicionante +
            `', '>', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Mayor">></button>
                    <button onclick="addCondicion('` +
            condicionante +
            `', '=', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
                    <button onclick="addCondicion('` +
            condicionante +
            `', '!=', '${val}', '${div}')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
                  </li>`;
        });
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

/**
 * Toggles the visibility of the specified elements.
 * @param {...string} selectors - The CSS selectors of the elements to toggle.
 */
function toggleVisibility(...selectors) {
  selectors.forEach((selector) => {
    $(selector).toggleClass("hide");
  });
}

/**
 * Select all elements with the class 'form-control' and attach an 'input' event listener
 * Check if the element that triggered the event has the 'border-danger' class
 * If it does, remove the 'border-danger' class from the element
 */

$(document).ready(function () {
  $(".form-control").on("input", function () {
    if ($(this).hasClass("border-danger")) {
      $(this).removeClass("border-danger");
    }
  });
});

function validarCampo(campo) {
  var value = $("#" + campo).val();
  if (value.trim() == "") {
    $("#" + campo).addClass("border-danger");
    toast_s("error", "Hay campos vacíos");
    return false;
  }
  return true;
}

document.addEventListener("DOMContentLoaded", function () {
  // Select all inputs with the 'check-length' class
  const inputs = document.querySelectorAll(".check-length");

  // Iterate over each selected input
  inputs.forEach((input) => {
    // Add classes to the parent container
    const parent = input.parentElement;
    parent.classList.add("input-group", "input-group-merge");

    // Get the maximum number of characters from the 'data-max' attribute
    const maxCharacters = parseInt(input.getAttribute("data-max"));

    // Create a new 'span' element to display the remaining characters
    const textRest = document.createElement("span");
    textRest.id = "res_" + input.name;
    textRest.classList.add("input-group-text");
    textRest.innerHTML = maxCharacters;
    parent.appendChild(textRest);

    // Add an 'input' event listener to the current input
    input.addEventListener("input", function () {
      let value = input.value;
      let remaining = maxCharacters - value.length;

      // If the value length exceeds the allowed maximum, truncate the value
      if (remaining <= 0) {
        input.value = value.substring(0, maxCharacters);
        remaining = 0;
      }

      // Update the 'span' content to display the remaining characters
      textRest.textContent = remaining;
    });
  });
});

var dialogs = document.querySelector(".dialogs");
var closeButton = document.querySelector(".close-button");

function toggleDialogs() {
  dialogs.classList.toggle("show-dialogs");
}

function windowOnClick(event) {
  if (event.target === dialogs) {
    toggleDialogs();
  }
}

if ($(".dialogs").length) {
  closeButton.addEventListener("click", toggleDialogs);
  window.addEventListener("click", windowOnClick);
}

function checkTablesForData() {
  // Obtener todas las tablas en el DOM
  var tables = document.querySelectorAll("table");

  tables.forEach(function (table) {
    // Verificar si la tabla tiene un tbody
    var tbody = table.querySelector("tbody");

    if (tbody) {
      // Verificar si el tbody está vacío
      if (tbody.rows.length === 0) {
        // Crear una fila y una celda para el mensaje
        var row = tbody.insertRow();
        var cell = row.insertCell();

        // Configurar la celda para que ocupe todo el ancho de la tabla y centrar el texto
        cell.colSpan = table.rows[0].cells.length;
        cell.style.textAlign = "center";
        cell.textContent = "No hay datos para mostrar";
      }
    }
  });
}

/**
 * Displays a SweetAlert modal dialog.
 *
 * @param {string} type - The type of the alert. Can be "success", "error", "warning", "info", or "question".
 * @param {string} text - The text to display in the alert.
 */
function swal(type, text) {
  Swal.fire({
    title: "Atención",
    text: text,
    icon: type,
    confirmButtonColor: "#04a9f5",
    confirmButtonText: "Ok",
  });
}

/**
 * Capitalizes the first letter of a string.
 *
 * @param {string} string - The input string.
 * @returns {string} The input string with the first letter capitalized.
 */
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const columnas_sistema = [
  "id",
  "nacionalidad",
  "cedula",
  "nombres",
  "otros_años",
  "status",
  "observacion",
  "cod_cargo",
  "banco",
  "cuenta_bancaria",
  "hijos",
  "instruccion_academica",
  "discapacidades",
  "tipo_nomina",
  "id_dependencia",
  "verificado",
  "correcion",
  "beca",
  "fecha_ingreso",
  "id",
  "nacionalidad",
  "cedula",
  "nombres",
  "otros_años",
  "status",
  "observacion",
  "cod_cargo",
  "banco",
  "cuenta_bancaria",
  "hijos",
  "instruccion_academica",
  "discapacidades",
  "tipo_nomina",
  "verificado",
  "correcion",
  "id_categoria",
  "id_partida",
  "beca",
  "fecha_ingreso",
];

function es_columnas_sistema(column) {
  if (columnas_sistema.indexOf(column) !== -1) {
    return true;
  }

  return;
  // verificar nombre no contenga nada de palabras_ban
  for (let i = 0; i < columnas_sistema.length; i++) {
    console.log(columnas_sistema[i]);
    if (column.includes(columnas_sistema[i])) {
      return true;
    }
  }
}

function addOptionsCamposCondicionantes() {
  $("#campo_condiciona").html(` <option value="">Seleccione</option>
    <option value="cod_cargo">Código de cargo</option>
    <option value="discapacidades">Discapacidades</option>
    <option value="instruccion_academica">Instrucción académica</option>
    <option value="hijos">Hijos</option>
    <option value="antiguedad">Antigüedad (desde la fecha de ingreso)</option>
    <option value="antiguedad_total">Antigüedad (Sumando años anteriores)</option>
    <option value="tipo_nomina">Tipo de nomina</option>`);

  $.ajax({
    url: "../../back/modulo_nomina/nom_columnas_back.php",
    type: "POST",
    data: {
      tabla: true,
    },
    cache: false,
    success: function (response) {
      $("#campo_condiciona").append(
        '<optgroup label="Columnas creadas por el usuario" >'
      );
      if (response) {
        var data = JSON.parse(response);

        for (var i = 0; i < data.length; i++) {
          const columna = data[i].COLUMN_NAME;
          let columnas_s = es_columnas_sistema(columna.trim());
          if (!columnas_s) {
            $("#campo_condiciona").append(
              '<option value="' + columna + '">' + columna + "</option>"
            );
          }
        }
      }
      $("#campo_condiciona").append("</optgroup>");
    },
  });
}

// obten la url y verifica si estamos en el directorio mod_nomina
var url = window.location.href;
if (url.indexOf("mod_nomina") > -1) {
  // si estamos en el directorio mod_nomina, ejecuta la función addOptionsCampos
  addOptionsCamposCondicionantes();
}

// Configuracion de lenguaje de datatable
const lenguaje_datat = {
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
};

// Separador de miles
function agregarSeparadorMiles(numero) {
  return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// informacion del sistema
const sistema = {
  tablas: "../../back/sistema_global/_DBH-select.php",
};

/**
 * Realiza una solicitud AJAX para obtener datos de una tabla específica de la base de datos.
 * @param {string} tabla - El nombre de la tabla de la cual se desean obtener los datos.
 * @param {Object|null} config - Configuración adicional para la solicitud (opcional).
 * @returns {Promise} - Una promesa que se resuelve con la respuesta del servidor si la solicitud es exitosa,
 *                      o se rechaza con el error en caso de fallo.
 */
function dbh_select(tabla, config = null) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: sistema.tablas,
      type: "POST",
      contentType: "application/json",
      dataType: "json",
      data: JSON.stringify({
        table: tabla,
        config: config,
      }),
      success: resolve,
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
        reject(error);
      },
    });
  });
}

/**
 * Procesa la respuesta de `dbh_select` para construir opciones HTML o almacenar datos en un array.
 * @param {Object} response - La respuesta recibida del servidor.
 * @param {Array} optionsArray - Array donde se almacenarán los elementos procesados.
 * @param {string|null} selector - Selector jQuery para un elemento del DOM donde se añadirán las opciones (opcional).
 *                                 Si es null, solo se almacenarán en el array sin añadir al DOM.
 * @param {function} formatOption - Función que define el formato de cada opción procesada.
 *                                  Recibe un elemento de la respuesta y devuelve una cadena de texto o array
 *                                  según el formato necesario.
 */
function handleResponse(response, optionsArray, selector, formatOption) {
  if (response.success) {
    response.success.forEach((item) => {
      const option = formatOption(item);
      optionsArray.push(option);
      if (selector) $(selector).append(option);
    });
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const activeItem = document.querySelector(
    ".pc-item.active:not(.pc-hasmenu) a"
  );

  // Verifica que el elemento existe antes de intentar acceder a su texto
  if (activeItem) {
    const activeText = activeItem.textContent.trim(); // Obtiene el texto

    // Obtén todos los elementos con la clase .descripcion_pagina
    const descripcionPaginas = document.querySelectorAll(".descripcion_pagina");

    // Itera sobre cada elemento y rellénalo con el texto obtenido
    descripcionPaginas.forEach((descripcionPagina) => {
      descripcionPagina.textContent = activeText;
    });
  }
});

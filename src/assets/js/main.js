
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



 /**
     * Adds a condition to a textarea based on the provided field, operator, and value.
     *
     * @param {string} campo - The field to be used in the condition.
     * @param {string} operador - The operator to be used in the condition.
     * @param {string} valor - The value to be used in the condition.
     */
 function addCondicion(campo, operador, valor) {
  let t_area = document.getElementById(textarea);

  // verifies if t_area has text, if true, using a swal, asks if the user wants to use OR or AND before the condition, depending on the selection, adds them before
  // if it doesn't have text, adds the condition directly
  if (t_area.value != '') {
    Swal.fire({
      title: "Operador lógico",
      text: "¿Qué operador lógico quieres usar?",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#04a9f5",
      cancelButtonColor: "#d33",
      confirmButtonText: "AND",
      cancelButtonText: "OR",
    }).then((result) => {
      if (result.isConfirmed) {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value = textBefore + ` AND ` + campo + operador + `'` + valor + `'` + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart = cursor + 5 + campo.length + operador.length + valor.length + 3;
        t_area.selectionEnd = cursor + 5 + campo.length + operador.length + valor.length + 3;
      } else {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value = textBefore + ` OR ` + campo + operador + `'` + valor + `'` + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart = cursor + 4 + campo.length + operador.length + valor.length + 3;
        t_area.selectionEnd = cursor + 4 + campo.length + operador.length + valor.length + 3;
      }
    });

  } else {
    let cursor = t_area.selectionStart;
    let text = t_area.value;
    let textBefore = text.substring(0, cursor);
    let textAfter = text.substring(cursor, text.length);
    t_area.value = textBefore + campo + operador + `'` + valor + `'` + textAfter + ` `;
    t_area.focus();
    t_area.selectionStart = cursor + campo.length + operador.length + valor.length + 3;
    t_area.selectionEnd = cursor + campo.length + operador.length + valor.length + 3;
  }
}


/**
 * Sets the condition for the given value.
*
* @param {string} value - The value to set the condition for.
*/
const booleans = {
  1: 'Si',
  0: 'No'
}
function setCondicionante(value) {
  if (value == '') {
    return
  }
  const resultDiv = document.getElementById('result');


  if (value == 'antiguedad' || value == 'antiguedad_total') {
    resultDiv.innerHTML = `<p>` + (value == 'antiguedad' ? 'Antiguedad (desde la fecha de ingreso)' : 'Antiguedad (Sumando años anteriores)') + `:</p>`

    resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="ms-2 me-auto">
        <div class="fw-bold">`+value+`</div>
      </div>
      <button onclick="addCondicion('` + value + `', '<', 'N')" type="button" class="btn btn-sm btn-info  me-2" title="Menor"><</button>
      <button onclick="addCondicion('` + value + `', '>', 'N')" type="button" class="btn btn-sm btn-success  me-2" title="Mayor">></button>
      <button onclick="addCondicion('` + value + `', '=', 'N')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
      <button onclick="addCondicion('` + value + `', '!=', 'N')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
    </li>`;

    return
  }


  fetch('../../back/modulo_nomina/nom_columnas_return.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        columna: value
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
      } else {

        // toma el html del option seleccionado en el campo tipo_calculo
        let html = document.getElementById('campo_condiciona').options[document.getElementById('campo_condiciona').selectedIndex].innerHTML;
        let option = document.getElementById('campo_condiciona').value;

        resultDiv.innerHTML = `<p>` + html + `:</p>`

        // recorre 'data' y verifica si es igual a 1 o 0 remplazas con si y no, sino imprimes el resultado normal
        data = data.map(value => {
          let val
          if (value == '1' || value == '0') {
            val = booleans[value];
          } else {
            val = value;
          }
          // agrega al resutdiv
          resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">${val}</div>
                    </div>
                    <button onclick="addCondicion('` + option + `', '<', '${val}')" type="button" class="btn btn-sm btn-primary  me-2" title="Menor"><</button>
                    <button onclick="addCondicion('` + option + `', '>', '${val}')" type="button" class="btn btn-sm btn-primary  me-2" title="Mayor">></button>
                    <button onclick="addCondicion('` + option + `', '=', '${val}')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
                    <button onclick="addCondicion('` + option + `', '!=', '${val}')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
                  </li>`;

        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}
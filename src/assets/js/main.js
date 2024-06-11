
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

 const space_areas = {
  'result-em_nomina' : 't_area-2',
  'result' : 't_area-1',
 }
 
 function addCondicion(campo, operador, valor, div = null) {

   let t_area = document.getElementById(textarea);
   if (div != 'null') {
    t_area = document.getElementById(space_areas[div]);
    }


  let areaValue = t_area.value
  let value = valor


  if (operador != '>' && operador != '<') {
    value =  `'` + valor + `'`
  }
  // verifies if t_area has text, if true, using a swal, asks if the user wants to use OR or AND before the condition, depending on the selection, adds them before
  // if it doesn't have text, adds the condition directly
  if (areaValue != '' && areaValue != null) {
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
        t_area.value = textBefore + ` AND ` + campo + operador + value + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart = cursor + 5 + campo.length + operador.length + value.length;
        t_area.selectionEnd = cursor + 5 + campo.length + operador.length + value.length;
      } else if (result.isDenied) {
        let cursor = t_area.selectionStart;
        let text = t_area.value;
        let textBefore = text.substring(0, cursor);
        let textAfter = text.substring(cursor, text.length);
        t_area.value = textBefore + ` OR ` + campo + operador + value + textAfter + ` `;
        t_area.focus();
        t_area.selectionStart = cursor + 4 + campo.length + operador.length + value.length;
        t_area.selectionEnd = cursor + 4 + campo.length + operador.length + value.length;
      }
    });

  } else {
    let cursor = t_area.selectionStart;
    let text = t_area.value;
    let textBefore = text.substring(0, cursor);
    let textAfter = text.substring(cursor, text.length);
    t_area.value = textBefore + campo + operador + value + textAfter + ` `;
    t_area.focus();
    t_area.selectionStart = cursor + campo.length + operador.length + value.length;
    t_area.selectionEnd = cursor + campo.length + operador.length + value.length;
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

function setCondicionante(condicionante, div = null) {
  if (condicionante == '') {
    return
  }
  const resultDiv = div == null ? document.getElementById('result') : document.getElementById(div);


  if (condicionante == 'antiguedad' || condicionante == 'antiguedad_total') {
    resultDiv.innerHTML = `<p>` + (condicionante == 'antiguedad' ? 'Antiguedad (desde la fecha de ingreso)' : 'Antiguedad (Sumando años anteriores)') + `:</p>`

    resultDiv.innerHTML += `<li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="ms-2 me-auto">
        <div class="fw-bold">`+condicionante+`</div>
      </div>
      <button onclick="addCondicion('` + condicionante + `', '<', 'N', `+div+`)" type="button" class="btn btn-sm btn-info  me-2" title="Menor"><</button>
      <button onclick="addCondicion('` + condicionante + `', '>', 'N', `+div+`)" type="button" class="btn btn-sm btn-success  me-2" title="Mayor">></button>
      <button onclick="addCondicion('` + condicionante + `', '=', 'N', `+div+`)" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
      <button onclick="addCondicion('` + condicionante + `', '!=', 'N', `+div+`)" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
    </li>`;

    return
  }


  fetch('../../back/modulo_nomina/nom_columnas_return.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        columna: condicionante
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
      } else {

        // toma el html del option seleccionado en el campo tipo_calculo
        let html = document.getElementById('campo_condiciona').options[document.getElementById('campo_condiciona').selectedIndex].innerHTML;


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
                    <button onclick="addCondicion('` + condicionante + `', '<', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Menor"><</button>
                    <button onclick="addCondicion('` + condicionante + `', '>', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Mayor">></button>
                    <button onclick="addCondicion('` + condicionante + `', '=', '${val}', '${div}')" type="button" class="btn btn-sm btn-primary  me-2" title="Igual">==</button>
                    <button onclick="addCondicion('` + condicionante + `', '!=', '${val}', '${div}')" type="button" id="miBoton" class="btn btn-sm btn-danger " title="Diferente">!=</button>
                  </li>`;

        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}



/**
 * Toggles the visibility of the specified elements.
 * @param {...string} selectors - The CSS selectors of the elements to toggle.
 */
function toggleVisibility(...selectors) {
  selectors.forEach(selector => {
    $(selector).toggleClass('hide');
  });
}



/**
 * Select all elements with the class 'form-control' and attach an 'input' event listener
 * Check if the element that triggered the event has the 'border-danger' class
 * If it does, remove the 'border-danger' class from the element
*/

$(document).ready(function() {
  $('.form-control').on('input', function() {
    if ($(this).hasClass('border-danger')) {
      $(this).removeClass('border-danger');
    }
  });
});




document.addEventListener('DOMContentLoaded', function() {
  // Select all inputs with the 'check-length' class
  const inputs = document.querySelectorAll('.check-length');

  // Iterate over each selected input
  inputs.forEach(input => {
    // Add classes to the parent container
    const parent = input.parentElement;
    parent.classList.add('input-group', 'input-group-merge');

    // Get the maximum number of characters from the 'data-max' attribute
    const maxCharacters = parseInt(input.getAttribute('data-max'));

    // Create a new 'span' element to display the remaining characters
    const textRest = document.createElement('span');
    textRest.id = 'res_' + input.name;
    textRest.classList.add('input-group-text');
    textRest.innerHTML = maxCharacters
    parent.appendChild(textRest);

    // Add an 'input' event listener to the current input
    input.addEventListener('input', function() {
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

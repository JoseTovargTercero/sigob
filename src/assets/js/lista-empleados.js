

    /**
     * Adds an event listener to the 'filtro_empleados' element and performs different actions based on the selected value.
     * @param {Event} event - The event object.
     */

    function seleccion_empleados(value, result_list) {

      const filtro = value;
      const empleadosList = document.getElementById('empleados-list');
      const herramientaFormulacion = $('#herramienta-formulacion');
      const otrasNominasList = $('#otras_nominas-list');

      empleadosList.innerHTML = '';

      switch (filtro) {
        case '1':
          aplicar_filtro(1, 'null', result_list);
          herramientaFormulacion.addClass('hide');
          otrasNominasList.addClass('hide');
          break;
        case '2':
          herramientaFormulacion.removeClass('hide');
          otrasNominasList.addClass('hide');
          break;
        case '3':
          otrasNominasList.removeClass('hide');
          herramientaFormulacion.addClass('hide');
          break;
      }
    }
    /**
     * Adds an event listener to the 'otra_nominas' element and applies a filter when the value changes.
     * 
     * @param {Event} event - The event object.
     */
    document.getElementById('otra_nominas').addEventListener('change', function(event) {
      let nombre = this.value;
      if (nombre != '') {
        aplicar_filtro(3, nombre, 'empleados-list');
      }
    });

    /**
     * Adds an event listener to the 'btn-obtener' button and performs a specific action when clicked.
     * 
     * @param {Event} event - The event object.
     * @returns {void}
     */
    function validarFormula(area, result_list) {
      let condicion = $('#' + area).val();
      if (condicion == '') {
        return toast_s('error', 'Debe indicar una condición');
      } else {
        if (result_list == 'empleados-list') {
          aplicar_filtro(2, condicion, result_list);
        } else {
          let accion = 'todos'; // Definir 'accion' aquí si es necesario
          tbl_emp_seleccionados(condicion, accion); // Pasar 'accion' como parámetro
        }
      }
    }



    /**
     * Applies a filter to retrieve employees based on the specified type and filter.
     *
     * @param {string} tipo - The type of filter to apply.
     * @param {string} filtro - The filter to apply.
     */

    let empleadosFiltro = []

    function aplicar_filtro(tipo, filtro, result_list) {
      empleadosFiltro = []
      $.ajax({
        url: '../../back/modulo_nomina/nom_formulacion_back',
        type: 'POST',
        data: {
          tipo_filtro: tipo,
          filtro: filtro.trim(),
          tabla_empleados: true
        },
        success: function(response) {
          let empleados = JSON.parse(response);
          let tabla = '';

          empleados.forEach(e => {
            empleadosFiltro[e.id] = [e.id];
            tabla += '<tr>';
            tabla += '<td>' + e.cedula + '</td>';
            tabla += '<td>' + e.nombres + '</td>';
            tabla += '<td class="text-center"><input class="form-check-input itemCheckbox" onchange="guardar_empleados_nomina()" type="checkbox" value="' + e.id + '"></td>';
            tabla += '</tr>';
          });

          document.getElementById(result_list).innerHTML = tabla;
        }
      });
    }

    /**
     * Checks or unchecks all checkboxes with the class 'itemCheckbox'.
     *
     * @param {boolean} status - The status to set for all checkboxes.
     */
    function checkAll(status, subfijo) {
      let itemCheckboxes = document.querySelectorAll('.itemCheckbox' + subfijo);
      itemCheckboxes.forEach(checkbox => {
        checkbox.checked = status;
      });

      guardar_empleados_nomina()
    }

    let empleadosSeleccionados = [] // Todos los emleados seleccionados para la nomina 

    /**
     * This function is responsible for saving the selected employees for the payroll.
     * It retrieves all the selected checkboxes and adds the corresponding employees to the 'empleadosSeleccionados' array.
     */
    function guardar_empleados_nomina() {
      empleadosSeleccionados = []
      let itemCheckboxes = document.querySelectorAll('.itemCheckbox');
      itemCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
          empleadosSeleccionados.push(empleadosFiltro[checkbox.value]);
        }
      });
      document.getElementById('resumen_epleados_seleccionados').innerText = 'Empleados seleccionados: ' + empleadosSeleccionados.length;
    }

    // document.getElementsByClassName('guardar_empleados_nomina').addEventListener('click', guardar_empleados_nomina);


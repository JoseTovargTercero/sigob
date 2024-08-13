const d = document
const w = window

let requestInfo

const tableLanguage = {
  decimal: '',
  emptyTable: 'No hay datos disponibles en la tabla',
  info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
  infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
  infoFiltered: '(filtrado de _MAX_ entradas totales)',
  infoPostFix: '',
  thousands: ',',
  lengthMenu: 'Mostrar _MENU_',
  loadingRecords: 'Cargando...',
  processing: '',
  search: 'Buscar:',
  zeroRecords: 'No se encontraron registros coincidentes',
  paginate: {
    first: 'Primera',
    last: 'Última',
    next: 'Siguiente',
    previous: 'Anterior',
  },
  aria: {
    orderable: 'Ordenar por esta columna',
    orderableReverse: 'Orden inverso de esta columna',
  },
}

export async function loadEmployeeList({ listaEmpleados }) {
  if (!listaEmpleados || listaEmpleados.length === 0) {
    toast_s('error', 'Error al obtener empleados')
    console.log(listaEmpleados)
    return
  }
  let requestTable = new DataTable('#employee-list-table', {
    columns: [{ data: 'cedula' }, { data: 'nombres' }, { data: 'status' }],
    responsive: true,
    pageLength: 10,

    language: tableLanguage,
    layout: {
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  let datosOrdenados = [...listaEmpleados].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((empleado) => {
    return {
      cedula: empleado.cedula,
      nombres: empleado.nombres,
      status: ` ${empleado.status}<select class="form-select" data-employeeid=${
        empleado.id
      } data-defaultvalue="${empleado.status}" data-nombres="${
        empleado.nombres
      }" data-cedula="${empleado.cedula}"/>
      <option value="A" ${empleado.status === 'A' && 'selected'}>ACTIVO</option>
      <option value="R" ${
        empleado.status === 'R' && 'selected'
      }>RETIRADO</option>
      <option value="S" ${
        empleado.status === 'S' && 'selected'
      }>SUSPENDIDO</option>
      <option value="C" ${
        empleado.status === 'C' && 'selected'
      }>COMISION DE SERVICIO</option>
        </select>`,
    }
  })

  requestTable.clear().draw()

  // console.log(datosOrdenados)
  requestTable.rows.add(data).draw()
}

export const nom_empleados_list_card = () => {
  return `  <div class='modal-window' id="modal-employee-list">
      <div class='modal-box slide-up-animation'>
        <div class='modal-box-header card-header'>
        <div>
          <h3>Lista de empleados</h3>
          <small class="text-secondary">Verifica el estado de los empleados en nomina</small>
          </div>
          <button
              id='btn-close-employee-list-card'
              type='button'
              class='btn btn-danger'
              aria-label='Close'
            >
              &times;
            </button>
        </div>
        <div class='modal-box-content'>
          <table
            id='employee-list-table'
            class='table table-striped mx-auto'
            style='width:100%'
          >
            <thead>
              <th class='w-25'>CEDULA</th>
              <th class=''>NOMBREs</th>
              <th class='w-25'>STATUS</th>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class='modal-box-footer card-footer'>
        <button class="btn btn-primary w-100 my-auto" id="btn-confirm-list" disabled>Sin modificaciones</button>
        </div>
      </div>
    </div>`
}

import { sendCalculoNomina } from '../api/peticionesNomina.js'
import { confirmNotification, validateModal } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

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

export function employeePayTableHTML({ nominaData, columns }) {
  requestInfo = nominaData

  let { informacion_empleados, nombre_nomina } = nominaData

  let cantidad_emplados = informacion_empleados.length
  let total_a_pagar = informacion_empleados.reduce(
    (acc, el) => Number(el.total_a_pagar) + acc,
    0
  )

  let rowsTr = informacion_empleados
    .map((row) => {
      let td = ''

      Object.values(row).map((el) => {
        td += `<td>${el}</td>`
      })

      return `<tr>${td}</tr>`
    })
    .join('')

  let columnsTh = columns
    .map((el) => {
      return `<th>${el}</th>`
    })
    .join('')

  let table = `<div class='card' id='request-employee-table-card'>
      <div class='card-header'>
        <div class=''>
          <div>
            <h5 class='mb-2'>Nómina ${nombre_nomina}</h5>
          
            <ul class='d-block'>
              <li><strong>Empleados en nómina:</strong> ${cantidad_emplados} empleado/s</li>
              <li><strong>Total a pagar:</strong> ${total_a_pagar} Bs</li>
            </ul>

            <small class='d-block text-center'>
            Utilice la barra horizontal para observar la información de la
            nómina
          </small>
          </div>
        </div>
      </div>
      <div class='card-body'>
        <table
          id='request-employee-table'
          class='table table-striped'
          style='width:100%'
        >
          <thead>${columnsTh}</thead>
          <tbody>
          ${rowsTr}</tbody>
        </table>
      </div>
    </div>`

  return table
}

export async function createTable({ nominaData, columns }) {
  let { informacion_empleados, nombre_nomina } = nominaData

  // let datosOrdenados = [...informacion_empleados].sort((a, b) => a.id - b.id)

  let columnTable = columns.map((el) => {
    return { data: el }
  })

  let employeePayTable = new DataTable('#request-employee-table', {
    columns: columnTable,
    responsive: true,
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `<button id="employee-pay-request" class="btn btn-info">REALIZAR PETICIÓN</button>
        `

        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

d.addEventListener('click', (e) => {
  if (e.target.id === 'employee-pay-request') {
    e.preventDefault()
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: 'Deseas realizar esta petición?',
      successFunction: sendCalculoNomina,
      successFunctionParams: requestInfo,
    })
  }
})

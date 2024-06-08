import {
  deleteEmployee,
  getDependencyData,
  getEmployeesData,
  getJobData,
  getProfessionData,
} from '../api/empleados.js'
import { employeeCard } from '../components/nom_empleado_card.js'
import { confirmNotification, validateModal } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

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

export async function createTable({ nominaData }) {
  let employeePayTable = new DataTable('#employee-pay-table', {
    responsive: true,
    scrollY: 300,
    scrollX: 300,
    language: tableLanguage,
    order: [],
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `<a class="btn btn-primary"
        href="#">Pagar</a>`
        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
    columns: [
      { data: 'columna', width: '10%' },
      { data: 'columna', width: '10%' },
      { data: 'columna', width: '10%' },
      { data: 'columna', width: '10%' },
    ],
  })

  employeePayTable.clear().draw()

  let { informacion_empleados, nombre_nomina } = nominaData

  let datosOrdenados = [...informacion_empleados].sort((a, b) => b.id - a.id)
  let columnsText = Object.keys(datosOrdenados[0]).map((column) => {
    let obj = {}
    obj[column] = column
    return obj
  })

  console.log(columnsText)

  // let data = datosOrdenados.map((empleado) => {
  //   columnsText
  //   return {

  //   }
  // })

  // console.log(data)
  // employeePayTable.rows.add(data).draw()
}

export function employeePayTableHTML({ nominaData }) {
  let { informacion_empleados, nombre_nomina } = nominaData

  let columnsText = Object.keys(informacion_empleados[0])

  let columns = columnsText.map((el) => `<th>${el}</th>`)

  let table = `
    <div class='card' id='employee-pay-table-card'>
      <div class='card-header'>
        <div class='d-flex align-items-start justify-content-between'>
          <div>
            <h5 class='mb-0'>Nómina ${nombre_nomina}</h5>
            <small class="mt-0 text-muted">Descripción</small>
          </div>
        </div>
      </div>
      <div class='card-body'>
        <table
          id='employee-pay-table'
          class='table table-striped responsive'
          style='width:100%'
        >
          <thead class='w-100'>
        
                    </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    `

  return table
}

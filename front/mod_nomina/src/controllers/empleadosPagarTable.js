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

  let data = datosOrdenados.map((empleado) => {
    return {
      columa: empleado.nombres,
      columa: empleado.nombre,
      columa: empleado.nombres,
      columa: empleado.nombres,
      // acciones: `
      //   <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}"><i class="bx bx-detail me-1"></i>Detalles</button>
      //   <button class="btn btn-warning btn-sm btn-edit" data-id="${empleado.id_empleado}"><i class="bx bx-edit me-1"></i>Editar</button>
      //   <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}"><i class="bx bx-trash me-1"></i>Eliminar</button>`,
    }
  })

  console.log(data)

  employeePayTable.rows.add(data).draw()
}

export function employeePayTableHTML({ nominaData }) {
  let { informacion_empleados, nombre_nomina } = nominaData

  let columns = Object.keys(informacion_empleados)
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
          class='table table-striped'
          style='width:100%'
        >
          <thead class='w-100'>
          <th>Columna</th><th>Columna</th><th>Columna</th><th>Columna</th>
          
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    `

  return table
}

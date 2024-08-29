import { deleteEmployee, getEmployeesData } from '../api/empleados.js'
import { employeeCard } from '../components/nom_empleado_card.js'
import {
  closeModal,
  confirmNotification,
  openModal,
  validateModal,
} from '../helpers/helpers.js'
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

let tableColumns = [
  { data: 'nombres' },
  { data: 'cedula' },
  { data: 'dependencia' },
  { data: 'nomina' },
  { data: 'acciones' },
]

function employeeFormButton() {
  let toolbar = document.createElement('div')
  toolbar.innerHTML = `<button class="btn btn-primary" id="btn-employee-form-open">REGISTRAR EMPLEADO</button>`
  return toolbar
}

let employeeTable = new DataTable('#employee-table', {
  responsive: true,
  scrollY: 300,
  language: tableLanguage,
  layout: {
    topEnd: employeeFormButton,
    topStart: { search: { placeholder: 'Buscar...' } },
    bottomStart: 'info',
    bottomEnd: 'paging',
  },
  columns: tableColumns,
})

export const validateEmployeeTable = async () => {
  if (!d.getElementById('employee-form')) return

  employeeTable.clear().draw()

  let empleados = await getEmployeesData()

  let empleadosOrdenados = [...empleados].sort(
    (a, b) => b.id_empleado - a.id_empleado
  )

  let data = empleadosOrdenados
    .filter((empleado) => empleado.status === 'A')
    .map((empleado) => {
      return {
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
        nomina: empleado.tipo_nomina,
        acciones: `
        <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}"><i class="bx bx-detail me-1"></i>Detalles</button>
        <button class="btn btn-warning btn-sm btn-edit" data-id="${empleado.id_empleado}"><i class="bx bx-edit me-1"></i>Editar</button>
        <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}" data-table="verificados"><i class="bx bx-trash me-1"></i>Eliminar</button>`,
      }
    })

  // AÑADIR FILAS A TABLAS
  employeeTable.rows.add(data).draw()

  d.addEventListener('click', (e) => {
    // if (e.target.dataset.tableid) {
    //   mostrarTabla(e.target.dataset.tableid)
    //   d.querySelectorAll('.nav-link').forEach((el) => {
    //     el.classList.remove('active')
    //   })
    //   e.target.classList.add('active')
    // }
  })
}

export async function confirmDeleteEmployee({ id, row, table }) {
  console.log(row)
  confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: async function () {
      await deleteEmployee(id)

      employeeTable.row(row).remove().draw()
    },
  })
}

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
  toolbar.innerHTML = `<button class="btn btn-primary" id="btn-employee-form-open">DESHABILITADO</button>`
  return toolbar
}

let employeeTableVerificados = new DataTable('#employee-table-verificados', {
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

let employeeTableCorregir = new DataTable('#employee-table-corregir', {
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

let employeeTableRevision = new DataTable('#employee-table-revision', {
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

const validateEmployeeTable = async () => {
  employeeTableVerificados.clear().draw()
  employeeTableRevision.clear().draw()
  employeeTableCorregir.clear().draw()

  let empleados = await getEmployeesData()

  console.log(empleados)
  let empleadosOrdenados = [...empleados].sort(
    (a, b) => b.id_empleado - a.id_empleado
  )
  let data = {
    revision: [],
    corregir: [],
    verificados: [],
  }

  empleadosOrdenados.forEach((empleado) => {
    if (empleado.verificado === 0) {
      data.revision.push({
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
        nomina: empleado.tipo_nomina,
        acciones: `
      <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}"><i class="bx bx-detail me-1"></i>Detalles</button>
      <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}" data-table="corregir"><i class="bx bx-trash me-1"></i>Eliminar</button>`,
      })
    }

    if (empleado.verificado === 1) {
      data.verificados.push({
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
        nomina: empleado.tipo_nomina,
        acciones: `
      <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}"><i class="bx bx-detail me-1"></i>Detalles</button>
      <button class="btn btn-warning btn-sm btn-edit" data-id="${empleado.id_empleado}"><i class="bx bx-edit me-1"></i>Editar</button>
      <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}" data-table="verificados"><i class="bx bx-trash me-1"></i>Eliminar</button>`,
      })
    }

    if (empleado.verificado === 2) {
      data.corregir.push({
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
        nomina: empleado.tipo_nomina,
        acciones: `
      <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}"><i class="bx bx-detail me-1"></i>Detalles</button>
      <button class="btn btn-warning btn-sm btn-edit" data-id="${empleado.id_empleado}"><i class="bx bx-edit me-1"></i>Editar</button>
      <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}" data-table="revisar"><i class="bx bx-trash me-1"></i>Eliminar</button>`,
      })
    }
  })

  console.log(data)
  // AÑADIR FILAS A TABLAS
  employeeTableVerificados.rows.add(data.verificados).draw()
  employeeTableCorregir.rows.add(data.corregir).draw()
  employeeTableRevision.rows.add(data.revision).draw()
}

d.addEventListener('click', (e) => {
  if (e.target.classList.contains('btn-delete')) {
    let fila = e.target.closest('tr')
    e.target.dataset
    confirmDelete({
      id: e.target.dataset.id,
      row: fila,
      table: e.target.dataset.table,
    })
  }

  if (e.target.classList.contains('btn-view')) {
    employeeCard({
      id: e.target.dataset.id,
      elementToInsert: 'employee-table-view',
    })
  }

  if (e.target.id === 'btn-close-employee-card') {
    d.getElementById('modal-employee').remove()
  }

  if (e.target.dataset.tableid) {
    mostrarTabla(e.target.dataset.tableid)
    d.querySelectorAll('.nav-link').forEach((el) => {
      el.classList.remove('active')
    })

    e.target.classList.add('active')
  }
})

async function confirmDelete({ id, row, table }) {
  let userConfirm = await confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: deleteEmployee,
    successFunctionParams: id,
  })

  // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
  if (userConfirm) {
    console.log('AAA', table)
    if (table === 'verificados') {
      let filteredRows = employeeTableVerificados.rows(function (
        idx,
        data,
        node
      ) {
        return node === row
      })
      filteredRows.remove().draw()
    }
    if (table === 'corregir') {
      let filteredRows = employeeTableCorregir.rows(function (idx, data, node) {
        return node === row
      })
      filteredRows.remove().draw()
    }
    if (table === 'revisar') {
      let filteredRows = employeeTableRevision.rows(function (idx, data, node) {
        return node === row
      })
      filteredRows.remove().draw()
    }

    // let filteredRows = employeeTableVerificados.rows(function (
    //   idx,
    //   data,
    //   node
    // ) {
    //   return node === row
    // })
    // filteredRows.remove().draw()
  }
}

function mostrarTabla(tablaId) {
  let verificadosId = 'employee-table-verificados',
    corregirseId = 'employee-table-corregir',
    revisionId = 'employee-table-revision'

  if (tablaId === verificadosId) {
    d.getElementById(`${verificadosId}-container`).classList.add('d-block')
    d.getElementById(`${verificadosId}-container`).classList.remove('d-none')
    d.getElementById(`${corregirseId}-container`).classList.add('d-none')
    d.getElementById(`${corregirseId}-container`).classList.remove('block')
    d.getElementById(`${revisionId}-container`).classList.add('d-none')
    d.getElementById(`${revisionId}-container`).classList.remove('d-block')
  } else if (tablaId === corregirseId) {
    d.getElementById(`${verificadosId}-container`).classList.add('d-none')
    d.getElementById(`${verificadosId}-container`).classList.remove('d-block')
    d.getElementById(`${corregirseId}-container`).classList.add('d-block')
    d.getElementById(`${corregirseId}-container`).classList.remove('d-none')
    d.getElementById(`${revisionId}-container`).classList.add('d-none')
    d.getElementById(`${revisionId}-container`).classList.remove('d-block')
  } else if (tablaId === revisionId) {
    d.getElementById(`${verificadosId}-container`).classList.add('d-none')
    d.getElementById(`${verificadosId}-container`).classList.remove('d-block')
    d.getElementById(`${corregirseId}-container`).classList.add('d-none')
    d.getElementById(`${corregirseId}-container`).classList.remove('d-block')
    d.getElementById(`${revisionId}-container`).classList.add('d-block')
    d.getElementById(`${revisionId}-container`).classList.remove('d-none')
  }
}

validateEmployeeTable()

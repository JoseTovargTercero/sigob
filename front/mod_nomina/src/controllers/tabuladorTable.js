import { deleteEmployee, getEmployeesData } from '../api/empleados.js'
import { deleteTabulator, getTabulatorsData } from '../api/tabulator.js'
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

let tabulatorTable = new DataTable('#tabulator-table', {
  responsive: true,
  scrollY: 300,
  language: tableLanguage,
  order: [],
  layout: {
    topEnd: function () {
      let toolbar = document.createElement('div')
      toolbar.innerHTML = `<a class="btn btn-primary"
      href="nom_tabulador_registrar">Añadir Tabulador</a>`
      return toolbar
    },
    topStart: { search: { placeholder: 'Buscar...' } },
    bottomStart: 'info',
    bottomEnd: 'paging',
  },
  columns: [
    { data: 'nombre', width: '10%' },
    { data: 'grados', width: '10%' },
    { data: 'pasos', width: '10%' },
    { data: 'aniosPasos', width: '10%' },
    { data: 'acciones', width: '10%' },
  ],
})

const loadTabulatorTable = async () => {
  tabulatorTable.clear().draw()

  let tabuladores = await getTabulatorsData()
  let tabuladoresOrdenados = [...tabuladores].sort((a, b) => b.id - a.id)

  let data = tabuladoresOrdenados.map((tabulador) => {
    return {
      nombre: tabulador.nombre,
      grados: tabulador.grados,
      pasos: tabulador.pasos,
      aniosPasos: tabulador.aniosPasos,
      acciones: `
      <button class="btn btn-info btn-sm btn-view" data-id="${tabulador.id}">MOSTRAR</button>
      <button class="btn btn-warning btn-sm btn-edit" data-id="${tabulador.id}">EDITAR</button>
      <button class="btn btn-danger btn-sm btn-delete" data-id="${tabulador.id}">ELIMINAR</button>`,
    }
  })

  tabulatorTable.rows.add(data).draw()
}

export const confirmDelete = async ({ id, row }) => {
  let userConfirm = await confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: deleteTabulator,
    successFunctionParams: id,
  })

  // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
  if (userConfirm) {
    let filteredRows = tabulatorTable.rows(function (idx, data, node) {
      return node === row
    })
    filteredRows.remove().draw()
  }
}

d.addEventListener('click', (e) => {
  if (e.target.classList.contains('btn-delete')) {
    let fila = e.target.closest('tr')
    confirmDelete({ id: e.target.dataset.id, row: fila })
    tabulatorTable.draw()
  }

  if (e.target.classList.contains('btn-edit')) {
    w.location.assign(`nom_tabulador_registrar.php?id=${e.target.dataset.id}`)
  }

  // if (e.target.classList.contains('btn-view')) {
  //   employeeCard({
  //     id: e.target.dataset.id,
  //     elementToInsert: 'employee-table-view',
  //   })
  // }

  //   if (e.target.id === 'btn-close-employee-card') {
  //     d.getElementById('modal-employee').remove()
  //   }
})

loadTabulatorTable()

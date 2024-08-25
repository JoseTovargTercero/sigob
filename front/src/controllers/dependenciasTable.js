import { deleteDependencia, getDependencias } from '../api/dependencias.js'
import { confirmNotification } from '../helpers/helpers.js'
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
let dependenciaTable = new DataTable('#dependencias-table', {
  columns: [
    { data: 'cod_dependencia' },
    { data: 'dependencia' },
    { data: 'acciones' },
  ],
  responsive: true,
  scrollY: 300,
  language: tableLanguage,
  layout: {
    topEnd: function () {
      let toolbar = document.createElement('div')
      toolbar.innerHTML = `
            `
      return toolbar
    },
    topStart: { search: { placeholder: 'Buscar...' } },
    bottomStart: 'info',
    bottomEnd: 'paging',
  },
})

export async function loadDependenciaTable() {
  console.log('hola')

  let dependencias = await getDependencias()

  console.log(dependencias)
  let datosOrdenados = [...dependencias.fullInfo].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((dependencia) => {
    return {
      cod_dependencia: dependencia.cod_dependencia,
      dependencia: dependencia.dependencia,
      acciones: `
      <button class="btn btn-warning btn-sm" id="btn-edit" data-id="${dependencia.id_dependencia}">Editar</button>
      <button class="btn btn-danger btn-sm" id="btn-delete" data-id="${dependencia.id_dependencia}">Eliminar</button>
     `,
    }
  })

  dependenciaTable.clear().draw()

  // console.log(datosOrdenados)
  dependenciaTable.rows.add(data).draw()
}

export const addDependenciaFila = ({ row }) => {
  console.log('añadiendooo', row)
  dependenciaTable.row.add(row).draw()
}

export function confirmDeleteDependencia({ id, row, table }) {
  confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: async function () {
      let filteredRows = dependenciaTable.rows(function (idx, data, node) {
        return node === row
      })

      let res = await deleteDependencia({ informacion: { id: id } })
      console.log(res)
      if (res) {
        if (res.error) return
        filteredRows.remove().draw()
      }
      // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
    },
    message: '¿Deseas eliminar esta dependencia?',
  })
}

// `
// <button class="btn btn-primary btn-sm" data-correlativo="${
//   peticion.correlativo
// }" ${
//   Number(peticion.status) === 0 ? 'disabled' : ''
// } id="btn-show-request">${
//   Number(peticion.status) === 0
//     ? `<i class='bx bx-low-vision' data-correlativo="${peticion.correlativo}"></i>`
//     : `<i class='bx bxs-show' id="btn-show-request"></i>`
// }</button>
// `

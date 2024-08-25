import { getCategorias } from '../api/categorias.js'
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
let categoriasTable = new DataTable('#categorias-table', {
  columns: [
    { data: 'categoria_nombre' },
    { data: 'categoria' },
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

export async function loadCategoriasTable() {
  let categorias = await getCategorias()

  console.log(categorias)
  let datosOrdenados = [...categorias.fullInfo].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((categoria) => {
    return {
      categoria_nombre: categoria.categoria_nombre,
      categoria: categoria.categoria,
      acciones: `
      <button class="btn btn-warning btn-sm" id="btn-edit" data-id="${categoria.id}">Editar</button>
      <button class="btn btn-danger btn-sm" id="btn-delete" data-id="${categoria.id}">Eliminar</button>
     `,
    }
  })

  categoriasTable.clear().draw()

  // console.log(datosOrdenados)
  categoriasTable.rows.add(data).draw()
}

export const addCategoriaFila = ({ row }) => {
  console.log('añadiendooo', row)
  categoriasTable.row.add(row).draw()
}

export function confirmDeleteCategoria({ id, row, table }) {
  confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: async function () {
      let filteredRows = categoriasTable.rows(function (idx, data, node) {
        return node === row
      })

      if (res) {
        if (res.error) return
        filteredRows.remove().draw()
      }
      // let res = await deleteDependencyData(id)
      // if (res.error) return
      // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
    },
    message: '¿Deseas eliminar esta categoria?',
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

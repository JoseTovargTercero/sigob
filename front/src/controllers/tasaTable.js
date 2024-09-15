import { deleteCategoria, getCategorias } from '../api/categorias.js'
import {
  actualizarTasa,
  actualizarTasaManual,
  obtenerHistorialTasa,
  obtenerTasa,
} from '../api/tasa..js'
import { nomTasaCard } from '../components/nom_tasa_card.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
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
let tasaTable
export async function inicializarTasaTable() {
  tasaTable = new DataTable('#tasa-table', {
    columns: [
      { data: 'usuario' },
      { data: 'valor' },
      { data: 'fecha' },
      { data: 'descripcion' },
    ],
    ordering: false,
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

  loadTasaTable()
}

export async function loadTasaTable() {
  let tasaHistorial = await obtenerHistorialTasa()
  console.log(tasaHistorial)

  if (!Array.isArray(tasaHistorial)) return

  let data = tasaHistorial.map((registro) => {
    return {
      usuario: registro.u_nombre,
      valor: registro.precio,
      fecha: registro.fecha,
      descripcion: registro.descripcion,
    }
  })

  tasaTable.clear().draw()

  // console.log(datosOrdenados)
  tasaTable.rows.add(data).draw()
}

// export const addCategoriaFila = ({ row }) => {
//   console.log('añadiendooo', row)
//   categoriasTable.row.add(row).draw()
// }

// export function confirmDeleteCategoria({ id, row, table }) {
//   confirmNotification({
//     type: NOTIFICATIONS_TYPES.delete,
//     successFunction: async function () {
//       let res = await deleteCategoria({ informacion: { id: id } })

//       if (res) {
//         if (res.error) return
//         categoriasTable.row(row).remove().draw()
//       }
//       // let res = await deleteDependencyData(id)
//       // if (res.error) return
//       // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
//     },
//     message: '¿Deseas eliminar esta categoria?',
//   })
// }

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

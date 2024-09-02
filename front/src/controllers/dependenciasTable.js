import { getCategorias } from '../api/categorias.js'
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
    { data: 'id_categoria' },
    { data: 'acciones' },
  ],
  responsive: true,
  scrollY: 350,
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
  if (!d.getElementById('dependencia-table')) return

  let dependencias = await getDependencias()

  if (!Array.isArray(dependencias.fullInfo)) return

  let categorias = await getCategorias()

  console.log(dependencias)
  let datosOrdenados = [...dependencias.fullInfo].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((dependencia) => {
    let categoria = categorias.fullInfo.find(
      (categoria) => categoria.id === dependencia.id_categoria
    )
    return {
      cod_dependencia: dependencia.cod_dependencia,
      dependencia: dependencia.dependencia,
      id_categoria: categoria ? categoria.categoria_nombre : 'Sin categoria',
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
      let res = await deleteDependencia({ informacion: { id: id } })
      console.log(res)
      if (res) {
        if (res.error) return
        dependenciaTable.row(row).remove().draw()
      }
      // ELIMINAR FILA DE LA TABLA CON LA API DE DATATABLES
    },
    message: '¿Deseas eliminar esta unidad?',
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

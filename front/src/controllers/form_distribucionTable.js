import { getFormPartidas } from '../api/partidas.js'
import { separarMiles } from '../helpers/helpers.js'

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

let partidasTable
export const validateDistribucionTable = async ({ partidas }) => {
  partidasTable = new DataTable('#distribucion-table', {
    columns: [
      { data: 'partida' },
      // { data: 'descripcion' },
      { data: 'monto_inicial' },
      { data: 'acciones' },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de partidas</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadDistribucionTable(partidas)
}

export const loadDistribucionTable = async (partidas) => {
  // let partidas = await getFormPartidas()

  if (!Array.isArray(partidas)) return

  if (!partidas || partidas.error) return

  let datosOrdenados = [...partidas].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    return {
      partida: el.partida,
      // descripcion: el.descripcion,
      monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      acciones: `
      <button class="btn btn-info btn-sm" data-editarid="${el.id}">Modificar</button>
      `,
    }
  })

  partidasTable.clear().draw()

  // console.log(datosOrdenados)
  partidasTable.rows.add(data).draw()
}

export async function deletePartidaRow({ id, row }) {
  partidasTable.row(row).remove().draw()
}

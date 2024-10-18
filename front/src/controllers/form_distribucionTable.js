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

let distribucionTable
export const validateDistribucionTable = async ({ partidas }) => {
  console.log(partidas)
  distribucionTable = new DataTable('#distribucion-table', {
    columns: [
      { data: 'sector_nombre' },
      { data: 'sector_cod' },
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

  console.log(partidas)

  let datosOrdenados = [...partidas].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    let sectorCod = `${el.sector_informacion.programa}.${el.sector_informacion.proyecto}.${el.sector_informacion.sector}`
    return {
      sector_nombre: el.sector_informacion.nombre,
      sector_cod: sectorCod,
      partida: el.partida,
      // descripcion: el.descripcion,
      monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      acciones: `
      <button class="btn btn-info btn-sm" data-editarid="${el.id}">Modificar</button>
      <button class="btn btn-danger btn-sm" data-eliminarid="${el.id}">Eliminar</button>
      `,
    }
  })

  distribucionTable.clear().draw()

  // console.log(datosOrdenados)
  distribucionTable.rows.add(data).draw()
}

export async function deleteDistribucionRow({ id, row }) {
  distribucionTable.row(row).remove().draw()
}

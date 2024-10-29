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

let gobernacionTable
export const validateGobernacionTable = async () => {
  gobernacionTable = new DataTable('#gobernacion-table', {
    columns: [
      // { data: 'sector_nombre' },

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

  loadGobernacionTable()
}

export const loadGobernacionTable = async () => {
  //   let partidas = await getFormPartidas()
  let partidas
  if (!Array.isArray(partidas)) return

  if (!partidas || partidas.error) return

  console.log(partidas)

  let datosOrdenados = [...partidas].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    let sector_codigo = `${el.sector_informacion.sector}.${el.sector_informacion.programa}.${el.sector_informacion.proyecto}`

    let descripcion =
      el.descripcion.length < 40
        ? el.descripcion
        : `${el.descripcion.slice(0, 40)} ...`

    return {
      // sector_nombre: el.sector_informacion.nombre,
      //   sector_cod: sector_codigo,
      //   partida: el.partida,
      //   descripcion: descripcion,
      //   monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      //   monto_actual: `${separarMiles(el.monto_actual)} Bs`,
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    }
  })

  gobernacionTable.clear().draw()

  // console.log(datosOrdenados)
  gobernacionTable.rows.add(data).draw()
}

export async function deleteDistribucionRow({ id, row }) {
  gobernacionTable.row(row).remove().draw()
}

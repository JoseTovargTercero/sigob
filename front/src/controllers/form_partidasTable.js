import { getFormPartidas } from '../api/partidas.js'

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
export const validatePartidasTable = async () => {
  partidasTable = new DataTable('#partidas-table', {
    columns: [
      { data: 'partida' },
      { data: 'nombre' },
      { data: 'acciones' },
      { data: 'descripcion' },
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

  loadPartidasTable()
}

export const loadPartidasTable = async () => {
  let partidas = await getFormPartidas()

  if (!Array.isArray(partidas.fullInfo)) return

  if (!partidas || partidas.error) return

  let datosOrdenados = [...partidas.fullInfo].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    return {
      partida: el.partida,
      nombre: el.nombre,
      descripcion: el.descripcion,
      acciones: `
      <button class="btn btn-info btn-sm" data-editarid="${el.id}">Editar</button>
      <button class="btn btn-danger btn-sm" data-eliminarid="${el.id}">Eliminar</button>
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

import {
  getAsignacionesEntes,
  getDistribucionEntes,
  getEntesPlan,
  getEntesPlanes,
} from '../api/form_entes.js'

let datos = [
  {
    id: 0,
    id_ente: 0,
    tipo_ente: 'D',
    ente_nombre: 'Ente 1',
    id_poa: 0,
    partidas: [
      { id: 1, monto: 5000 },
      { id: 2, monto: 10000 },
    ],
    monto: 15000,
  },
  {
    id: 1,
    id_ente: 1,
    tipo_ente: 'J',
    ente_nombre: 'Ente 2',
    partidas: [
      { id: 1, monto: 7000 },
      { id: 2, monto: 12000 },
    ],
    monto: 19000,
  },
  {
    id: 2,
    id_ente: 2,
    tipo_ente: 'D',
    ente_nombre: 'Ente 3',
    id_poa: 2,
    partidas: [
      { id: 1, monto: 6000 },
      { id: 2, monto: 15000 },
    ],
    monto: 21000,
  },
  {
    id: 3,
    id_ente: 3,
    tipo_ente: 'J',
    ente_nombre: 'Ente 4',
    id_poa: 3,
    partidas: [
      { id: 1, monto: 8000 },
      { id: 2, monto: 11000 },
    ],
    monto: 19000,
  },
  {
    id: 4,
    id_ente: 4,
    tipo_ente: 'D',
    ente_nombre: 'Ente 5',
    id_poa: 4,
    partidas: [
      { id: 1, monto: 9000 },
      { id: 2, monto: 8000 },
    ],
    monto: 17000,
  },
  {
    id: 5,
    id_ente: 5,
    tipo_ente: 'J',
    ente_nombre: 'Ente 6',
    id_poa: 5,
    partidas: [
      { id: 1, monto: 7000 },
      { id: 2, monto: 17000 },
    ],
    monto: 24000,
  },
  {
    id: 6,
    id_ente: 6,
    tipo_ente: 'D',
    ente_nombre: 'Ente 7',
    id_poa: 6,
    partidas: [
      { id: 1, monto: 10000 },
      { id: 2, monto: 12000 },
    ],
    monto: 22000,
  },
  {
    id: 7,
    id_ente: 7,
    tipo_ente: 'J',
    ente_nombre: 'Ente 8',
    id_poa: 7,
    partidas: [
      { id: 1, monto: 9500 },
      { id: 2, monto: 10500 },
    ],
    monto: 20000,
  },
  {
    id: 8,
    id_ente: 8,
    tipo_ente: 'D',
    ente_nombre: 'Ente 9',
    id_poa: 8,
    partidas: [
      { id: 1, monto: 8500 },
      { id: 2, monto: 13500 },
    ],
    monto: 22000,
  },
  {
    id: 9,
    id_ente: 9,
    tipo_ente: 'J',
    ente_nombre: 'Ente 10',
    id_poa: 9,
    partidas: [
      { id: 1, monto: 9200 },
      { id: 2, monto: 9800 },
    ],
    monto: 19000,
  },
]

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

let asignacionEntesTable
export const validateAsignacionEntesTable = async (id_ejercicio) => {
  asignacionEntesTable = new DataTable('#asignacion-entes-table', {
    columns: [
      { data: 'ente_nombre' },
      { data: 'monto' },
      { data: 'fecha' },
      { data: 'acciones' },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de asignación presupuestaria a entes</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadAsignacionEntesTable(id_ejercicio)
}

export const loadAsignacionEntesTable = async (id_ejercicio) => {
  // let planes = await getEntesPlanes()
  let asignaciones = await getAsignacionesEntes()

  if (!Array.isArray(asignaciones.fullInfo)) return

  if (!asignaciones || asignaciones.error) return

  let datosOrdenados = [...asignaciones.fullInfo].sort((a, b) => a.id - b.id)

  let data = datosOrdenados
    .filter((el) => Number(el.id_ejercicio) === Number(id_ejercicio))
    .map((el) => {
      return {
        ente_nombre: el.ente_nombre,
        monto: el.monto_total,
        tipo: el.tipo_ente,
        fecha: el.fecha,
        acciones:
          Number(el.status) === 0
            ? `<button class="btn btn-primary btn-sm" data-validarId="${el.id}">VALIDAR</button>`
            : `<button class="btn btn-secondary btn-sm" data-validarId="${el.id}">DETALLES</button>`,
      }
    })
  console.log(data)

  asignacionEntesTable.clear().draw()

  // console.log(datosOrdenados)
  asignacionEntesTable.rows.add(data).draw()
}

let distribucionEntesTable2
export const validateAsignacionEntesTable2 = async () => {
  distribucionEntesTable = new DataTable('#distribucion-entes-table', {
    columns: [{ data: 'ente_nombre' }, { data: 'monto' }, { data: 'acciones' }],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center">Historial de asignaciones a entes</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadAsignacionEntesTable()
}

export const loadAsignacionEntesTable2 = async () => {
  // let planes = await getEntesPlanes()
  let distribuciones = await getDistribucionEntes()

  if (!Array.isArray(distribuciones.fullInfo)) return

  if (!distribuciones || distribuciones.error) return

  let datosOrdenados = [...distribuciones.fullInfo].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    return {
      ente_nombre: `Distribucion para ente "${el.ente_nombre}"`,
      monto: el.monto_total,
      tipo: el.tipo_ente,
      acciones:
        Number(el.status) === 0
          ? `<button class="btn btn-info btn-sm" data-validarId="${el.id}">VALIDAR</button>`
          : Number(el.status) === 1
          ? ` <span class='btn btn-success btn-sm'>
            Aceptado
          </span>`
          : ` <span class='btn btn-danger btn-sm'>
            Rechazado
          </span>`,
    }
  })

  distribucionEntesTable.clear().draw()

  // console.log(datosOrdenados)
  distribucionEntesTable.rows.add(data).draw()
}

export async function deletePartidaRow({ id, row }) {
  distribucionEntesTable.row(row).remove().draw()
}

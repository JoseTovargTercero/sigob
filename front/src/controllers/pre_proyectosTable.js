import { getTraspasos } from '../api/pre_traspasos.js'
import { separadorLocal, tableLanguage } from '../helpers/helpers.js'
import { meses } from '../helpers/types.js'

const d = document
const w = window

let solicitudesDozavosTable
export async function validateProyectosTable() {
  solicitudesDozavosTable = new DataTable('#proyecto-table', {
    scrollY: 300,
    language: tableLanguage,
    columns: [
      { data: 'tipo' },
      { data: 'numero_orden' },

      { data: 'monto' },
      { data: 'fecha' },
      { data: 'status' },
      { data: 'acciones' },
    ],
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

  // loadProyectosTable(id_ejercicio)
}

export async function loadProyectosTable(id_ejercicio) {
  let dataFetch = await getTraspasos(id_ejercicio)
  // console.log(dataFetch)

  // console.log(id_ejercicio)

  if (!Array.isArray(dataFetch)) return

  if (!dataFetch || dataFetch.error) return

  let datosOrdenados = [...dataFetch].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((datp) => {
    return {
      tipo: Number(dato.tipo) === 1 ? 'Traslado' : 'Traspaso',
      numero_orden: dato.n_orden,

      monto: separadorLocal(dato.monto_total),
      fecha: dato.fecha,
      status:
        Number(dato.status) === 0
          ? `<span class="btn btn-warning btn-sm">Pendiente</span>`
          : Number(dato.status) === 1
          ? `<span class='btn btn-success btn-sm'>Aceptado</span>`
          : `<span class="btn btn-danger btn-sm">Rechazado</span>`,
      acciones:
        Number(dato.status) === 0
          ? `<button
              class='btn btn-secondary btn-sm btn-view'
              data-detalleid='${dato.id}'
            >
              <i class='bx bx-detail me-1'></i>Validar
            </button>`
          : ` <button
              class='btn btn-info btn-sm btn-view'
              data-detalleid='${dato.id}'
            >
              <i class='bx bx-detail me-1'></i>Detalles
            </button>`,
    }
  })

  solicitudesDozavosTable.clear().draw()

  // console.log(datosOrdenados)
  solicitudesDozavosTable.rows.add(data).draw()
}

export async function deleteTraspasoRow({ id, row }) {
  solicitudesDozavosTable.row(row).remove().draw()
}

import { getTraspasos } from '../api/pre_traspasos.js'
import { separadorLocal, tableLanguage } from '../helpers/helpers.js'
import { meses } from '../helpers/types.js'

const d = document
const w = window

let solicitudesDozavosTable

export async function validateTraspasosTable({ id_ejercicio }) {
  solicitudesDozavosTable = new DataTable('#traspaso-table', {
    scrollY: 300,
    language: tableLanguage,
    columns: [
      { data: 'tipo' },
      {
        data: 'solicitante',
        render: function (data) {
          return `<div class="text-wrap">
              ${data}
            </div>`
        },
      },
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

  loadTraspasosTable(id_ejercicio)
}

export async function loadTraspasosTable(id_ejercicio) {
  let solicitudes = await getTraspasos(id_ejercicio)
  // console.log(solicitudes)

  // console.log(id_ejercicio)

  if (!Array.isArray(solicitudes)) return

  if (!solicitudes || solicitudes.error) return

  let datosOrdenados = [...solicitudes].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((solicitud) => {
    return {
      tipo: Number(solicitud.tipo) === 1 ? 'Traslado' : 'Traspaso',
      numero_orden: solicitud.n_orden ? solicitud.n_orden : 'Pendiente',
      solicitante: solicitud.ente ? solicitud.ente : 'Control Presupuestario',
      monto: separadorLocal(solicitud.monto_total),
      fecha: solicitud.fecha,
      status:
        Number(solicitud.status) === 0
          ? `<span class="btn btn-warning btn-sm">Pendiente</span>`
          : Number(solicitud.status) === 1
          ? `<span class='btn btn-success btn-sm'>Aceptado</span>`
          : `<span class="btn btn-danger btn-sm">Rechazado</span>`,
      acciones:
        Number(solicitud.status) === 0
          ? `<button
              class='btn btn-secondary btn-sm btn-view'
              data-detalleid='${solicitud.id}'
            >
              <i class='bx bx-detail me-1'></i>Validar
            </button>`
          : ` <button
              class='btn btn-info btn-sm btn-view'
              data-detalleid='${solicitud.id}'
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

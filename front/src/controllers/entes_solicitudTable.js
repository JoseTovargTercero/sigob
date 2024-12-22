import { getSolicitudesDozavos } from '../api/pre_solicitudesDozavos.js'

import { separarMiles, tableLanguage } from '../helpers/helpers.js'

const d = document
const w = window

let solicitudEntesTable

export async function validateSolicitudEntesTable(id_ejercicio) {
  solicitudEntesTable = new DataTable('#solicitud-entes-table', {
    columns: [
      { data: 'numero_orden' },
      { data: 'numero_compromiso' },
      { data: 'mes' },
      { data: 'tipo' },
      { data: 'monto' },
      { data: 'fecha' },
      { data: 'acciones' },
    ],
    responsive: true,
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = ``
        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  // loadSolicitudEntesTable(id_ejercicio)
}

export async function loadSolicitudEntesTable({ id_ejercicio }) {
  let solicitudes = await getSolicitudesDozavos()

  if (!Array.isArray(solicitudes)) return

  if (!solicitudes || solicitudes.error) return

  let datosOrdenados = [...solicitudes].sort((a, b) => a.id - b.id)
  let data = datosOrdenados
    .filter(
      (solicitud) =>
        Number(solicitud.status) !== 3 &&
        Number(id_ejercicio) === Number(solicitud.id_ejercicio)
    )
    .map((solicitud) => {
      return {
        numero_orden: solicitud.numero_orden,
        numero_compromiso: !solicitud.numero_compromiso
          ? 'No registrado'
          : solicitud.numero_compromiso,
        mes: meses[solicitud.mes],
        tipo: solicitud.tipo === 'D' ? 'Disminuye' : 'Aumenta',
        monto: separadorLocal(solicitud.monto),
        fecha: solicitud.fecha,
        acciones:
          Number(solicitud.status) === 0
            ? `<button
            class='btn btn-info btn-sm btn-view'
            data-detalleid='${solicitud.id}'
          >
            <i class='bx bx-detail me-1'></i>Detalles
          </button>`
            : ` <button
            class='btn btn-secondary btn-sm btn-view'
            data-detalleid='${solicitud.id}'
          >
            <i class='bx bx-detail me-1'></i>Validar
          </button>`,
      }
    })

  solicitudEntesTable.clear().draw()
  solicitudEntesTable.rows.add(data).draw()
}

export async function deleteSolicitudEnte({ id, row }) {
  solicitudEntesTable.row(row).remove().draw()
}

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
      numero_orden: solicitud.numero_orden,
      entes: solicitud.ente_nombre,

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

  solicitudesDozavosTable.clear().draw()

  // console.log(datosOrdenados)
  solicitudesDozavosTable.rows.add(data).draw()
}

export async function deleteTraspasoRow({ id, row }) {
  solicitudesDozavosTable.row(row).remove().draw()
}

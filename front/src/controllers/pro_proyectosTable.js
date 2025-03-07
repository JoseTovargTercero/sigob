import { getProyectos } from '../api/pro_proyectos.js'

import { separadorLocal, tableLanguage } from '../helpers/helpers.js'

const d = document
const w = window

let proyectoTable
export async function validateProyectosTable() {
  proyectoTable = new DataTable('#pro-proyecto-table', {
    scrollY: 300,
    language: tableLanguage,
    columns: [
      { data: 'tipo_credito' },
      { data: 'tipo_proyecto' },
      { data: 'fecha' },
      { data: 'monto' },
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
  let dataFetch = await getProyectos(id_ejercicio)
  console.log(dataFetch)

  if (!Array.isArray(dataFetch)) return

  if (!dataFetch || dataFetch.error) return

  let datosOrdenados = [...dataFetch].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((dato) => {
    return {
      tipo_credito: Number(dato.tipo_credito) === 0 ? 'FCI' : 'Venezuela Bella',
      tipo_proyecto:
        Number(dato.tipo_proyecto) === 0 ? 'Transferencia' : 'Compra',
      fecha: dato.fecha,
      monto: `${separadorLocal(dato.monto)} Bs`,
      status:
        Number(dato.status) === 0
          ? `<span class="btn btn-warning btn-sm">Sin decreto</span>`
          : Number(dato.status) === 1
          ? `<span class='btn btn-success btn-sm'>Aceptado</span>`
          : `<span class="btn btn-danger btn-sm">Rechazado</span>`,
      acciones:
        Number(dato.status) === 0
          ? `<button
              class='btn btn-secondary btn-sm btn-view'
              data-detalleid='${dato.id_credito}'
            >
              <i class='bx bx-detail me-1'></i>Validar
            </button>`
          : ` <button
              class='btn btn-info btn-sm btn-view'
              data-detalleid='${dato.id_credito}'
            >
              <i class='bx bx-detail me-1'></i>Detalles
            </button>`,
    }
  })

  proyectoTable.clear().draw()

  // console.log(datosOrdenados)
  proyectoTable.rows.add(data).draw()
}

export async function deleteTraspasoRow({ id, row }) {
  proyectoTable.row(row).remove().draw()
}

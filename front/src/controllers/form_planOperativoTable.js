import { getEntePlanOperativos } from '../api/form_planOperativo.js'

import { separadorLocal, tableLanguage } from '../helpers/helpers.js'
import { meses } from '../helpers/types.js'

const d = document
const w = window

let planesOperativosTable

export async function validatePlanOperativoTable(id_ejercicio) {
  planesOperativosTable = new DataTable('#plan-operativo-table', {
    columns: [
      { data: 'ente' },

      { data: 'objetivo_general' },
      // { data: 'fecha' },
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

  // loadPlanOperativo(id_ejercicio)
}

export async function loadPlanOperativo({ id_ejercicio }) {
  let planes = await getEntePlanOperativos(id_ejercicio)
  console.log(planes)

  if (!Array.isArray(planes)) return

  if (!planes || planes.error) return

  let datosOrdenados = [...planes].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((plan) => {
    return {
      ente: plan.ente.ente_nombre,
      codigo: plan.codigo,
      objetivo_general: plan.objetivo_general,

      acciones: `<button
            class='btn btn-info btn-sm btn-view'
            data-detalleid='${plan.id}' data-enteid='${plan.id_ente}'
          >
            <i class='bx bx-detail me-1'></i>Detalles
          </button>`,
    }
  })

  planesOperativosTable.clear().draw()
  planesOperativosTable.rows.add(data).draw()
}

export async function deleteSolicitudEnte({ id, row }) {
  planesOperativosTable.row(row).remove().draw()
}

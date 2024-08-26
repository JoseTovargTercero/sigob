import { getPeticionMovimientos } from '../api/movimientos.js'
import {
  descargarNominaTxt,
  getPeticionesNomina,
  getPeticionNomina,
} from '../api/peticionesNomina.js'
import { nomCorregirCard } from '../components/nom_corregir_card.js'
import { nomReportCard } from '../components/nom_report_card.js'
import { validarIdentificador } from './peticionesNominaForm.js'

const d = document
const w = window

let requestInfo

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

let requestTable = new DataTable('#request-table-historial', {
  columns: [
    { data: 'correlativo' },
    { data: 'nombre' },
    { data: 'identificador' },
    { data: 'fecha' },
    { data: 'status' },
    { data: 'acciones' },
  ],
  responsive: true,
  scrollY: 350,
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

export async function loadRequestTableHistorico() {
  let peticiones = await getPeticionesNomina()
  let datosOrdenados = [...peticiones].sort(
    (a, b) => a.correlativo - b.correlativo
  )

  console.log(peticiones)
  let data = datosOrdenados
    .filter(
      (peticion) =>
        Number(peticion.status) === 1 && Number(peticion.status_archivos) === 1
    )
    .map((peticion) => {
      return {
        correlativo: peticion.correlativo,
        nombre: peticion.nombre_nomina,
        identificador: validarIdentificador(peticion.identificador),
        fecha: peticion.creacion,
        status: `<span class="btn btn-success btn-sm">Revisado</span>`,
        acciones: `
      <button class="btn btn-primary btn-sm" data-id="${
        peticion.correlativo
      }" ${Number(peticion.status) === 0 ? 'disabled' : ''} data-show="${
          peticion.id
        }">Informacion</button>
     `,
      }
    })

  requestTable.clear().draw()

  // console.log(datosOrdenados)
  requestTable.rows.add(data).draw()
}

d.addEventListener('click', async (e) => {
  if (!d.getElementById('request-historial')) return
  console.log('hola')
  if (e.target.dataset.show) {
    e.preventDefault()
    let peticion = await getPeticionNomina(e.target.dataset.show)
    // console.log(peticion)

    let reportCard = d.getElementById('modal-report')
    if (reportCard) reportCard.remove()

    nomReportCard({ data: peticion, elementToInsert: 'request-historial' })
  }

  if (e.target.dataset.close === 'btn-close-report') {
    let reportCard = d.getElementById('modal-report')
    reportCard.remove()
  }
  if (e.target.dataset.show) {
    e.preventDefault()
    let peticion = await getPeticionNomina(e.target.dataset.show)

    console.log(peticion)
    let reportCard = d.getElementById('modal-report')
    if (reportCard) reportCard.remove()

    nomReportCard({ data: peticion, elementToInsert: 'request-historial' })
  }

  if (e.target.dataset.correlativotxt) {
    descargarNominaTxt({
      identificador: e.target.dataset.identificador,
      correlativo: e.target.dataset.correlativotxt,
    }).then((res) => {
      d.getElementById('modal-report').remove()
    })
  }
})

// `
// <button class="btn btn-primary btn-sm" data-correlativo="${
//   peticion.correlativo
// }" ${
//   Number(peticion.status) === 0 ? 'disabled' : ''
// } id="btn-show-request">${
//   Number(peticion.status) === 0
//     ? `<i class='bx bx-low-vision' data-correlativo="${peticion.correlativo}"></i>`
//     : `<i class='bx bxs-show' id="btn-show-request"></i>`
// }</button>
// `

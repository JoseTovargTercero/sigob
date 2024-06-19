import { getPeticionesNomina } from '../api/peticionesNomina.js'

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

let requestTable = new DataTable('#request-nom-table', {
  columns: [
    { data: 'correlativo' },
    { data: 'nombre' },
    { data: 'status' },
    { data: 'fecha' },
    { data: 'acciones' },
  ],
  responsive: true,
  scrollY: 150,
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

export async function loadRequestTable() {
  let peticiones = await getPeticionesNomina()
  let datosOrdenados = [...peticiones].sort(
    (a, b) => a.correlativo - b.correlativo
  )

  let data = datosOrdenados.map((peticion) => {
    console.log(peticion)
    return {
      correlativo: peticion.correlativo,
      nombre: peticion.nombre_nomina,
      status: peticion.status == 1 ? 'Revisado' : 'Pendiente',
      fecha: peticion.creacion,
      acciones: `
      <button class="btn btn-primary btn-sm" data-correlativo="${
        peticion.correlativo
      }" ${
        Number(peticion.status) === 0 ? 'disabled' : ''
      } id="btn-show-request">Informacion</button>
     `,
    }
  })

  requestTable.clear().draw()

  // console.log(datosOrdenados)
  requestTable.rows.add(data).draw()
}

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

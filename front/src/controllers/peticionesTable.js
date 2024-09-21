import { getPeticionesNomina } from '../api/peticionesNomina.js'
import { validarIdentificador } from './peticionesNominaForm.js'

const d = document
const w = window

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

let requestTableConfirmado = new DataTable('#request-nom-table-confirmado', {
  columnsDef: [
    { width: '20%', targets: 0 }, // Establece el ancho de la primera columna al 20%
    { width: '30%', targets: 1 }, // Establece el ancho de la segunda columna al 30%
  ],
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

let requestTableRevision = new DataTable('#request-nom-table-revision', {
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

export async function loadRequestTable() {
  let peticiones = await getPeticionesNomina()

  if (!peticiones || peticiones.error) return

  let datosOrdenados = [...peticiones].sort(
    (a, b) => a.correlativo - b.correlativo
  )

  let dataObj = {
    revision: [],
    confirmado: [],
  }

  datosOrdenados.forEach((peticion) => {
    if (
      Number(peticion.status) === 1 &&
      Number(peticion.status_archivos) === 0
    ) {
      dataObj.confirmado.push({
        correlativo: peticion.correlativo,
        nombre: peticion.nombre_nomina,
        status: `<span class="btn btn-success btn-sm">Revisado sin descargar</span>`,
        identificador: validarIdentificador(peticion.identificador),
        fecha: peticion.creacion,
        acciones: `
          <button class="btn btn-primary btn-sm" data-correlativo="${
            peticion.correlativo
          }" ${
          Number(peticion.status) === 0 ? 'disabled' : ''
        } id="btn-show-request">Informacion</button>
         `,
      })
    }
    if (Number(peticion.status) === 2) {
      dataObj.revision.push({
        correlativo: peticion.correlativo,
        nombre: peticion.nombre_nomina,
        status: `<span class="btn btn-danger btn-sm">Rechazada</span>`,
        identificador: validarIdentificador(peticion.identificador),
        fecha: peticion.creacion,
        acciones: `
        <button class="btn btn-secondary btn-sm" data-revisar="${
          peticion.id
        }" ${Number(peticion.status) === 0 ? 'disabled' : ''}>Revisar</button>
       `,
      })
    }

    if (Number(peticion.status) === 0) {
      dataObj.revision.push({
        correlativo: peticion.correlativo,
        nombre: peticion.nombre_nomina,
        status: `<span class="btn btn-warning btn-sm">Pendiente</span>`,
        identificador: validarIdentificador(peticion.identificador),
        fecha: peticion.creacion,
        acciones: `
        <button class="btn btn-primary btn-sm" data-correlativo="${
          peticion.correlativo
        }" ${
          Number(peticion.status) === 0 ? 'disabled' : ''
        }>Informacion</button>
       `,
      })
    }
  })

  console.log(peticiones)
  console.log(dataObj)
  // let data = datosOrdenados.map((peticion) => {
  //   return {
  //     correlativo: peticion.correlativo,
  //     nombre: peticion.nombre_nomina,
  //     status: Number(peticion.status) === 1 ? 'Revisado' : 'Pendiente',
  //     identificador: validarIdentificador(peticion.identificador),
  //     fecha: peticion.creacion,
  //     acciones: `
  //     <button class="btn btn-primary btn-sm" data-correlativo="${
  //       peticion.correlativo
  //     }" ${
  //       Number(peticion.status) === 0 ? 'disabled' : ''
  //     } id="btn-show-request">Informacion</button>
  //    `,
  //   }
  // })

  requestTableConfirmado.clear().draw()
  requestTableRevision.clear().draw()

  requestTableConfirmado.rows.add(dataObj.confirmado).draw()
  requestTableRevision.rows.add(dataObj.revision).draw()

  // console.log(datosOrdenados)
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

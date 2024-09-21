import {
  getPeticionesNomina,
  getRegConPeticionesNomina,
} from '../api/peticionesNomina.js'
import { validarIdentificador } from '../helpers/helpers.js'

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

let regconRequestTable = new DataTable('#regcon-request-table', {
  columns: [
    { data: 'correlativo' },
    { data: 'nombre' },
    { data: 'identificador' },
    { data: 'fecha' },
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

export async function loadRegconRequestTable() {
  let peticiones = await getRegConPeticionesNomina()

  if (!Array.isArray(peticiones)) return

  let datosOrdenados = [...peticiones].sort(
    (a, b) => a.correlativo - b.correlativo
  )

  console.log(peticiones, datosOrdenados)

  let data = []
  datosOrdenados.forEach((peticion) => {
    if (Number(peticion.status) === 0) {
      data.push({
        correlativo: peticion.correlativo,
        nombre: peticion.nombre_nomina,
        identificador: validarIdentificador(peticion.identificador),
        fecha: peticion.creacion,
        acciones: ` <button
            class='btn btn-primary btn-sm'
            data-peticion-id='${peticion.id}'
            data-correlativo='${peticion.correlativo}'
            data-nombre='${peticion.nombre_nomina}'
          >CONSULTAR</button>`,
      })
    }
  })

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

  console.log(data)

  regconRequestTable.clear().draw()

  regconRequestTable.rows.add(data).draw()

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

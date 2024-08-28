import { separarMiles } from '../helpers/helpers.js'

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

export function employeePayTableHTML({ nominaData, columns, elementToInsert }) {
  requestInfo = nominaData

  let { informacion_empleados, nombre_nomina } = nominaData

  let cantidad_emplados = separarMiles(informacion_empleados.length)
  let total_a_pagar = separarMiles(
    informacion_empleados
      .reduce((acc, el) => Number(el.total_a_pagar) + acc, 0)
      .toFixed(2)
  )

  let rowsTr = informacion_empleados
    .map((row) => {
      let td = ''

      Object.values(row).map((el) => {
        td += `<td>${el}</td>`
      })

      return `<tr>${td}</tr>`
    })
    .join('')

  let columnsTh = columns
    .map((el) => {
      return `<th>${el}</th>`
    })
    .join('')

  let card = d.getElementById('request-employee-table-card')
  if (card) card.remove()

  let table = ` <div class='card rounded' id='request-employee-table-card'>
      <div class='card-header'>
        
          <h5 class='mb-2'>Lista de empleados de la nomina "${nombre_nomina}"</h5>
          <p class='m-0'>
            <strong>Empleados en nómina:</strong> ${cantidad_emplados} 
            empleado/s
          </p>
          <p class='m-0'>
            <strong>Total a pagar:</strong> ${total_a_pagar} Bs
          </p>
        
      </div>
      <div class='card-body'>
        <table
          id='request-employee-table'
          class='table table-striped'
          style='width:100%'
        >
          <thead>${columnsTh}</thead>
          <tbody>${rowsTr}</tbody>
        </table>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterend', table)

  createTable({ nominaData, columns })
}

export function createTable({ nominaData, columns }) {
  let { informacion_empleados, nombre_nomina } = nominaData

  // let datosOrdenados = [...informacion_empleados].sort((a, b) => a.id - b.id)

  let columnTable = columns.map((el) => {
    return { data: el }
  })

  let employeePayTable = new DataTable('#request-employee-table', {
    columns: columnTable,
    scrollY: 350,
    responsive: true,
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
}

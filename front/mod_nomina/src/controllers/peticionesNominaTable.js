import { sendCalculoNomina } from '../api/peticionesNomina.js'
import { confirmNotification, validateModal } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

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

export async function createTable({ nominaData }) {
  let { informacion_empleados, nombre_nomina } = nominaData

  let datosOrdenados = [...informacion_empleados].sort((a, b) => a.id - b.id)

  let columnsText = Object.keys(datosOrdenados[0]).map((el) => {
    return { data: el }
  })

  let employeePayTable = new DataTable('#employee-pay-table', {
    columns: columnsText,
    responsive: true,
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `<button id="employee-pay-request" class="btn btn-info">REALIZAR PETICIÓN</button>
        `

        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  employeePayTable.clear().draw()

  // console.log(datosOrdenados)
  employeePayTable.rows.add(datosOrdenados).draw()
}

export function employeePayTableHTML({ nominaData }) {
  requestInfo = nominaData

  let { informacion_empleados, nombre_nomina } = nominaData

  let columnsText = Object.keys(informacion_empleados[0])

  let cantidad_emplados = informacion_empleados.length
  let total_a_pagar = informacion_empleados.reduce(
    (acc, el) => Number(el.total_a_pagar) + acc,
    0
  )

  let columns = columnsText
    .map((el) => {
      return `<th>${el}</th>`
    })
    .join('')

  let table = `<div class='card' id='employee-pay-table-card'>
      <div class='card-header'>
        <div class=''>
          <div>
            <h5 class='mb-0'>Nómina ${nombre_nomina}</h5>
          
            <ul class='d-block'>
              <li><strong>Empleados en nómina:</strong> ${cantidad_emplados} empleado/s</li>
              <li><strong>Total a pagar:</strong> ${total_a_pagar} Bs</li>
            </ul>

            <small class='d-block text-center'>
            Utilice la barra horizontal para observar la información de la
            nómina
          </small>
          </div>
        </div>
      </div>
      <div class='card-body'>
        <table
          id='employee-pay-table'
          class='table table-striped'
          style='width:100%'
        >
          <thead>${columns}</thead>
          <tbody></tbody>
        </table>
      </div>
    </div>`

  return table
}

d.addEventListener('click', (e) => {
  if (e.target.id === 'employee-pay-request') {
    e.preventDefault()
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: 'Deseas realizar esta petición?',
      successFunction: sendCalculoNomina,
      successFunctionParams: requestInfo,
    })
  }
})

// .map((empleados) => {
//   let {
//     id,
//     nombres,
//     nacionalidad,
//     cedula,
//     banco,
//     tipo_banco,
//     cod_cargo,
//     otros_años,
//     discapacidades,
//     hijos,
//     instruccion_academica,
//     id_dependencia,
//     fecha_ingreso,
//     tipo_nomina,
//     salario_base,
//     salario_integral,
//     CONTRIBUCION_POR_DISCAPACIDAD,
//     PRIMA_POR_HIJO_EMPLEADOS,
//     PRIMA_POR_TRANSPORTE,
//     PRIMA_POR_ANTIGUEDAD_EMPLEADOS,
//     PRIMA_POR_ESCALAFON,
//     PRIMA_POR_FRONTERA,
//     PRIMA_POR_PROFESIONALES,
//     S_S_O,
//     RPE,
//     A_P_S_S_O,
//     A_P_RPE,
//     PAGO_DE_BECA,
//     PRIMA_P_DED_AL_S_PUBLICO_UNICO_DE_SALUD,
//     total_a_pagar,
//   } = empleados
//   return {
//     id,
//     nombres,
//     nacionalidad,
//     cedula,
//     banco,
//     tipo_banco,
//     cod_cargo,
//     otros_años,
//     discapacidades,
//     hijos,
//     instruccion_academica,
//     id_dependencia,
//     fecha_ingreso,
//     tipo_nomina,
//     salario_base,
//     salario_integral,
//     CONTRIBUCION_POR_DISCAPACIDAD,
//     PRIMA_POR_HIJO_EMPLEADOS,
//     PRIMA_POR_TRANSPORTE,
//     PRIMA_POR_ANTIGUEDAD_EMPLEADOS,
//     PRIMA_POR_ESCALAFON,
//     PRIMA_POR_FRONTERA,
//     PRIMA_POR_PROFESIONALES,
//     S_S_O,
//     RPE,
//     A_P_S_S_O,
//     A_P_RPE,
//     PAGO_DE_BECA,
//     PRIMA_P_DED_AL_S_PUBLICO_UNICO_DE_SALUD,
//     total_a_pagar,
//   }
// })

import { validarIdentificador } from '../controllers/peticionesNominaForm.js'
import { separarMiles } from '../helpers/helpers.js'

const d = document
const w = window

export function createComparationContainer({ data, elementToInsert }) {
  if (!data) return
  let { registro_actual, registro_anterior } = data

  let cardElement = d.getElementById('request-comparation-container')
  if (cardElement) cardElement.remove()

  let card = `<div class='' id='request-comparation-container'>
  ${createCard({
    actual: registro_actual,
    anterior: registro_anterior,
  })}
</div>
  
`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)
}

const createCard = ({ actual, anterior }) => {
  let correlativoActual = actual.correlativo || 'Sin correlativo'
  let nombreNominaActual = actual.nombre_nomina
  let estadoActual = actual.status
  let totalEmpleadosActual = actual.empleados.length
  let totalPagarActual = actual.total_pagar
    .reduce((acc, el) => el + acc, 0)
    .toFixed(2)

  console.log(actual.identificador)
  let identificadorActual = validarIdentificador(actual.identificador)

  let correlativoAnterior,
    nombreNominaAnterior,
    estadoAnterior,
    totalEmpleadosAnterior,
    totalPagarAnterior,
    diferenciaEmpleados,
    identificadorAnterior

  if (anterior) {
    correlativoAnterior = anterior.correlativo
    nombreNominaAnterior = anterior.nombre_nomina
    estadoAnterior = anterior.status
    totalEmpleadosAnterior = anterior.empleados.length
    totalPagarAnterior = anterior.total_pagar
      .reduce((acc, el) => el + acc, 0)
      .toFixed(2)

    identificadorAnterior = validarIdentificador(anterior.identificador)
  }

  let listaAsignaciones = createObjectList(
    anterior.asignaciones,
    actual.asignaciones,
    'Asignaciones'
  )

  let listaDeducciones = createObjectList(
    anterior.deducciones,
    actual.deducciones,
    'Deducciones'
  )

  let listaAportes = createObjectList(
    anterior.aportes,
    actual.aportes,
    'Aportes'
  )

  let listaEmpleados = createObjectList(
    anterior
      ? {
          'CANTIDAD EMPLEADOS': anterior.empleados.length,
        }
      : false,
    { 'CANTIDAD EMPLEADOS': actual.empleados.length },
    'Empleados'
  )

  return `
    <div class='card slide-up-animation'>
      <div class='card-header row p-0'>
     ${
       anterior
         ? `<div class="col">
        <h5 class='card-title text-center m-2'>
          <b>Nómina Anterior:</b>
        </h5>
        <h5 class='card-title text-center m-2'>
         ${correlativoAnterior} - ${nombreNominaAnterior}
        </h5>
        <h6 class='card-subtitle text-center mb-2'>
          <b>${identificadorAnterior}</b>
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Cantidad de empleados: </b>${separarMiles(totalEmpleadosAnterior)}
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Total a pagar: </b>${separarMiles(totalPagarAnterior)} bs
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Estatus: </b>${estadoAnterior == 0 ? 'En revisión' : 'Revisado'}
        </h6>
      </div>`
         : ''
     }

      <div class="col">
      <h5 class='card-title text-center m-2'>
        <b>Nómina consultada:</b>
      </h5>
      <h5 class='card-title text-center m-2'>
        ${correlativoActual} - ${nombreNominaActual}
      </h5>
       <h6 class='card-subtitle text-center mb-2'>
          <b>${identificadorActual}</b>
        </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Cantidad de empleados: </b>${separarMiles(totalEmpleadosActual)}
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Total a pagar: </b>${separarMiles(totalPagarActual)}bs
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Estatus: </b>${estadoActual == 0 ? 'En revisión' : 'Revisado'}
      </h6>
    </div>
      </div>

      <div class='row gap-2 mx-0 request-list-container'>
      
      
        ${
          listaAsignaciones
            ? `
          <div class="col-sm">
          ${listaAsignaciones}
      </div>`
            : ''
        }

        <div class="col-sm d-flex flex-column">
        ${listaDeducciones ? listaDeducciones : ''}
        ${listaAportes ? listaAportes : ''}
        ${listaEmpleados ? listaEmpleados : ''}
        </div>
        

      
      </div>
      
    `
}

// for (const keys in obj) {
//   li += `<li class="list-group-item p-1"><b>${keys[0]}${keys
//     .slice(1, keys.length - 1)
//     .toLocaleLowerCase()}:</b> ${obj[keys]} Bs.</li>`
// }

// return `<ul class="list-group list-group-flush mb-4">${li}</ul>`
const createObjectList = (anterior, actual, title) => {
  if (actual.length === 0) return
  let tr = ''
  let totalListActual = 0
  let totalListAnterior = 0

  let cantidadPropiedades = Object.values(actual).length

  const celdaDiferencia = (diferencia) => {
    let diferenciaNumber = separarMiles(Number(diferencia).toFixed(2))

    if (diferencia > 0)
      return `<td class="table-success">+${diferenciaNumber}</td>`
    if (diferencia < 0)
      return `<td class="table-blue-gray">${diferenciaNumber}</td>`
    return `<td class="table-info">${diferenciaNumber}</td>`
  }

  // lista de petición consultada

  for (const key in actual) {
    let diferencia = anterior ? actual[key] - anterior[key] : ''
    totalListActual += actual[key]
    if (anterior) totalListAnterior += anterior[key]
    tr += `
      <tr>
        <td>${key.toLocaleLowerCase()}</td>
         ${
           anterior
             ? `<td class="table-secondary">${separarMiles(anterior[key])}</td>`
             : ''
         }
        <td class="table-secondary">${separarMiles(actual[key].toFixed(2))}</td>
         ${anterior ? celdaDiferencia(diferencia) : ''}
      </tr>`
  }

  let totalDiferencia = totalListActual - totalListAnterior

  // lista de petición anterior

  if (cantidadPropiedades >= 1) {
    tr += `<tr class="p-0 table-primary">
    <td>TOTAL</td>
    ${
      totalListAnterior
        ? `<td class="table-secondary">${separarMiles(
            totalListAnterior.toFixed(2)
          )}</td>`
        : ''
    }
    <td class='table-secondary'>${separarMiles(
      totalListActual.toFixed(2)
    )}</td>${totalListAnterior ? celdaDiferencia(totalDiferencia) : ''}
  </tr>`
  }

  return `
    <table class="table table-xs" style='width: 100%'>
          <thead>
        <th class="table-warning"><i>${title}</i></th>
        ${anterior ? `<th class="">Anterior</th>` : ''}
        <th class="">Actual</th>
        ${anterior ? `<th class="">Diferencia</th>` : ''}
      </thead>
      <tbody>${tr}</tbody>
    </table>`
}

// return `<div
// class='request-comparation-container'
// id='request-comparation-container'
// >${
// !registro_anterior
//   ? `  <div class='card p-2 slide-up-animation'>
// <div class='card-header'>
//   <h5 class='card-title text-center m-2'>
//     <b>Nómina anterior:</b>
//   </h5>
//   <h5 class='card-title text-center m-2'>
//     No existe un registro anterior de nómina
//   </h5>
// </div>
// </div>`
//   : createCard({
//       correlativo: correlativoAnterior,
//       nombreNomina: nombreNominaAnterior,
//       estado: estadoAnterior,
//       listaAsignaciones: listaAsignacionesAnterior,
//       listaDeducciones: listaDeduccionesAnterior,
//       totalEmpleados: totalEmpleadosAnterior,
//       totalPagar: totalPagarAnterior,
//     })
// }
// ${createCard({
// correlativo: correlativoActual,
// nombreNomina: nombreNominaActual,
// estado: estadoActual,
// listaAsignaciones: listaAsignacionesActual,
// listaDeducciones: listaDeduccionesActual,
// totalEmpleados: totalEmpleadosActual,
// totalPagar: totalPagarActual,
// confirmBtn: true,
// })}`
// }

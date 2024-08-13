const d = document
const w = window

export function createComparationContainer({ data, tablaDiferencia }) {
  if (!data) return
  let { registro_actual, registro_anterior, confirmBtn } = data

  return `
    <div class='' id='request-comparation-container'>
      ${createCard({
        actual: registro_actual,
        anterior: registro_anterior,
        confirmBtn,
      })}
    </div>
      <div class='card rounded row mx-0 justify-content-center'>
    

      <div class="row gap-2 mx-0 request-list-container">
        <div class='col mb-2'> 
          <div class='card-header py-2 pb-2'>
          <h5 class='card-title mb-0 text-center'>Empleados eliminados de nomina</h5>
           <small class='d-block mt-0 text-center text-muted'>
             Visualice los empleados eliminados con respecto a la nomina anterior
           </small>  
      </div>
          <table
            id='peticion-empleados-eliminados'
            class='table table-xs table-striped'
            style='width:100%;'
          >
            <thead class='w-100'>
              <th>NOMBRES</th>
              <th>CEDULA</th>
              <th>DEPENDENCIA</th>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class='col mb-2'>
   <div class='card-header py-2 pb-2'>
     
          <h5 class='card-title mb-0 text-center'>Empleados nuevos en nomina</h5>
         <small class='d-block mt-0 text-center text-muted'>
           Visualice los empleados nuevos con respecto a la nomina anterior
         </small>
      </div>
          <table
            id='peticion-empleados-nuevos'
            class='table table-xs table-striped'
            style='width:100%'
          >
            <thead class='w-100'>
              <th>NOMBRES</th>
              <th>CEDULA</th>
              <th>DEPENDENCIA</th>
            </thead>
            <tbody></tbody>
          </table>
        </div>  
      </div>
      </div>
       ${
         confirmBtn
           ? `<div class='d-flex justify-content-center gap-2 mb-2'>
         <button class='btn btn-danger' id="deny-request" data-correlativo="${registro_actual.correlativo}">RECHAZAR</button>
          <button class='btn btn-primary' id="confirm-request" data-correlativo="${registro_actual.correlativo}">CONFIRMAR</button>
          </div>`
           : ''
       }
  `
}

const createCard = ({ actual, anterior, confirmBtn }) => {
  let correlativoActual = actual.correlativo || 'Sin correlativo'
  let nombreNominaActual = actual.nombre_nomina
  let estadoActual = actual.status
  let totalEmpleadosActual = actual.empleados.length
  let totalPagarActual = actual.total_pagar
    .reduce((acc, el) => el + acc, 0)
    .toFixed(2)

  let correlativoAnterior,
    nombreNominaAnterior,
    estadoAnterior,
    totalEmpleadosAnterior,
    totalPagarAnterior,
    diferenciaEmpleados

  if (anterior) {
    correlativoAnterior = anterior.correlativo
    nombreNominaAnterior = anterior.nombre_nomina
    estadoAnterior = anterior.status
    totalEmpleadosAnterior = anterior.empleados.length
    totalPagarAnterior = anterior.total_pagar
      .reduce((acc, el) => el + acc, 0)
      .toFixed(2)
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
          <b>Cantidad de empleados: </b>${totalEmpleadosAnterior}
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Total a pagar: </b>${totalPagarAnterior}bs
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Estado: </b>${estadoAnterior == 0 ? 'En revisión' : 'Revisado'}
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
        <b>Cantidad de empleados: </b>${totalEmpleadosActual}
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Total a pagar: </b>${totalPagarActual}bs
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Estado: </b>${estadoActual == 0 ? 'En revisión' : 'Revisado'}
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
    if (diferencia > 0) return `<td class="table-success">+${diferencia}</td>`
    if (diferencia < 0) return `<td class="table-danger">${diferencia}</td>`
    return `<td class="table-info">${diferencia}</td>`
  }

  for (const key in actual) {
    let diferencia = anterior ? actual[key] - anterior[key] : ''
    totalListActual += actual[key]
    if (anterior) totalListAnterior += anterior[key]
    tr += `
      <tr>
        <td>${key.toLocaleLowerCase()}</td>
         ${anterior ? `<td class="table-secondary">${anterior[key]}</td>` : ''}
        <td class="table-secondary">${actual[key]}</td>
         ${anterior ? celdaDiferencia(diferencia) : ''}
      </tr>`
  }

  let totalDiferencia = totalListActual - totalListAnterior

  if (cantidadPropiedades > 1) {
    tr += `<tr class="p-0 table-primary">
    <td>TOTAL</td>
    ${
      totalListAnterior
        ? `<td class="table-secondary">${totalListAnterior}</td>`
        : ''
    }
    <td class='table-secondary'>${totalListActual}</td>${
      totalListAnterior ? celdaDiferencia(totalDiferencia) : ''
    }
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

export function createComparationContainer({ data }) {
  if (!data) return
  let { registro_actual, registro_anterior, confirmBtn } = data

  console.log(data)
  return `<div
      class='request-comparation-container'
      id='request-comparation-container'
    >
    ${createCard({
      actual: registro_actual,
      anterior: registro_anterior,
      confirmBtn,
    })}`
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
    totalPagarAnterior

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
    <div class='card p-2 slide-up-animation'>
      <div class='card-header row py-2'>
     ${
       anterior
         ? `<div class="col">
        <h5 class='card-title text-center m-2'>
          <b>Nómina Anterior:</b>
        </h5>
        <h5 class='card-title text-center m-2'>
          <b>Nombre nómina:</b> ${correlativoAnterior} - ${nombreNominaAnterior}
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
        <b>Nombre nómina:</b> ${correlativoActual} - ${nombreNominaActual}
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

      <div class='card-body request-list-container'>
      
        ${listaAsignaciones ? listaAsignaciones : ''}

        <div class="d-flex flex-column">
        ${listaDeducciones ? listaDeducciones : ''}
        ${listaAportes ? listaAportes : ''}
        ${listaAportes ? listaAportes : ''}
        </div>
        

      
      </div>
      <div class="card-footer">
      ${
        confirmBtn
          ? `<div class='form-group'>
            <button class='btn btn-primary w-100' id="confirm-request" data-correlativo="${correlativoActual}">CONFIRMAR</button>
          </div>`
          : ''
      }
      </div>
    </div>`
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
    <table class="table" style='width: 100%'>
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

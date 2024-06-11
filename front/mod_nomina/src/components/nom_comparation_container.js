export function createComparationContainer({ data }) {
  console.log(data)
  let { registro_actual, registro_anterior } = data

  let correlativoActual = registro_actual.correlativo
  let nombreNominaActual = registro_actual.nombre_nomina
  let estadoActual = registro_actual.status
  let listaAsignaciones = createObjectList(
    registro_anterior.asignaciones,
    registro_actual.asignaciones,
    'Asignaciones'
  )

  let listaDeducciones = createObjectList(
    registro_anterior.deducciones,
    registro_actual.deducciones,
    'Deducciones'
  )
  let totalEmpleadosActual = registro_actual.empleados.length
  let totalPagarActual = registro_actual.total_pagar.reduce(
    (acc, el) => el + acc,
    0
  )

  return `<div
      class='request-comparation-container'
      id='request-comparation-container'
    >
    ${createCard({
      correlativo: correlativoActual,
      nombreNomina: nombreNominaActual,
      estado: estadoActual,
      listaAsignaciones: listaAsignaciones,
      listaDeducciones: listaDeducciones,
      totalEmpleados: totalEmpleadosActual,
      totalPagar: totalPagarActual,
      confirmBtn: true,
    })}`
}

const createCard = ({
  correlativo,
  nombreNomina,
  estado,
  listaAsignaciones,
  listaDeducciones,
  totalEmpleados,
  totalPagar,
  confirmBtn,
}) => {
  return `
    <div class='card p-2 slide-up-animation'>
      <div class='card-header d-flex py-2'>
      <div class="">
        <h5 class='card-title text-center m-2'>
          <b>Nómina Anterior:</b>
        </h5>
        <h5 class='card-title text-center m-2'>
          <b>Nombre nómina:</b> ${correlativo} - ${nombreNomina}
        </h5>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Cantidad de empleados: </b>${totalEmpleados}
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Total a pagar: </b>${totalPagar}bs
        </h6>
        <h6 class='card-subtitle text-center mb-2'>
          <b>Estado: </b>${estado}
        </h6>
      </div>

      <div class="">
      <h5 class='card-title text-center m-2'>
        <b>Nómina consultada:</b>
      </h5>
      <h5 class='card-title text-center m-2'>
        <b>Nombre nómina:</b> ${correlativo} - ${nombreNomina}
      </h5>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Cantidad de empleados: </b>${totalEmpleados}
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Total a pagar: </b>${totalPagar}bs
      </h6>
      <h6 class='card-subtitle text-center mb-2'>
        <b>Estado: </b>${estado}
      </h6>
    </div>
      </div>

      <div class='card-body table-responsive'>
       
        ${listaAsignaciones}
        
        ${listaDeducciones}

      
      </div>
      <div class="card-footer">
      ${
        confirmBtn
          ? `<div class='form-group'>
            <button class='btn btn-primary w-100'>CONFIRMAR</button>
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
const createObjectList = (objAnterior, objActual, title) => {
  let li = ''
  let tr = ''

  for (const keys in objActual) {
    let diferencia = objAnterior ? objAnterior[keys] - objActual[keys] : ''
    let anterior = objAnterior ? objAnterior[keys] : ''
    let actual = objActual[keys]
    tr += `<tr>
        <td>${keys}</td>
       ${objAnterior ? `<td class="table-secondary">${anterior}</td>` : ''}
        <td class="table-primary">${actual}</td>
        ${objAnterior ? `<td>${diferencia}</td>` : ''}
      </tr>`
  }

  return `
    <table class="table table-sm table-responsive mx-auto" style='width: fit-content'>
    <h5 class='card-title text-center'>
    <b>${title}</b>
  </h5>
      <thead>
        <th class="">Propiedad</th>
        ${objAnterior ? `<th>Anterior</th>` : ''}
        <th>Actual</th>
        ${objAnterior ? `<th>Diferencia</th>` : ''}
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

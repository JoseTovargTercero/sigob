export function createComparationContainer({ data }) {
  console.log(data)
  let { registro_actual, registro_anterior } = data

  let correlativoActual = registro_actual.correlativo
  let nombreNominaActual = registro_actual.nombre_nomina
  let estadoActual = registro_actual.status
  let listaAsignacionesActual = createObjectList(registro_actual.asignaciones)
  let listaDeduccionesActual = createObjectList(registro_actual.deducciones)
  let totalEmpleadosActual = registro_actual.empleados.length
  let totalPagarActual = registro_actual.total_pagar.reduce(
    (acc, el) => el + acc,
    0
  )

  let correlativoAnterior,
    nombreNominaAnterior,
    estadoAnterior,
    listaAsignacionesAnterior,
    listaDeduccionesAnterior,
    totalEmpleadosAnterior,
    totalPagarAnterior

  if (registro_anterior) {
    correlativoAnterior = registro_anterior.correlativo
    nombreNominaAnterior = registro_anterior.nombre_nomina
    estadoAnterior = registro_anterior.status
    listaAsignacionesAnterior = createObjectList(registro_anterior.asignaciones)
    listaDeduccionesAnterior = createObjectList(registro_anterior.deducciones)
    totalEmpleadosAnterior = registro_anterior.empleados.length
    totalPagarAnterior = registro_anterior.total_pagar.reduce(
      (acc, el) => el + acc,
      0
    )
  }

  return `<div
      class='request-comparation-container'
      id='request-comparation-container'
    >${
      !registro_anterior
        ? `  <div class='card p-2 slide-up-animation'>
      <div class='card-header'>
        <h5 class='card-title text-center m-2'>
          <b>Nómina anterior:</b>
        </h5>
        <h5 class='card-title text-center m-2'>
          No existe un registro anterior de nómina
        </h5>
      </div>
    </div>`
        : createCard({
            correlativo: correlativoAnterior,
            nombreNomina: nombreNominaAnterior,
            estado: estadoAnterior,
            listaAsignaciones: listaAsignacionesAnterior,
            listaDeducciones: listaDeduccionesAnterior,
            totalEmpleados: totalEmpleadosAnterior,
            totalPagar: totalPagarAnterior,
          })
    }
    ${createCard({
      correlativo: correlativoActual,
      nombreNomina: nombreNominaActual,
      estado: estadoActual,
      listaAsignaciones: listaAsignacionesActual,
      listaDeducciones: listaDeduccionesActual,
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
      <div class='card-header'>
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

      <div class='card-body'>
        <h5 class='card-title text-center'>
          <b>ASIGNACIONES</b>
        </h5>
        ${listaAsignaciones}
        <h5 class='card-title text-center'>
          <b>DEDUCCIONES</b>
        </h5>
        ${listaDeducciones}

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

const createObjectList = (obj) => {
  let li = ''
  for (const keys in obj) {
    li += `<li class="list-group-item p-2"><b>${keys.toUpperCase()}:</b> ${
      obj[keys]
    } Bs.</li>`
  }

  return `<ul class="list-group list-group-flush mb-4">${li}</ul>`
}

export const nomReportCard = ({ data, identificador }) => {
  if (!data) return `<div class="card"><h2>DATOS NO ESPECIFICADOS</h2></div>`
  if (!identificador)
    return `<div class="card"><h2>IDENTIFICADOR NO ESPECIFICADOS</h2></div>`

  let nombre_nomina = data.nombre_nomina
  let totalEmpleados = data.empleados.length
  let fechaCreacion = data.creacion
  let totalPagar = data.total_a_pagar.reduce((value, acc) => value + acc, 0)

  return ` <div
      class='card col-lg-8 mx-auto p-2 slide-up-animation d-flex flex-row align-items-center gap-2 nom-report-card'
      id='nom-report-card'
    >
      <div class='card-header py-2'>
        <small class='d-block text-center w-100 py-0'>
          Generar reportes (PDF, TXT, ETC)
        </small>
        <p class=' mb-0'>CORRELATIVO: </p>
        <h5 class=' mb-2'>${identificador}</h5>

        <p class=' mb-0'>NOMBRE DE NOMINA: </p>
        <h5 class=' mb-2'>${nombre_nomina}</h5>

        <p class=' mb-0'>TOTAL A PAGAR: </p>
        <h5 class=' mb-2'>${totalPagar}Bs.</h5>

        <p class=' mb-0'>Total de empleados: </p>
        <h5 class=' mb-2'>${totalEmpleados} empleado/s</h5>

        <p class=' mb-0'>Fecha de creación: </p>
        <h5 class=' mb-2'>${fechaCreacion}</h5>
      </div>
      <div class='card-body'>
      <h5 class='text-center mb-2'>Generar reportes:</h5>
        <div class='btn-report-actions'>
          <button class='mx-0 btn btn-danger' id='generar-txt'>
            <i class='bx bxs-file-pdf bx-sm'></i>
          </button>
          <button class='mx-auto btn btn-secondary' id='generar-pdf'>
            <i class='bx bxs-file-txt bx-sm'></i>
          </button><button class='mx-auto btn btn-secondary' id='generar-pdf'>
          <i class='bx bxs-file-txt bx-sm'></i>
        </button><button class='mx-auto btn btn-secondary' id='generar-pdf'>
        <i class='bx bxs-file-txt bx-sm'></i>
      </button><button class='mx-auto btn btn-secondary' id='generar-pdf'>
      <i class='bx bxs-file-txt bx-sm'></i>
    </button>
        </div>
      </div>
    </div>`
}

// el correlativo, nombre_nomina, total a pagar, la fecha y quizás el identificador

{
  /* <button class='btn btn-danger' id='generar-pdf-venezuela'>
PDF VENEZUELA
</button>
<button class='btn btn-danger' id='generar-pdf-tesoro'>
PDF tesoro
</button>

<button class='btn btn-danger' id='generar-pdf-bicentenario'>
PDF bicentenario
</button>
<button class='btn btn-danger' id='generar-pdf-caroni'>
PDF caroni
</button> */
}

import { FRECUENCY_TYPES } from '../helpers/types.js'

export const nomReportCard = ({ data }) => {
  if (!data)
    return `<div class='modal-window' id='modal-report'>
        <div class='modal-box'>
          <header class='modal-box-header'>
            <h5>Gestionar información</h5>
            <button
              id='btn-close-report'
              type='button'
              class='btn btn-danger'
              aria-label='Close'
            >
              ×
            </button>
          </header>
          <div class='card'>
            <h2>DATOS NO ESPECIFICADOS</h2>
          </div>
        </div>
      </div>`

  console.log(data)
  let nombre_nomina = data.nombre_nomina
  let totalEmpleados = data.empleados.length
  let fechaCreacion = data.creacion
  let correlativo = data.correlativo
  let totalPagar = data.total_a_pagar.reduce((value, acc) => value + acc, 0)

  let identificador = FRECUENCY_TYPES[data.frecuencia][0]
  let frecuencia = data.frecuencia
  console.log(frecuencia)

  return `<div class='modal-window' id='modal-report'>
      <div class='modal-box'>
        <header class='modal-box-header'>
          <h5>Gestionar información</h5>
          <button
            id='btn-close-report'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            ×
          </button>
        </header>
        <div
          class='card h-100 p-2 slide-up-animation d-flex flex-row align-items-center gap-2 nom-report-card'
          id='nom-report-card'
        >
          <div class='card-header py-5'>
            <small class='d-block text-center w-100 py-0'>
              Generar reportes (PDF, TXT, ETC)
            </small>
            <p class=' mb-0'>CORRELATIVO: </p>
            <h5 class=' mb-2'>${correlativo}</h5>
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
              <a
                target='_parent'
                href='../../../../../sigob/back/modulo_nomina/nom_txt_descargas.php?correlativo=${correlativo}&frecuencia=${frecuencia}'
                class='mx-auto btn btn-secondary size-change-animation'
                id='generar-txt'
              >
                <i class='bx bxs-file-txt bx-sm'></i>
              </a>
            </div>
          </div>
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

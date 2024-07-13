import { descargarNominaTxt } from '../api/peticionesNomina.js'
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

  let nombre_nomina = data.nombre_nomina
  let totalEmpleados = data.empleados.length
  let fechaCreacion = data.creacion
  let correlativo = data.correlativo
  let identificador = data.identificador
  let totalPagar = data.total_a_pagar
    .reduce((value, acc) => value + acc, 0)
    .toFixed(2)

  console.log(data)

  let frecuencia = data.frecuencia

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
          class='card h-100 p-2 slide-up-animation d-flex flex-column align-items-center gap-2 nom-report-card'
          id='nom-report-card'
        >
          <div class='card-header py-5 text-center'>
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
              <button
              data-correlativo="${correlativo}"        
              data-identificador="${identificador}"        
                class='mx-auto btn btn-secondary size-change-animation'
                id='generar-txt'
              >
               GENERAR 📁
              </button>
              
            </div>
          </div>
        </div>
      </div>
    </div>`
}

{
  /* <i class='bx bxs-file-txt bx-sm'></i> */
}

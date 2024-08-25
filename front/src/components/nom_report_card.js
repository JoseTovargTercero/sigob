import { separarMiles } from '../helpers/helpers.js'
import { FRECUENCY_TYPES } from '../helpers/types.js'

export const nomReportCard = ({ data, elementToInsert }) => {
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
  let totalEmpleados = separarMiles(data.empleados.length)
  let fechaCreacion = data.creacion
  let correlativo = data.correlativo
  let identificador = data.identificador
  let totalPagar = separarMiles(
    data.total_a_pagar.reduce((value, acc) => value + acc, 0).toFixed(2)
  )

  console.log(data)

  let frecuencia = data.frecuencia
  let card2 = ` <div class='modal-window' id='modal-report'>
  <div class='modal-box slide-up-animation'>
    <header class='modal-box-header'>
      <h5 class=' mb-0 text-center'>Gestionar información de petición</h5>
      <button
        data-close='btn-close-report'
        type='button'
        class='btn btn-danger'
        aria-label='Close'
      >
        &times;
      </button>
    </header>
    <div class='modal-box-content text-center'>
     <small class='d-block text-center w-100 py-0 mb-2'>
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
    <div class="modal-box-footer card-footer d-flex align-items-center justify-content-center gap-2 py-0">
       <h5 class='text-center mb-2'>Generar reportes:</h5>
        <button
              data-correlativotxt="${correlativo}"        
              data-identificador="${identificador}"        
                class='btn btn-secondary size-change-animation'
              >
               GENERAR 📁
              </button>
      </div>
  </div>
</div>`

  document
    .getElementById(elementToInsert)
    .insertAdjacentHTML('beforebegin', card2)
}

// {
//   /* <i class='bx bxs-file-txt bx-sm'></i> */
// }

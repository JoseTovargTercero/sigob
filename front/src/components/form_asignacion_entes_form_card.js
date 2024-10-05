// CONTINUAR MAQUETADO
// AJUSTAR INFORMACIÓN DE LA DISTRIBUCIÓN PRESUPUESTARIA
// AJUSTAR INFORMACIÓN DEL PLAN OPERATIVO DE ENTES
// COLOCAR EL MONTO RESTANTE EN UN HEADER EN LA CARD
// AÑADIR UNA TABLA PARA SELECCIONAR PARTIDAS QUE SE QUIERAN ASIGNAR PARA POSTERIOR ASIGNARLES SU MONTO

import { getPartidas } from '../api/partidas.js'
import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const form_asignacion_entes_form_card = async ({
  elementToInset,
  plan,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  let ejercicio = await getEjecicio(1),
    partidas = await getPartidas()

  const oldCardElement = d.getElementById('asignacion-entes-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `   <div class='card slide-up-animation' id='asignacion-entes-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Validar información de plan operativo</h5>
          <small class='mt-0 text-muted'>
            Introduzca los datos para la verificar el plan operativo
          </small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body' id='card-body-container'>
        <div class='row' id='card-body-part-1'>
          <div class='col'>
            <h5 class=''>
              Información de la distribución presupuestaria anual
            </h5>
            <h5 class=''>
              <b>Año actual:</b>
              <span>${ejercicio ? ejercicio.ano : 'No definido'}</span>
            </h5>
            <h5 class=''>
              <b>Situado actual:</b>
              <span>${
                ejercicio ? separarMiles(ejercicio.situado) : 'No definido'
              }</span>
            </h5>
            <ul class='list-group'>
              <li class='list-group-item'>Año fiscal actual: 4</li>
              <li class='list-group-item'>Tipo de Ente: Monto total</li>
              <li class='list-group-item'>Nombre del Ente: Ente 5</li>
              <li class='list-group-item'>ID POA: 4</li>
              <li class='list-group-item'>Partidas a Abonar:</li>
              <ul>
                <li class='list-group-item'>Partida 1: $9000</li>
              </ul>
              <li class='list-group-item'>Monto Total: $17000</li>
            </ul>
          </div>
          <div class='col'>
            <h5 class='card-title'>Plan Operativo</h5>
            <ul class='list-group'>
              <li class='list-group-item'>ID Ente: 4</li>
              <li class='list-group-item'>Tipo de Ente: Descentralizado</li>
              <li class='list-group-item'>Nombre del Ente: Ente 5</li>
              <li class='list-group-item'>ID POA: 4</li>
              <li class='list-group-item'>Partidas a Abonar:</li>
              <ul>
                <li class='list-group-item'>Partida 1: $9000</li>
              </ul>
              <li class='list-group-item'>Monto Total: $17000</li>
            </ul>
          </div>
        </div>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='asignacion-entes-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById('asignacion-entes-form-card')
  // let formElement = d.getElementById('asignacion-entes-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }
  }

  async function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

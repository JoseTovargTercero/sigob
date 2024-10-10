import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const form_distribucion_modificar_form_card = ({ elementToInset }) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('distribucion-modificar-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `   <div class='card slide-up-animation' id='distribucion-modificar-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Modificar valor entre partidas</h5>
          <small class='mt-0 text-muted'>
            Modifique el valor entre partidas antes de que cierre el ejercicio
            fiscal
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
      <div class='card-body'>
        <form id='distribucion-modificar-form-card'>

          <div class='row mb-4'>
            <div class='col'>
              <h6 class='mb-0'>
                Monto total:{' '}
                <b id='monto-total'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>Monto disponible en esta partida</small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Monto restante:{' '}
                <b id='monto-restante'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>
                Monto total asignado a nueva partida
              </small>
            </div>
          </div>

          <div class='row'>
            <div class='col'>
              <div class='form-group'>
                <label class='form-label'>Partida a modificar</label>
                <input
                  class='form-control'
                  name='partida-1'
                  type='text'
                  placeholder='MONTO...'
                />
              </div>
            </div>
            <div class='col'>
              <div class='form-group'>
                <label class='form-label'>
                  Partida a asignar (distribucion)
                </label>
                <input
                  class='form-control'
                  name='partida-2'
                  type='text'
                  placeholder='Partida (distribucion)'
                />
              </div>

              <div class='form-group'>
                <label class='form-label'>Partida a asignar (nueva)</label>
                <input
                  class='form-control'
                  name='partida-2'
                  type='text'
                  placeholder='Partida (nueva)'
                />
              </div>
            </div>
            <div class='col'>
              <div class='form-group'>
                <label class='form-label'>Monto a asignar</label>
                <input
                  class='form-control partida-input partida-monto'
                  type='number'
                  name='partida-monto'
                  id='partida-monto'
                  placeholder='Monto a asignar...'
                />
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='distribucion-modificar-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById('distribucion-modificar-card')
  let formElement = d.getElementById('distribucion-modificar-form-card')

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

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

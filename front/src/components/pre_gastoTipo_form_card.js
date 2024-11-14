import { getPartidas } from '../api/partidas.js'
import { registrarTipoGasto } from '../api/pre_gastos.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { pre_gastos_form_card } from './pre_gastos_form_card.js'

const d = document

export const pre_gastosTipo_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('gastos-tipo-form-card')
  if (cardElement) cardElement.remove()

  let fieldList = { nombre: '' }
  let fieldListErrors = {
    nombre: {
      value: true,
      message: 'Escriba el nombre del tipo de gasto',
      type: 'text',
    },
  }

  let card = `  <div class='card slide-up-animation' id='gastos-tipo-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo tipo de gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
          Introduzca el nombre para el nuevo tipo de gasto y la partida asociada
          </small>
        </div>
        <button
          data-close='btn-close-report'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <form id='gastos-tipo-form'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label for="nombre" class='form-label'>
                  Nombre para nuevo tipo de gasto
                </label>
                <div class='input-group'>
                <input
                  class='form-control'
                  type='text'
                  name='nombre'
                  id='nombre'
                  placeholder='Nombre...'
                />
                </div>
              </div>
            </div>

            </div>
          </div>
        </form>
        <div class='card-footer'>
        <button class='btn btn-primary' id='gastos-tipo-guardar'>
          Guardar
        </button>
      </div>
      </div>
     
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  const formElement = d.getElementById('gastos-tipo-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-tipo-form-card')
    let gastosRegistrarCointaner = d.getElementById(
      'gastos-registrar-container'
    )
    gastosRegistrarCointaner.classList.remove('hide')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
      pre_gastos_form_card({ elementToInsert: 'gastos-view' })
    }
    if (e.target.id === 'gastos-tipo-guardar') {
      enviarInformacion()
    }
  }

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar este tipo de gasto?',
      successFunction: async function () {
        let res = await registrarTipoGasto({ nombre: fieldList.nombre })
        if (res.success) {
          closeCard()
        }
      },
    })
  }

  function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}

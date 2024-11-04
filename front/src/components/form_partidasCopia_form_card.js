import {
  actualizarPartida,
  getFormPartidas,
  guardarPartida,
  guardarPartidaOrdinaria,
} from '../api/partidas.js'
import { loadPartidasTable } from '../controllers/form_partidasTable.js'
import {
  confirmNotification,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

let fieldList = {
  partida: '',
  ordinaria: '',

  denominacion: '',
}

let fieldListErrors = {
  partida: {
    value: true,
    type: 'partida',
    message: 'Formato no coincide',
  },
  ordinaria: {
    value: true,
    type: 'number2',
    message: 'Terminación inválida',
  },

  denominacion: {
    value: true,
    type: 'text',
    message: 'Descripción inválida',
  },
}
export const form_partidaCopia_form_card = async ({ elementToInsert, id }) => {
  let nombreCard = 'partidaCopy'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) oldCardElement.remove()

  let card = `  <div class='modal-window' id='${nombreCard}-form-card'>
      <div class='card modal-box slide-up-animation'>
        <div class='card-header modal-box-header d-flex justify-content-between'>
          <div class=''>
            <h5 class='mb-0'>
              Creación de partida ordinaria
            </h5>
            
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
          <form id='${nombreCard}-form' autocomplete='off'>
            <div class='row'>
              <div class='col-sm'>
                <div class='form-group'>
                  <label class='form-label'>Partida original</label>
                  <input
                    class='form-control'
                    type='text'
                    name='partida'
                    id='partida'
                    placeholder='xxx.xx.xx.xx.xxxx'
                    disabled
                  />
                </div>
              </div>
              <div class='col-sm-3'>
                <div class='form-group'>
                  <label class='form-label'>Terminación (Ordinaria)</label>
                  <input
                    class='form-control'
                    type='number'
                    name='ordinaria'
                    id='ordinaria'
                    
                    value=''
                    placeholder='Terminacion "0000"'
                  />
                </div>
              </div>
              <div class='col-sm'>
                <div class='form-group'>
                  <label class='form-label'>Descripción</label>
                  <input
                    class='form-control'
                    type='text'
                    name='denominacion'
                    id='denominacion'
                    placeholder='Descripción partida...'
                  />
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class='card-footer'>
          <button class='btn btn-primary' id='${nombreCard}-guardar'>
            Guardar
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  const formElement = d.getElementById(`${nombreCard}-form`)
  const cardElement = d.getElementById(`${nombreCard}-form-card`)

  if (id) {
    let partida = await getFormPartidas(id)

    let inputs = formElement.querySelectorAll('input')

    console.log(partida)

    inputs.forEach((input) => {
      // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN SELECT
      if (input.name === 'partida') input.value = partida[input.name]
      if (input.name !== 'ordinaria') {
        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      }
    })
  }

  const closeCard = () => {
    validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === `${nombreCard}-guardar`) {
      let inputs = formElement.querySelectorAll('input')

      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })
      console.log(fieldListErrors)
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Valide nuevamente los campos',
        })
      }

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea guardar esta nueva partida?',
        successFunction: async function () {
          let resultado = await guardarPartidaOrdinaria({
            partida: fieldList.partida,
            ordinaria: fieldList.ordinaria,
            denominacion: fieldList.denominacion,
          })

          if (resultado.success) {
            loadPartidasTable()
            closeCard()
          }
        },
      })
    }
  }

  function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })

    console.log(fieldList)
  }

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

function validateEditButtons() {
  d.getElementById(`partida-registrar`).removeAttribute('disabled')

  let editButtons = d.querySelectorAll('[data-copiaid][disabled]')

  if (editButtons.length < 1) return

  editButtons.forEach((btn) => {
    if (btn.hasAttribute('disabled')) {
      btn.removeAttribute('disabled')
    }
  })
}

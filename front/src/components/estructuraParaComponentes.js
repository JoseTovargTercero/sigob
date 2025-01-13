import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

const nombre_componente = ({ elementToInsert }) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  let nombreCard = '${nombreCard}'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
        <div class='card-header d-flex justify-content-between'>
          <div class=''>
            <h5 class='mb-0'>CAMBIAR TEXTO</h5>
            <small class='mt-0 text-muted'>CAMBIAR TEXTO</small>
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
        <div class='card-body'></div>
        <div class='card-footer'>
          <button class='btn btn-primary' id='${nombreCard}-guardar'>
            Guardar
          </button>
        </div>
      </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  function closeCard(card) {
    // validateEditButtons()
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
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

function chosenSelect() {
  let select = ` <div class='form-group'>
        <label for='search-select-${nombreCard}' class='form-label'>
          Seleccione el sector
        </label>
        <select
          class='form-select ${nombreCard}-input chosen-select'
          name='id_sector'
          id='search-select-${nombreCard}'
        >
          <option>Elegir...</option>
        </select>
      </div>`

  let options = [`<option>Elegir...</option>`]
  let data

  data.fullInfo.forEach((sector) => {
    let option = `<option value='${sector.id}'>${sector.sector}.${sector.programa}.${sector.proyecto} - ${sector.nombre}</option>`
    options.push(option)
  })

  selectEjercicio.innerHTML = options.join('')

  $('.chosen-select')
    .chosen()
    .change(function (obj, result) {
      console.log('changed: %o', arguments)
    })
}

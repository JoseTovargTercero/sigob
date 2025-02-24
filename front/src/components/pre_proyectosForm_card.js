import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const pre_proyectosForm_card = ({ elementToInsert }) => {
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

  let creditosForm = () => {
    return `      <div class='row slide-up-animation' id='card-body-part-1'>
        <div class='col'>
          <div class='form-group'>
            <label class='form-label' for='monto'></label>
            <input
              class='form-select proyecto-input'
              name='monto'
              id='monto'
              placeholder='Monto...'
            />
          </div>
        </div>
        <div class='col'>
          <div class='form-group'>
            <label class='form-label' for='monto'></label>
            <input
              class='form-select proyecto-input'
              name='fecha'
              id='fecha'
              type='date'
            />
          </div>
        </div>

        <div class='form-group'>
          <label for='tipo_credito' class='form-label'>
            Tipo de crédito
          </label>
          <select
            class='form-select proyecto-input'
            name='tipo-credito'
            id='tipo-credito'
          >
            <option value=''>Elegir...</option>
            <option value='FCI'>FCI</option>
            <option value='VB'>Venezuela Bella</option>
          </select>
        </div>

        <div class='form-group'>
          <label for='tipo_credito' class='form-label'>
            Tipo de crédito
          </label>
          <select
            class='form-select proyecto-input'
            name='tipo-credito'
            id='tipo-credito'
          >
            <option value=''>Elegir...</option>
            <option value='FCI'>FCI</option>
            <option value='VB'>Venezuela Bella</option>
          </select>
        </div>
      </div>`
  }

  let proyectoForm = () => {
    return `  <div class='row slide-up-animation' id='card-body-part-2'>
        <div class='form-group'>
          <texarea
            name='proyecto-descripcion'
            id='proyecto-descripcion'
          ></texarea>
        </div>

        <div class='mb-4 col-4 align-self-start'>
          <h5 class='text-center text-blue-600 mb-2'>Acciones</h5>
          <div id='opciones-container-accion'></div>
          <div class='text-center'>
            <button
              type='button'
              class='btn btn-sm bg-brand-color-1 text-white'
              data-add='accion'
            >
              <i class='bx bx-plus'></i> AGREGAR PARTIDA
            </button>
          </div>
        </div>
      </div>`
  }

  let card = `  <div class='card slide-up-animation' id='${nombreCard}-form-card'>
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
      <div class='card-body'>${creditosForm()}</div>
      <div class='card-footer'>
        <div class='card-footer text-center'>
          <button class='btn btn-secondary' id='btn-previus'>
            Atrás
          </button>
          <button class='btn btn-primary' id='btn-next'>
            Siguiente
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  //   let formElement = d.getElementById(`${nombreCard}-form`)

  let formFocus = 1

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

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')

    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part-1')
    let cardBodyPart2 = d.getElementById('card-body-part-2')
    let cardBodyPart3 = d.getElementById('card-body-part-3')

    if (e.target === btnNext) {
      if (formFocus === 1) {
        // let planInputs = d.querySelectorAll('.plan-input')

        // planInputs.forEach((input) => {
        //   fieldList = validateInput({
        //     target: input,
        //     fieldList,
        //     fieldListErrors,
        //     type: fieldListErrors[input.name].type,
        //   })
        // })

        // if (Object.values(fieldListErrors).some((el) => el.value)) {
        //   toastNotification({
        //     type: NOTIFICATIONS_TYPES.fail,
        //     message: 'Hay campos inválidos',
        //   })
        //   return
        // }

        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', creditosForm())
        }

        if (btnPrevius.hasAttribute('disabled'))
          btnPrevius.removeAttribute('disabled')

        formFocus++
        return
      }
      if (formFocus === 2) {
        // let planInputsOptions = d.querySelectorAll('.plan-input-option')

        // planInputsOptions.forEach((input) => {
        //   fieldListOptions = validateInput({
        //     target: input,
        //     fieldListOptions,
        //     fieldListErrorsOptions,
        //     type: fieldListErrorsOptions[input.name].type,
        //   })
        // })

        // if (Object.values(fieldListErrorsOptions).some((el) => el.value)) {
        //   toastNotification({
        //     type: NOTIFICATIONS_TYPES.fail,
        //     message: 'Hay campos inválidos',
        //   })
        //   return
        // }

        cardBodyPart2.classList.add('d-none')

        if (id) {
          btnNext.textContent = 'Actualizar'
        } else {
          btnNext.textContent = 'Enviar'
        }

        if (cardBodyPart3) {
          cardBodyPart3.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', terceraVista())
        }

        formFocus++
        return
      }

      if (formFocus === 3) {
        let data = validarInformacion()

        console.log(data)

        enviarInformacion(data)
      }
    }

    if (e.target === btnPrevius) {
      if (formFocus === 3) {
        cardBodyPart2.classList.remove('d-none')
        btnNext.textContent = 'Siguiente'

        if (cardBodyPart3) {
          cardBodyPart3.classList.add('d-none')
        }

        formFocus--
        return
        // confirmNotification({
        //   type: NOTIFICATIONS_TYPES.send,
        //   message: 'Si continua se borrarán los cambios hechos aquí',
        //   successFunction: function () {
        //     cardBodyPart2.remove()

        //     cardBodyPart1.classList.remove('d-block')
        //     cardBodyPart1.classList.add('d-none')
        //     btnNext.textContent = 'Siguiente'
        //     // btnAdd.classList.remove('d-none')

        //     partidasSeleccionadas = []
        //     cardBody.innerHTML += seleccionPartidas()
        //     validarSeleccionPartidasTable()

        //     formFocus--
        //   },
        // })
      }
      if (formFocus === 2) {
        cardBodyPart1.classList.remove('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.add('d-none')
        }

        formFocus--

        btnPrevius.setAttribute('disabled', true)

        return
      }
    }
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

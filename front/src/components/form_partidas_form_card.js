import {
  actualizarPartida,
  getFormPartidas,
  guardarPartida,
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
  nombre: '',
  descripcion: '',
}

let fieldListErrors = {
  partida: {
    value: true,
    type: 'partida',
    message: 'Formato no coincide',
  },
  nombre: {
    value: true,
    type: 'text',
    message: 'Nombre inválido',
  },
  descripcion: {
    value: true,
    type: 'text',
    message: 'Descripción inválida',
  },
}
export const form_partida_form_card = async ({ elementToInsert, id }) => {
  const oldCardElement = d.getElementById('partida-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `    <div class='card slide-up-animation' id='partida-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nueva partida presupuestaria</h5>
          <small class='mt-0 text-muted'>
            Introduzca los datos para la nueva partida
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
        <form id='partida-form' autocomplete='off'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Código</label>
                <input
                  class='form-control'
                  type='text'
                  name='partida'
                  id='partida'
                  placeholder='xxx.xx.xx.xx.xxxx'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Nombre</label>
                <input
                  class='form-control'
                  type='text'
                  name='nombre'
                  id='nombre'
                  placeholder='Nombre de partida...'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Descripción</label>
                <input
                  class='form-control'
                  type='text'
                  name='descripcion'
                  id='descripcion'
                  placeholder='Descripción partida...'
                />
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='partida-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  const formElement = d.getElementById('partida-form')
  const cardElement = d.getElementById('partida-form-card')

  if (id) {
    let partida = await getFormPartidas(id)

    let inputs = formElement.querySelectorAll('input')

    inputs.forEach((input) => {
      // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN SELECT
      if (partida[input.name] !== undefined) input.value = partida[input.name]

      validateInput({
        target: input,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[input.name].type,
      })
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

    if (e.target.id === 'partida-guardar') {
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

      if (id) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message: '¿Desea actualizar esta partida?',
          successFunction: async function () {
            let resultado = await actualizarPartida({
              partida: formElement.partida.value,
              nombre: formElement.nombre.value,
              descripcion: formElement.descripcion.value,
              id,
            })

            loadPartidasTable()
            closeCard()
          },
        })
      }
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea guardar esta nueva partida?',
        successFunction: async function () {
          let resultado = await guardarPartida({
            partida: formElement.partida.value,
            nombre: formElement.nombre.value,
            descripcion: formElement.descripcion.value,
          })

          loadPartidasTable()
          closeCard()
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
  d.getElementById('partida-registrar').removeAttribute('disabled')

  let editButtons = d.querySelectorAll('[data-editarid][disabled]')

  if (editButtons.length < 1) return

  editButtons.forEach((btn) => {
    if (btn.hasAttribute('disabled')) {
      btn.removeAttribute('disabled')
      btn.textContent = 'Editar'
    }
  })
}

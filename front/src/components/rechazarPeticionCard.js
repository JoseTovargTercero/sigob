import { rechazarPeticion } from '../api/movimientos.js'
import { deleteRowMovimiento } from '../controllers/movimientosTable.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const rechazarPeticionCard = ({
  elementToInsertId,
  correcciones,
  movimientosId,
  peticionId,
  resetInput,
}) => {
  let element = d.getElementById(elementToInsertId)

  let cardElement = d.getElementById('motivo-card')
  if (cardElement) return

  let fieldList = {
    motivo: '',
  }

  let fieldListErrors = {
    motivo: {
      value: true,
      type: 'text',
      message: 'Tiene que llenar el motivo del rechazo',
    },
  }

  let card = `<div class='modal-window' id='motivo-card'>
      <div class='card modal-box short slide-up-animation'>
        <header class='modal-box-header'>
          <h5 class=' mb-0 text-center'>RECHAZO DE NOMINA</h5>
          <button
            id='btn-close-motivo-card'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </header>
        <div class='modal-box-content'>
        <div class="row">
          <div class="col">
          <p class="h6">Correciones realizadas: ${correcciones.length}</p>
          </div>
          </div>
          <form id='motivo-card-form'>
            <label for='motivo'>Motivo de rechazo</label>
            <textarea
              class='form-control'
              name='motivo'
              placeholder='Motivo de rechazo de petición...'
              id='motivo'
              style='height: 150px'
            ></textarea>
          </form>
        </div>
        <div class='modal-box-footer card-footer d-flex align-items-center justify-content-center gap-2 py-0'>
         <button class='btn btn-danger' id='btn-confirm-motivo'>
          Rechazar
        </button>
        <button class='btn btn-secondary' id='btn-deny-motivo'>
        Cancelar
        </button>
       
        </div>
      </div>
    </div>`

  element.insertAdjacentHTML('beforeend', card)

  let rechazoPeticionCardForm = d.getElementById('motivo-card-form')

  const closeModalCard = () => {
    let cardElement = d.getElementById('motivo-card')
    console.log(cardElement)
    cardElement.remove()
    d.removeEventListener('click', validateClick)
    return false
  }

  const confirmModalCard = () => {
    console.log('a')
    fieldList = validateInput({
      target: rechazoPeticionCardForm.motivo,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[rechazoPeticionCardForm.motivo.name].type,
    })

    if (Object.values(fieldListErrors).some((el) => el.value)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se puede rechazar sin una correción general',
      })
      return
    }

    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Seguro de querer continuar?',
      successFunction: async function () {
        let res = await rechazarPeticion({
          peticion: { id: peticionId, correccion: fieldList.motivo },
          movimientos: movimientosId,
          correcciones: correcciones,
        })
        resetInput()
      },
    })
  }

  const validateClick = (e) => {
    if (e.target.id === 'btn-close-motivo-card') {
      closeModalCard(e)
    }
    if (e.target.id === 'btn-deny-motivo') {
      closeModalCard(e)
    }
    if (e.target.id === 'btn-confirm-motivo') {
      confirmModalCard()
    }
  }

  d.addEventListener('click', validateClick)

  rechazoPeticionCardForm.addEventListener('submit', (e) => e.preventDefault())
  rechazoPeticionCardForm.addEventListener('input', (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
    console.log(fieldList)
  })
}

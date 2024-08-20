import { toastNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const nomCorregirCard = ({
  elementToInsertId,
  info,
  correcciones,
  movimientosId,
  peticionId,
}) => {
  let element = d.getElementById(elementToInsertId)
  console.log(info)

  let fieldList = {
    input: '',
  }

  let fieldListErrors = {
    input: {
      value: true,
      type: 'text',
      message: 'Complete el campo de corrección',
    },
  }

  let card = `<div class='modal-window' id='movimiento-card'>
      <div class='card modal-box short slide-up-animation'>
        <header class='modal-box-header'>
          <h5 class=' mb-0 text-center'>Añadir corrección</h5>
          <button
            id='btn-close-movimiento-card'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </header>
        <div class='modal-box-content'>
        <h1>CONTENIDO</h1>
        </div>
        <div class="modal-box-footer card-footer d-flex align-items-center justify-content-center gap-2 py-0">
            <button class="btn btn-primary" id="btn-confirm">Añadir</button>
          </div>
      </div>
    </div>`

  element.insertAdjacentHTML('beforeend', card)

  let btnClose = d.getElementById('btn-close-movimiento-card')
  let btnConfirm = d.getElementById('btn-confirm')
  let movimientoCardForm = d.getElementById('movimiento-card-form')

  const closeModalCard = () => {
    let cardElement = d.getElementById('movimiento-card')
    cardElement.remove()
    btnClose.removeEventListener('click', closeModalCard)
    btnConfirm.removeEventListener('click', confirmModalCard)
    movimientoCardForm.removeEventListener('input', validarInput)

    return false
  }

  const validarInput = (e) => {
    console.log(fieldList)
    fieldList = validateInput({
      target: movimientoCardForm.correccion,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[movimientoCardForm.correccion.name].type,
    })
  }

  const confirmModalCard = () => {
    console.log('a')
    fieldList = validateInput({
      target: movimientoCardForm.correccion,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[movimientoCardForm.correccion.name].type,
    })

    if (Object.values(fieldListErrors).some((el) => el.value)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se puede añadir una corrección vacía',
      })
      return
    }

    let cardElement = d.getElementById('movimiento-card')
    cardElement.remove()
    btnClose.removeEventListener('click', closeModalCard)
    correcciones.push([
      Number(info.id),
      fieldList.correccion,
      Number(peticionId),
    ])
    movimientosId.push(info.id)

    // Eliminar fila en tabla de movimientos
    deleteRowMovimiento(d.querySelector(`[data-id="${info.id}"]`).closest('tr'))
    toastNotification({
      type: NOTIFICATIONS_TYPES.done,
      message: 'Correción añadida',
    })
  }

  movimientoCardForm.addEventListener('submit', (e) => e.preventDefault())
  movimientoCardForm.addEventListener('input', validarInput)

  btnClose.addEventListener('click', closeModalCard)
  btnConfirm.addEventListener('click', confirmModalCard)
}

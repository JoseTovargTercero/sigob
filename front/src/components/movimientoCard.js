import { deleteRowMovimiento } from '../controllers/movimientosTable.js'
import { toastNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const movimientoCard = ({
  elementToInsertId,
  info,
  correciones,
  movimientosId,
}) => {
  let element = d.getElementById(elementToInsertId)
  console.log(info)

  let fieldList = {
    correcion: '',
  }

  let fieldListErrors = {
    correcion: {
      value: true,
      type: 'text',
      message: 'Complete el campo de correción',
    },
  }

  let card = `<div class='modal-window' id='movimiento-card'>
      <div class='card modal-box short slide-up-animation'>
        <header class='modal-box-header'>
          <h5 class=' mb-0 text-center'>Añadir correción</h5>
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
          <div class='row'>
          <div class="col">
          <p class="h6">Acción: "${info.accion}"</p>
          </div>
          <div class="col">
          <p class="h6">Campo: "${info.campo}"</p>
          </div>
          <div class="col">
          <p class="h6">Valor anterior: "${info.valor_anterior}"</p>
          </div>
          <div class="col table-s">
          <p class="h6">Valor nuevo: "${info.valor_nuevo}"</p>
          </div>
          </div>

          <form id='movimiento-card-form'>
           <label for="correcion">OBSERVACIONES</label>
                      <textarea class="form-control" name="correcion"
                        placeholder="Observación para este movimiento..." id="correcion" style="height: 50px"></textarea>
          </form>
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
    btnConfirm.removeEventListener('click', closeModalCard)
    movimientoCardForm.removeEventListener('input', closeModalCard)

    return false
  }

  const validarInput = (e) => {
    console.log(fieldList)
    fieldList = validateInput({
      target: movimientoCardForm.correcion,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[movimientoCardForm.correcion.name].type,
    })
  }

  const confirmModalCard = () => {
    console.log('a')
    fieldList = validateInput({
      target: movimientoCardForm.correcion,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[movimientoCardForm.correcion.name].type,
    })

    if (Object.values(fieldListErrors).some((el) => el.value)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se puede añadir una correción vacía',
      })
      return
    }

    let cardElement = d.getElementById('movimiento-card')
    cardElement.remove()
    btnClose.removeEventListener('click', closeModalCard)
    correciones.push([info.usuario_id, info.id, fieldList.correcion])
    movimientosId.push(info.id)

    let closestRow = d.getElementById('btn-confirm')
    deleteRowMovimiento()
  }

  movimientoCardForm.addEventListener('submit', (e) => e.preventDefault())
  movimientoCardForm.addEventListener('input', validarInput)

  btnClose.addEventListener('click', closeModalCard)
  btnConfirm.addEventListener('click', confirmModalCard)
}

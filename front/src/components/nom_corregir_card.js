import { toastNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const nomCorregirCard = ({ elementToInsertId }) => {
  let element = d.getElementById(elementToInsertId)

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
      <div class='card modal-box slide-up-animation'>
       
        <div class="card-header">
            <h5>Corrección general de nómina:</h5>
            <p>Motivo de rechazo: [Motivo]</p>
        </div>
        <div class="card-body">
            <h5>Correcciones a realizar:</h5>
            <ul class="list-group">
                <li class="list-group-item">Corrección 1 <button class="btn btn-primary btn-corregir">Corregir</button></li>
                <li class="list-group-item">Corrección 2 <button class="btn btn-primary btn-corregir">Corregir</button></li>
                <li class="list-group-item">Corrección 3 <button class="btn btn-primary btn-corregir">Corregir</button></li>
            </ul>
        </div>
        <div class="card-footer">
            <h5>Área de corrección:</h5>
            <textarea class="form-control mb-2" rows="3" placeholder="Ingrese la corrección aquí"></textarea>
            <div>
                <button class="btn btn-success mr-2">Corregir manual</button>
                <button class="btn btn-danger">Revertir</button>
            </div>
        </div>
    
      </div>
    </div>`

  element.insertAdjacentHTML('beforeend', card)

  // let btnClose = d.getElementById('btn-close-movimiento-card')
  // let btnConfirm = d.getElementById('btn-confirm')
  // let movimientoCardForm = d.getElementById('movimiento-card-form')

  // const closeModalCard = () => {
  //   let cardElement = d.getElementById('movimiento-card')
  //   cardElement.remove()
  //   btnClose.removeEventListener('click', closeModalCard)
  //   btnConfirm.removeEventListener('click', confirmModalCard)
  //   movimientoCardForm.removeEventListener('input', validarInput)

  //   return false
  // }

  // const validarInput = (e) => {
  //   console.log(fieldList)
  //   fieldList = validateInput({
  //     target: movimientoCardForm.correccion,
  //     fieldList,
  //     fieldListErrors,
  //     type: fieldListErrors[movimientoCardForm.correccion.name].type,
  //   })
  // }

  // const confirmModalCard = () => {
  //   console.log('a')
  //   fieldList = validateInput({
  //     target: movimientoCardForm.correccion,
  //     fieldList,
  //     fieldListErrors,
  //     type: fieldListErrors[movimientoCardForm.correccion.name].type,
  //   })

  //   if (Object.values(fieldListErrors).some((el) => el.value)) {
  //     toastNotification({
  //       type: NOTIFICATIONS_TYPES.fail,
  //       message: 'No se puede añadir una corrección vacía',
  //     })
  //     return
  //   }

  //   let cardElement = d.getElementById('movimiento-card')
  //   cardElement.remove()
  //   btnClose.removeEventListener('click', closeModalCard)
  //   correcciones.push([
  //     Number(info.id),
  //     fieldList.correccion,
  //     Number(peticionId),
  //   ])
  //   movimientosId.push(info.id)

  //   // Eliminar fila en tabla de movimientos
  //   deleteRowMovimiento(d.querySelector(`[data-id="${info.id}"]`).closest('tr'))
  //   toastNotification({
  //     type: NOTIFICATIONS_TYPES.done,
  //     message: 'Correción añadida',
  //   })
  // }

  // movimientoCardForm.addEventListener('submit', (e) => e.preventDefault())
  // movimientoCardForm.addEventListener('input', validarInput)

  // btnClose.addEventListener('click', closeModalCard)
  // btnConfirm.addEventListener('click', confirmModalCard)
}

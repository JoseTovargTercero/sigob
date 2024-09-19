import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const pre_gastos_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('gastos-form-card')
  if (cardElement) cardElement.remove()

  let card = ` <div class='card' id='gastos-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
            Introduzca el tipo de gasto y montó para ser procesado
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
        <form id="gastos-form-card">
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>TIPO DE GASTO</label>
                <select class='form-select' name='tipo_gasto'>
                  <option value='viaticos'>Viáticos</option>
                  <option value='suministros'>Suministros</option>
                  <option value='alquiler'>Alquiler</option>
                  <option value='servicios_publicos'>Servicios Públicos</option>
                  <option value='capacitacion'>Capacitación</option>
                  <option value='publicidad'>Publicidad</option>
                </select>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Monto</label>
                <input
                  class='form-control'
                  type='number'
                  name='monto'
                  id='monto'
                  placeholder='00.00Bs'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Monto</label>
                <input
                  class='form-control'
                  type='date'
                  name='fecha'
                  id='fecha'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>DESCRIPCIóN</label>
                <textarea
                  class='form-control'
                  name=''
                  id=''
                  placeholder='Se registra un gasto de...'
                ></textarea>
              </div>
            </div>
          </div>
        </form>
        <div clas='card-footer'>
          <button class='btn btn-primary' id='gastos-guardar'>
            Guardar
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforebegin', card)

  const formElement = d.getElementById('gastos-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-card-form')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.rechazarid) {
      let id = e.target.dataset.rechazarid
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Seguro de rechazar esta solicitud de dozavo?',
        successFunction: async function () {
          let row = d.querySelector(`[data-detalleid="${id}"]`).closest('tr')

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Solicitud rechazada',
          })

          deleteSolicitudDozeavo({ row, id })
          closeCard()
        },
      })
    }

    if (e.target.dataset.close) {
      closeCard()
    }
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

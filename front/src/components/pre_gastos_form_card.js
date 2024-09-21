import { getTiposGastos } from '../api/pre_gastos.js'

import {
  confirmNotification,
  insertOptions,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { pre_gastosTipo_form_card } from './pre_gastoTipo_form_card.js'

const d = document

export const pre_gastos_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('gastos-form-card')
  if (cardElement) cardElement.remove()

  let card = ` <div class='card slide-up-animation' id='gastos-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
            Introduzca el tipo de gasto y montó para ser procesado
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
        <form id='gastos-form'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label class='form-label'>Tipo de gasto</label>
                <div class='input-group'>
                  <div class='w-80'>
                    <select
                      class='form-select'
                      name='tipo_gasto'
                      id='search-select-gastos'
                    >
                    
                    </select>
                  </div>
                  <div class='input-group-prepend'>
                    <button
                      type='button'
                      id='add-tipo-gasto'
                      class='input-group-text btn btn-primary'
                    >
                      +
                    </button>
                  </div>
                </div>
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
                <label class='form-label'>Fecha del gasto</label>
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
                  rows="1"
                ></textarea>
              </div>
            </div>
          </div>
        </form>
       
      </div>
      <div class='card-footer'>
      <button class='btn btn-primary' id='gastos-guardar'>
        Guardar
      </button>
    </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  getTiposGastos().then((res) => {
    console.log(res.mappedData)
    insertOptions({ input: 'gastos', data: res.mappedData })
  })

  const formElement = d.getElementById('gastos-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-form-card')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
      let gastosRegistrarCointaner = d.getElementById(
        'gastos-registrar-container'
      )
      gastosRegistrarCointaner.classList.remove('hide')
    }
    if (e.target.id === 'add-tipo-gasto') {
      closeCard()

      pre_gastosTipo_form_card({ elementToInsert: 'gastos-view' })
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

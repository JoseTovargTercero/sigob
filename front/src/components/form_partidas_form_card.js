import { guardarPartida } from '../api/partidas.js'
import {
  confirmNotification,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

let fieldList = {
  codigo: '',
  nombre: '',
  descripcion: '',
}

let fieldListErrors = {
  codigo: {
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
export const form_partida_form_card = async ({ elementToInsert, data }) => {
  const cardElement = d.getElementById('partida-form-card')
  if (cardElement) cardElement.remove()

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
                  name='codigo'
                  id='codigo'
                  placeholder='xx.xx.si.xxx.xx.xx.xxxx'
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

  const closeCard = () => {
    let cardElement = d.getElementById('partida-form-card')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }
    if (e.target.id === 'add-tipo-gasto') {
      closeCard()

      pre_gastosTipo_form_card({ elementToInsert: 'gastos-view' })
    }

    if (e.target.id === 'partida-guardar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: async function () {
          console.log(formElement.nombre)
          guardarPartida({
            codigo: formElement.codigo.value,
            nombre: formElement.nombre.value,
            descripcion: formElement.descripcion.value,
          })
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
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}

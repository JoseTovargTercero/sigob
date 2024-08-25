import { sendDependencia } from '../api/dependencias.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

let fieldList = {
  dependencia: '',
  cod_dependencia: '',
}
let fieldListErrors = {
  dependencia: {
    value: true,
    message: 'No puede estar vacío',
    type: 'text',
  },
  cod_dependencia: {
    value: true,
    message: 'No puede estar vacío',
    type: 'number',
  },
}

export const nom_dependencia_form_card = ({
  elementToInsert,
  reloadSelect,
}) => {
  let cardElement = d.getElementById('modal-dependency')
  if (cardElement) cardElement.remove()

  let card = `  <div id='modal-dependency' class='modal-window hide'>
              <div class='modal-box short slide-up-animation'>
                <header class='modal-box-header'>
                  <h4>AÑADIR NUEVA UNIDAD</h4>
                  <button id='btn-close-modal-dependency' type='button' class='btn btn-danger' aria-label='Close'>
                    &times;
                  </button>
                </header>

                <div class='modal-box-content'>
                  <form id='employee-dependencia-form'>
                    <div class='row mx-0'>
                      <div class='col-sm'>
                        <input class='form-control' type='text' name='dependencia' placeholder='Nombre unidad...'
                          id='dependencia' />
                      </div>
                      <div class='col-sm'>
                        <input type='number' class=' form-control' name='cod_dependencia'
                          id='cod_dependencia-input' placeholder='Codigo de unidad' />
                      </div>
                    </div>
                  </form>
                </div>

                <div class='modal-box-footer'>
                  <button class='btn btn-primary' id='dependency-save-btn'>
                    GUARDAR UNIDAD
                  </button>
                </div>
              </div>
            </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforeend', card)

  let formElement = d.getElementById('employee-dependencia-form')
  const closeModalCard = () => {
    let cardElement = d.getElementById('modal-dependency')
    console.log(cardElement)
    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.id === 'dependency-save-btn') {
      fieldList = validateInput({
        target: formElement.dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.dependencia.name].type,
      })
      fieldList = validateInput({
        target: formElement.cod_dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.cod_dependencia.name].type,
      })
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Necesita llenar todos los campos',
        })
      }

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Seguro de registrar esta dependencia?',
        successFunction: async function () {
          sendDependencia({
            informacion: {
              dependencia: formElement.dependencia.value,
              cod_dependencia: formElement.cod_dependencia.value,
            },
          }).then((res) => {
            closeModalCard()
            reloadSelect()
          })
        },
      })
    }

    if (e.target.id === 'btn-close-modal-dependency') {
      console.log('hola')
      closeModalCard()
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

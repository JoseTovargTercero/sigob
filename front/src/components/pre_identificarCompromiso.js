import {
  generarCompromisoPdf,
  registrarCompromiso,
} from '../api/pre_compromisos.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const pre_identificarCompromiso = ({
  elementToInsert,
  reset,
  registerCompromise = false,
  acceptFunction,
}) => {
  let fieldList = { codigo: '' }
  let fieldListErrors = {
    codigo: {
      value: true,
      message: 'El compromiso necesita una identificación',
      type: 'textarea',
    },
  }

  let nombreCard = 'compromiso'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) oldCardElement.remove()

  let card = `  <div id='${nombreCard}-form-card'>
      <form id='${nombreCard}-form'>
        <div class='form-group'>
          <label for='codigo' class='form-label'>
            Identificar compromiso
          </label>
          <input
            class='form-control partida-partida chosen-distribucion'
            type='text'
            name='codigo'
            id='codigo'
            placeholder='Identificación para el compromiso'
          />
        </div>
      </form>
    </div>`

  let modal = ` <div class='modal-window' id='${nombreCard}-form-card'>
      <div class='modal-box slide-up-animation'>
        <div class='modal-box-header'>
          <div class=''>
            <h5 class='mb-0'>Identificar compromiso antes de aceptar gasto</h5>
          </div>
          <button
            data-close='btn-close'
            type='button'
            class='btn btn-sm btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </div>
        <div class='modal-box-content'>${card}</div>
        <div class='modal-box-footer'>
          <button class='btn btn-primary' id='${nombreCard}-guardar'>
            Aceptar
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', modal)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    // cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === `${nombreCard}-guardar`) {
      let input = d.getElementById('codigo')
      fieldList = validateInput({
        target: input,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[input.name].type,
      })

      if (fieldListErrors.codigo.value) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Campo invalido',
        })
        return
      }

      enviarInformacion()
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

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message:
        'Al aceptar se descontará del presupuesto actual ¿Desea continuar?',
      successFunction: async function () {
        let res = await acceptFunction(fieldList.codigo)

        if (res.success) {
          if (registerCompromise) {
            let data = { ...registerCompromise, codigo: fieldList.codigo }
            let registrarRespuesta = await registrarCompromiso(data)
            if (!registrarRespuesta || registrarRespuesta.error) {
              toastNotification({
                message:
                  'No se pudo registrar el compromiso, vuelva a intentarlo',
              })
              return
            }
            let generarRespuesta = await generarCompromisoPdf(
              registrarRespuesta.success.id_compromiso,
              registrarRespuesta.success.correlativo
            )

            closeCard()
            reset()
            return
          }
          generarCompromisoPdf(
            res.success.compromiso.id_compromiso,
            res.success.compromiso.correlativo
          )

          closeCard()
          reset()
        }
      },
    })
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

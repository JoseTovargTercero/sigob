import {
  actualizarDirectivoData,
  registrarDirectivoData,
} from '../../api/form_informacion.js'
import { loadDirectivoTable } from '../../controllers/form_informacionTables.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../../helpers/types.js'
const d = document

export const form_informacionDirectivoForm = ({ elementToInsert, data }) => {
  let fieldList = {
    nombre_apellido: '',
    direccion: '',
    telefono: '',
    email: '',
  }
  let fieldListErrors = {
    nombre_apellido: {
      value: true,
      message: 'Introduzca un nombre válido',
      type: 'textarea',
    },
    direccion: {
      value: true,
      message: 'Introduzca una dirección válida',
      type: 'textarea',
    },
    telefono: {
      value: true,
      message: 'Introduzca un número telefónico válido',
      type: 'number',
    },
    email: {
      value: true,
      message: 'Introduzca un email válido',
      type: 'email',
    },
  }
  const oldCardElement = d.getElementById('directivo-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `    <div class='card slide-up-animation' id='directivo-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Formulario directivo</h5>
          <small class='mt-0 text-muted'>Introduzca los datos requeridos</small>
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
        <form id='directivo-form'>
          <div class='row mb-3'>
            <div class='col'>
              <label class='form-label' for='nombre_apellido'>
                Nombre y Apellido
              </label>
              <input
                type='text'
                class='form-control directivo-input'
                id='nombre_apellido'
                name='nombre_apellido'
                placeholder='Ingrese su nombre y apellido'
              />
            </div>
          </div>
          <div class='row mb-3'>
            <div class='col'>
              <label class='form-label' for='direccion'>
                Dirección
              </label>
              <input
                type='text'
                class='form-control directivo-input'
                id='direccion'
                name='direccion'
                placeholder='Ingrese su dirección completa'
              />
            </div>
            <div class='col'>
              <label class='form-label' for='telefono'>
                Teléfono
              </label>
              <input
                type='tel'
                class='form-control directivo-input'
                id='telefono'
                name='telefono'
                placeholder='Ingrese su número de teléfono'
              />
            </div>
            <div class='col'>
              <label class='form-label' for='email'>
                Correo Electrónico
              </label>
              <input
                type='email'
                class='form-control directivo-input'
                id='email'
                name='email'
                placeholder='Ingrese su correo electrónico'
              />
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='directivo-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  if (data) {
    let inputs = d.querySelectorAll('.directivo-input')
    inputs.forEach((input) => {
      input.value = data[input.name]
    })

    fieldList.id = data.id
  }

  let cardElement = d.getElementById('directivo-form-card')
  let formElement = d.getElementById('directivo-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === 'directivo-guardar') {
      let inputs = d.querySelectorAll('.directivo-input')

      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      let validaciones = Object.values(fieldListErrors)

      if (validaciones.some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Rellene los campos requeridos',
        })
      } else {
        enviarInformacion()
      }
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

  function enviarInformacion(info) {
    if (fieldList.id) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea actualizar este registro?',
        successFunction: async function () {
          let res = await actualizarDirectivoData({ info: fieldList })

          if (res.success) {
            closeCard()
            loadDirectivoTable()
          }
        },
      })
    } else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea realizar este registro?',
        successFunction: async function () {
          let res = await registrarDirectivoData({ info: fieldList })
          if (res.success) {
            closeCard()
            loadDirectivoTable()
          }
        },
      })
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

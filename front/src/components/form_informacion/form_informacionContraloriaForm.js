import {
  actualizarContraloriaData,
  registrarContraloriaData,
} from '../../api/form_informacion.js'
import { loadContraloriaTable } from '../../controllers/form_informacionTables.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../../helpers/types.js'
const d = document

export const form_informacionContraloriaForm = ({ elementToInsert, data }) => {
  let fieldList = {
    domicilio: '',
    telefono: '',
    pagina_web: '',
    email: '',
    nombre_apellido_gobernador: '',
  }
  let fieldListErrors = {
    domicilio: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
    telefono: {
      value: 'number',
      message: 'mensaje de error',
      type: 'text',
    },
    pagina_web: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
    email: {
      value: true,
      message: 'mensaje de error',
      type: 'email',
    },

    nombre_apellido_contralor: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('contraloria-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `    <div class='card slide-up-animation' id='contraloria-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Formulario contraloria</h5>
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
      <form id="contraloria-form">
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='nombre_apellido_contralor'>Nombre y Apellido del Contralor</label>
              <input
                  type='text'
                  class='form-control contraloria-input'
                  id='nombre_apellido_contralor'
                  name='nombre_apellido_contralor'
                  placeholder='Abog. Guillermo Forti'
                  required
              />
          </div>
          <div class='col'>
              <label class="form-label" for='domicilio'>Domicilio</label>
              <input
                  type='text'
                  class='form-control contraloria-input'
                  id='domicilio'
                  name='domicilio'
                  placeholder='AVENIDA AEROPUERTO SECTOR LOS LIRIOS "SEDE DE LA CONTRALORIA"'
                  required
              />
          </div>
      </div>
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='telefono'>Teléfono</label>
              <input
                  type='text'
                  class='form-control contraloria-input'
                  id='telefono'
                  name='telefono'
                  placeholder='0248-5212759'
                  required
              />
          </div>
          <div class='col'>
              <label class="form-label" for='pagina_web'>Página Web</label>
              <input
                  type='url'
                  class='form-control contraloria-input'
                  id='pagina_web'
                  name='pagina_web'
                  placeholder='www.contraloriaestadoamazonas.gob.ve'
              />
          </div>
      </div>
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='email'>Email</label>
              <input
                  type='email'
                  class='form-control contraloria-input'
                  id='email'
                  name='email'
                  placeholder='contraloria_amazonas@yahoo.es'
              />
          </div>
        
      </div>
    
  </form>
  
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='contraloria-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  if (data) {
    let inputs = d.querySelectorAll('.contraloria-input')
    inputs.forEach((input) => {
      input.value = data[input.name]
    })

    fieldList.id = data.id
  }

  let cardElement = d.getElementById('contraloria-form-card')
  let formElement = d.getElementById('contraloria-form')

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

    if (e.target.id === 'contraloria-guardar') {
      let inputs = d.querySelectorAll('.contraloria-input')

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
          let res = await actualizarContraloriaData({ info: fieldList })
          if (res.success) {
            loadContraloriaTable()
            closeCard()
          }
        },
      })
    } else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea realizar este registro?',
        successFunction: async function () {
          let res = await registrarContraloriaData({ info: fieldList })
          if (res.success) {
            loadContraloriaTable()
            closeCard()
          }

          closeCard()
          loadContraloriaTable()
        },
      })
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

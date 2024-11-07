import {
  actualizarGobernacionData,
  registrarGobernacionData,
} from '../../api/form_informacion.js'
import { loadGobernacionTable } from '../../controllers/form_informacionTables.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../../helpers/types.js'
const d = document

export const form_informacionGobernacionForm = ({ elementToInsert, data }) => {
  let fieldList = {
    identificacion: '',
    domicilio: '',
    telefono: '',
    pagina_web: '',
    fax: '',
    codigo_postal: '',
    nombre_apellido_gobernador: '',
  }
  let fieldListErrors = {
    identificacion: {
      value: true,
      message: 'Introduzca una identificacion válida',
      type: 'textarea',
    },
    domicilio: {
      value: true,
      message: 'Introduzca un domicilio válido',
      type: 'textarea',
    },
    telefono: {
      value: true,
      message: 'Introduzca un teléfono válido',
      type: 'textarea',
    },
    pagina_web: {
      value: true,
      message: 'Introduzca una página web válida',
      type: 'textarea',
    },
    fax: {
      value: null,
      message: 'Introduzca un fax válido',
      type: 'textarea',
    },
    codigo_postal: {
      value: true,
      message: 'Introduzca un código postal válido',
      type: 'textarea',
    },
    nombre_apellido_gobernador: {
      value: true,
      message: 'Introduzca un nombre válido',
      type: 'textarea',
    },
  }
  const oldCardElement = d.getElementById('gobernacion-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = ` <form id="gobernacion-form">
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='identificacion'>Identificación</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='identificacion'
                  name='identificacion'
                  placeholder='GOBERNACIÓN DE AMAZONAS'
                  required
              />
          </div>
          <div class='col'>
              <label class="form-label" for='domicilio'>Domicilio</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='domicilio'
                  name='domicilio'
                  placeholder='AVENIDA RIO NEGRO. FRENTE A LA PLAZA BOLIVAR.'
                  required
              />
          </div>
          <div class='col'>
              <label class="form-label" for='telefono'>Teléfono</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='telefono'
                  name='telefono'
                  placeholder='0248-5212759'
                  required
              />
          </div>
      </div>
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='pagina_web'>Página Web</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='pagina_web'
                  name='pagina_web'
                  placeholder='www.contraloriaestadoamazonas.gob.ve'
              />
          </div>
          <div class='col'>
              <label class="form-label" for='fax'>Fax</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='fax'
                  name='fax'
                  placeholder='(Dejar en blanco si no aplica)'
              />
          </div>
          <div class='col'>
              <label class="form-label" for='codigo_postal'>Código Postal</label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='codigo_postal'
                  name='codigo_postal'
                  placeholder='7101'
              />
          </div>
      </div>
      <div class='row mb-3'>
          <div class='col'>
              <label class="form-label" for='nombre_apellido_gobernador'>
                  Nombre y Apellido del Gobernador
              </label>
              <input
                  type='text'
                  class='form-control gobernacion-input'
                  id='nombre_apellido_gobernador'
                  name='nombre_apellido_gobernador'
                  placeholder='Ing. MIGUEL RODRIGUEZ'
              />
          </div>
      </div>
      <div class='card-footer'>
      <button class='btn btn-primary' id='gobernacion-guardar'>
        Actualizar
      </button>
    </div>
    </form>
    `

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  if (data) {
    let inputs = d.querySelectorAll('.gobernacion-input')
    inputs.forEach((input) => {
      input.value = data[input.name]
    })

    fieldList.id = data.id
  }

  let cardElement = d.getElementById('gobernacion-form-card')
  let formElement = d.getElementById('gobernacion-form')

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

    if (e.target.id === 'gobernacion-guardar') {
      let inputs = d.querySelectorAll('.gobernacion-input')

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
          let res = await actualizarGobernacionData({ info: fieldList })
        },
      })
    } else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea realizar este registro?',
        successFunction: async function () {
          let res = await registrarGobernacionData({ info: fieldList })
        },
      })
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', validateInputFunction)
  formElement.addEventListener('click', validateClick)
}

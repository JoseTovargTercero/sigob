import {
  actualizarConsejoData,
  actualizarContraloriaData,
  registrarConsejoData,
  registrarContraloriaData,
} from '../../api/form_informacion.js'
import {
  loadConsejoTable,
  loadContraloriaTable,
} from '../../controllers/form_informacionTables.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../../helpers/types.js'
const d = document

export const form_informacionConsejoForm = ({ elementToInsert, data }) => {
  let fieldList = {
    nombre_apellido_presidente: '',
    nombre_apellido_secretario: '',
    domicilio: '',
    telefono: '',
    pagina_web: '',
    email: '',
    consejo_local: '',
  }

  let fieldListErrors = {
    nombre_apellido_presidente: {
      value: true,
      message: 'Introduzca un texto válido',
      type: 'textarea',
    },
    nombre_apellido_secretario: {
      value: true,
      message: 'Introduzca un texto válido',
      type: 'textarea',
    },
    domicilio: {
      value: true,
      message: 'Introduzca un texto válido',
      type: 'textarea',
    },
    telefono: {
      value: true,
      message: 'Introduzca un número telefónico válido',
      type: 'textarea',
    },
    pagina_web: {
      value: true,
      message: 'Introduzca una web válida',
      type: 'textarea',
    },
    email: {
      value: true,
      message: 'Introduzca un correo eléctrónico válido',
      type: 'email',
    },

    consejo_local: {
      value: true,
      message: 'Introduzca un texto válido',
      type: 'textarea',
    },
  }
  const oldCardElement = d.getElementById('consejo-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `
 <form id="consejo-form">
    <div class='row mb-3'>
        <div class='col'>
            <label class="form-label" for='nombre_apellido_presidente'>Nombre y Apellido del Presidente</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='nombre_apellido_presidente'
                name='nombre_apellido_presidente'
                placeholder='Ej. Delkis Bastidas'
                required
            />
        </div>
        <div class='col'>
            <label class="form-label" for='nombre_apellido_secretario'>Nombre y Apellido del Secretario</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='nombre_apellido_secretario'
                name='nombre_apellido_secretario'
                placeholder='Ej. Lester Mirabal'
                required
            />
        </div>
    </div>
    <div class='row mb-3'>
        <div class='col'>
            <label class="form-label" for='domicilio'>Domicilio</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='domicilio'
                name='domicilio'
                placeholder='Avenida Aeropuerto Sector "Simón Bolivar"'
                required
            />
        </div>
        <div class='col'>
            <label class="form-label" for='telefono'>Teléfono</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='telefono'
                name='telefono'
                placeholder='Ej. 0248-5212759'
                required
            />
        </div>
    </div>
    <div class='row mb-3'>
        <div class='col'>
            <label class="form-label" for='pagina_web'>Página Web</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='pagina_web'
                name='pagina_web'
                placeholder='Ej. www.contraloriaestadoamazonas.gob.ve'
            />
        </div>
        <div class='col'>
            <label class="form-label" for='email'>Email</label>
            <input
                type='email'
                class='form-control consejo-input'
                id='email'
                name='email'
                placeholder='Ej. contraloria_amazonas@yahoo.es'
            />
        </div>
    </div>
     <div class='row mb-3'>
      <div class='col'>
            <label class="form-label" for='consejo_local'>Consejo local</label>
            <input
                type='text'
                class='form-control consejo-input'
                id='consejo_local'
                name='consejo_local'
                placeholder='Consejo local...'
            />
        </div>
     </div>
     <div class='card-footer'>
        <button class='btn btn-primary' id='consejo-guardar'>
          Guardar
        </button></div>
</form>

  
      
      
     `

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  if (data) {
    let inputs = d.querySelectorAll('.consejo-input')
    inputs.forEach((input) => {
      input.value = data[input.name]
    })

    fieldList.id = data.id
  }

  let cardElement = d.getElementById('consejo-form-card')
  let formElement = d.getElementById('consejo-form')

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

    if (e.target.id === 'consejo-guardar') {
      console.log(fieldList, fieldListErrors)

      let inputs = d.querySelectorAll('.consejo-input')

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

  function enviarInformacion() {
    if (fieldList.id) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea actualizar este registro?',
        successFunction: async function () {
          console.log(fieldList)
          let res = await actualizarConsejoData({ info: fieldList })
        },
      })
    } else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea realizar este registro?',
        successFunction: async function () {
          let res = await registrarConsejoData({ info: fieldList })
        },
      })
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', validateInputFunction)
  formElement.addEventListener('click', validateClick)
}

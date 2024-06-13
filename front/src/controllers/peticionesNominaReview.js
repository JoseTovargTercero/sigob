import {
  confirmarPeticionNomina,
  getComparacionNomina,
  getPeticionesNomina,
} from '../api/peticionesNomina.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

let fieldList = {
  'select-nomina': '',
}

let fieldListErrors = {
  'select-nomina': {
    value: true,
    message: 'Seleccione una nómina a consultar',
    type: 'text',
  },
}

let nominas = {}

export async function validateRequestNomForm({
  selectId,
  consultBtnId,
  formId,
}) {
  let requestInfo = await getPeticionesNomina()

  console.log(requestInfo)

  let selectNom = d.getElementById(selectId)
  let consultNom = d.getElementById(consultBtnId)
  let requestComparationForm = d.getElementById(formId)
  // let comparationContainer = d.getElementById('request-comparation-container')

  let selectValues = await requestInfo
    .map((el) => {
      if (el.status == 0) {
        nominas.correlativo = el.correlativo
        nominas.nombre_nomina = el.nombre_nomina
        return `<option value="${el.correlativo}">${el.correlativo} - ${el.nombre_nomina}</option>`
      }
    })
    .join('')

  selectNom.insertAdjacentHTML('beforeend', selectValues)

  selectNom.addEventListener('change', (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
    console.log(fieldList)
  })

  d.addEventListener('click', async (e) => {
    if (fieldList['select-nomina'] === '') return

    let comparationContainer = d.getElementById('request-comparation-container')

    if (e.target === consultNom) {
      let result = requestInfo.find(
        (el) => el.correlativo === fieldList['select-nomina']
      )
      if (comparationContainer) comparationContainer.remove()

      let peticiones = await getComparacionNomina(result)

      requestComparationForm.insertAdjacentHTML(
        'beforeend',
        createComparationContainer({ data: peticiones })
      )
    }

    if (e.target.id === 'confirm-request') {
      console.log(e.target.dataset.correlativo)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: confirmarPeticionNomina,
        successFunctionParams: e.target.dataset.correlativo,
        message: '¿Seguro de aceptar esta petición?',
      })
    }
  })
}

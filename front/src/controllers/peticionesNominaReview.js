import { getRegConEmployeeData } from '../api/empleados.js'
import {
  confirmarPeticionNomina,
  generarNominaTxt,
  getComparacionNomina,
  getPeticionesNomina,
  getRegConPeticionesNomina,
} from '../api/peticionesNomina.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { nom_comparation_employee } from '../components/regcon_comparation_employee.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { FRECUENCY_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'

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
  let requestInfo = await getRegConPeticionesNomina()

  console.log(requestInfo)

  let selectNom = d.getElementById(selectId)
  let consultNom = d.getElementById(consultBtnId)
  let requestComparationForm = d.getElementById(formId)
  let comparationContainer = d.getElementById('request-comparation-container')

  let selectValues = requestInfo
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
    // console.log(fieldList)
  })

  d.addEventListener('click', async (e) => {
    if (fieldList['select-nomina'] === '') return

    comparationContainer = d.getElementById('request-comparation-container')

    if (e.target === consultNom) {
      let result = requestInfo.find(
        (el) => el.correlativo === fieldList['select-nomina']
      )
      fieldList.frecuencia = result.frecuencia
      fieldList.identificador = result.identificador

      if (comparationContainer) comparationContainer.remove()

      let peticiones = await getComparacionNomina(result)
      peticiones.confirmBtn = true

      console.log(peticiones)

      requestComparationForm.insertAdjacentHTML(
        'beforeend',
        createComparationContainer({ data: peticiones })
      )

      let tablaDiferencia = await nom_comparation_employee({
        anterior: peticiones.registro_anterior.empleados,
        actual: peticiones.registro_actual.empleados,
        obtenerEmpleado: getRegConEmployeeData,
      })
    }

    if (e.target.id === 'confirm-request') {
      console.log(e.target.dataset.correlativo)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: confirmarPeticionNomina,
        successFunctionParams: e.target.dataset.correlativo,
        othersFunctions: [resetInput, validateRequestFrecuency],
        message: '¿Seguro de aceptar esta petición?',
      })
    }
  })

  async function resetInput() {
    let comparationContainer = d.getElementById('request-comparation-container')

    if (comparationContainer) comparationContainer.remove()

    selectNom.value = ''
    selectNom.innerHTML = ''
    let requestInfo = await getRegConPeticionesNomina()
    let selectValues = requestInfo
      .map((el) => {
        if (el.status == 0) {
          nominas.correlativo = el.correlativo
          nominas.nombre_nomina = el.nombre_nomina
          return `<option value="${el.correlativo}">${el.correlativo} - ${el.nombre_nomina}</option>`
        }
      })
      .join('')

    selectNom.insertAdjacentHTML(
      'beforeend',
      `<option value="">Seleccionar petición de nómina</option>`
    )
    selectNom.insertAdjacentHTML('beforeend', selectValues)
  }

  async function validateRequestFrecuency() {
    let res = await generarNominaTxt({
      correlativo: fieldList['select-nomina'],
      identificador: fieldList.identificador,
    })

    console.log(res)
  }
}

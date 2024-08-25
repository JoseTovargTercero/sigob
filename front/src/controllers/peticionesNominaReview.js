import { getRegConEmployeeData } from '../api/empleados.js'
import { getRegConMovimiento } from '../api/movimientos.js'
import {
  confirmarPeticionNomina,
  generarNominaTxt,
  getComparacionNomina,
  getPeticionesNomina,
  getRegConPeticionesNomina,
} from '../api/peticionesNomina.js'
import { rechazarPeticionCard } from '../components/rechazarPeticionCard.js'
import { movimientoCard } from '../components/movimientoCard.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { nom_comparation_employee } from '../components/regcon_comparation_employee.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { FRECUENCY_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadMovimientosTable,
  reloadTableMovimientos,
} from './movimientosTable.js'

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

let correcciones = []
let movimientosId = []

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

  let requestInformation = d.getElementById('request-information')

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

    if (e.target === consultNom) {
      movimientosId = []
      correcciones = []
      let result = requestInfo.find(
        (el) => el.correlativo === fieldList['select-nomina']
      )
      fieldList.frecuencia = result.frecuencia
      fieldList.identificador = result.identificador
      fieldList.id = result.id
      fieldList.correlativo = result.correlativo

      console.log(fieldList)

      let peticiones = await getComparacionNomina(result)
      peticiones.confirmBtn = true

      console.log(peticiones)

      requestInformation.classList.remove('hide')

      let tablaMovimietnos = await loadMovimientosTable({
        id_nomina: peticiones.registro_actual.nomina_id,
        elementToInsert: 'request-information',
      })

      let tablaDiferencia = await nom_comparation_employee({
        anterior: peticiones.registro_anterior.empleados,
        actual: peticiones.registro_actual.empleados,
        elementToInsert: 'request-information',
        obtenerEmpleado: getRegConEmployeeData,
      })

      createComparationContainer({
        data: peticiones,
        elementToInsert: 'request-information',
      })
    }

    if (e.target.id === 'confirm-request') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: function () {
          confirmarPeticionNomina(fieldList.correlativo)
          resetInput()
          validateRequestFrecuency()
        },

        message: '¿Seguro de aceptar esta petición?',
      })
    }

    if (e.target.id === 'reset-request') {
      movimientosId = []
      correcciones = []
      let result = requestInfo.find(
        (el) => el.correlativo === fieldList['select-nomina']
      )
      fieldList.frecuencia = result.frecuencia
      fieldList.identificador = result.identificador
      fieldList.id = result.id
      fieldList.correlativo = result.correlativo

      console.log(fieldList)

      let peticiones = await getComparacionNomina(result)
      peticiones.confirmBtn = true

      console.log(peticiones)
      reloadTableMovimientos({
        id_nomina: peticiones.registro_actual.nomina_id,
      })
    }

    if (e.target.classList.contains('btn-corregir')) {
      getRegConMovimiento(e.target.dataset.id).then((res) => {
        console.log(res)
        let correccion = movimientoCard({
          elementToInsertId: 'request-comparation-container',
          info: res,
          correcciones,
          movimientosId,
          peticionId: fieldList.id,
        })
      })
    }

    if (e.target.id === 'deny-request') {
      rechazarPeticionCard({
        elementToInsertId: 'request-comparation-container',
        peticionId: fieldList.id,
        correcciones,
        movimientosId,
        resetInput,
      })
    }
  })

  async function resetInput() {
    requestInformation.classList.add('hide')

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

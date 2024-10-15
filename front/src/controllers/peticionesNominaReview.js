import { getRegConEmployeeData } from '../api/empleados.js'
import { getRegConMovimiento } from '../api/movimientos.js'
import {
  confirmarPeticionNomina,
  generarNominaTxt,
  getRegConComparacionNomina,
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
import { loadRegconRequestTable } from './regcon_peticionesTable.js'

const d = document
const w = window

let fieldList = {}

let nominas = {}

let correcciones = []
let movimientosId = []

export async function validateRequestNomForm() {
  // let requestInfo = await getRegConPeticionesNomina()

  let requestComparationForm = d.getElementById('request-nom-form')

  let requestInformation = d.getElementById('request-information')

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.peticionId) {
      requestInformation.classList.remove('hide')
      let peticion = await getRegConPeticionesNomina(
        e.target.dataset.peticionId
      )

      // Para uso en otros scopes
      fieldList.frecuencia = peticion.frecuencia
      fieldList.identificador = peticion.identificador
      fieldList.nombre_nomina = peticion.nombre_nomina
      fieldList.id = peticion.id
      fieldList.correlativo = peticion.correlativo

      let comparacionNomina = await getRegConComparacionNomina({
        correlativo: peticion.correlativo,
        nombre_nomina: peticion.nombre_nomina,
      })

      let tablaMovimietnos = await loadMovimientosTable({
        id_nomina: comparacionNomina.registro_actual.nomina_id,
        elementToInsert: 'request-information',
      })

      let tablaDiferencia = await nom_comparation_employee({
        anterior: comparacionNomina.registro_anterior.empleados,
        actual: comparacionNomina.registro_actual.empleados,
        elementToInsert: 'request-information',
        obtenerEmpleado: getRegConEmployeeData,
      })

      createComparationContainer({
        data: comparacionNomina,
        elementToInsert: 'request-information',
      })

      scroll(0, 500)
    }

    // if (e.target === 'asd') {
    //   movimientosId = []
    //   correcciones = []
    //   let result = requestInfo.find(
    //     (el) => el.correlativo === fieldList['select-nomina']
    //   )
    //   fieldList.frecuencia = result.frecuencia
    //   fieldList.identificador = result.identificador
    //   fieldList.id = result.id
    //   fieldList.correlativo = result.correlativo

    //   console.log(fieldList)

    //   let peticiones = await getComparacionNomina(result)
    //   peticiones.confirmBtn = true

    //   console.log(peticiones)
    // }

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
      // let result = requestInfo.find(
      //   (el) => el.correlativo === fieldList['select-nomina']
      // )
      // fieldList.frecuencia = result.frecuencia
      // fieldList.identificador = result.identificador
      // fieldList.id = result.id
      // fieldList.correlativo = result.correlativo

      // console.log(fieldList)

      let peticiones = await getRegConComparacionNomina({
        correlativo: fieldList.correlativo,
        nombre_nomina: fieldList.nombre_nomina,
      })
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

    loadRegconRequestTable()
  }

  async function validateRequestFrecuency() {
    let res = await generarNominaTxt({
      correlativo: fieldList.correlativo,
      identificador: fieldList.identificador,
    })

    console.log(res)
  }
}

function mostrarTabla(tablaId) {
  let confirmadoId = 'request-table-confirmado'
  let revisionId = 'request-table-revision'

  let confirmadoTable = d.getElementById(`${confirmadoId}-container`)
  let revisionTable = d.getElementById(`${revisionId}-container`)

  if (tablaId === confirmadoId) {
    confirmadoTable.classList.add('d-block')
    confirmadoTable.classList.remove('d-none')
    revisionTable.classList.add('d-none')
    revisionTable.classList.remove('d-block')
  } else if (tablaId === revisionId) {
    confirmadoTable.classList.add('d-none')
    confirmadoTable.classList.remove('d-block')
    revisionTable.classList.add('d-block')
    revisionTable.classList.remove('d-none')
  }
}

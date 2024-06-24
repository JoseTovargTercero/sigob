import {
  calculoNomina,
  getNominas,
  getPeticionesNomina,
  enviarCalculoNomina,
  descargarNominaTxt,
} from '../api/peticionesNomina.js'
import { nomReportCard } from '../components/nom_report_card.js'
import { tableListCard } from '../components/tabla_lista_card.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { createTable, employeePayTableHTML } from './peticionesNominaTable.js'
import { loadRequestTable } from './peticionesTable.js'

const d = document
const w = window

// const selectGrupo = d.getElementById('grupo')
// const selectNomina = d.getElementById('nomina')
// const requestSelectContainer = d.getElementById('request-employee-container')
// const showRequestGroupBtn = d.getElementById('show-request-group')
// const closeRequestListBtn = d.getElementById('close-request-list')
// const employeePayForm = d.getElementById('employee-pay-form')

let fieldList = {
  nomina: '',
  grupo: '',
}

let fieldListErrors = {
  grupo: {
    value: true,
    message: 'Seleccione un grupo de nómina',
    type: 'number',
  },
  nomina: {
    value: true,
    message: 'Seleccione una nómina',
    type: 'text',
  },
}

let requestInfo

export function validateEmployeePayForm({
  selectIdNomina,
  selectIdGrupo,
  selectIdFrecuencia,
  requestSelectContainerId,
  showRequestGroupBtnId,
  formId,
}) {
  // Cargar tabla de peticiones
  loadRequestTable()

  let selectGrupo = d.getElementById(selectIdGrupo)
  let selectNomina = d.getElementById(selectIdNomina)
  let selectFrecuencia = d.getElementById(selectIdFrecuencia)
  let requestSelectContainer = d.getElementById(requestSelectContainerId)
  let showRequestGroupBtn = d.getElementById(showRequestGroupBtnId)
  let employeePayForm = d.getElementById(formId)

  selectGrupo.addEventListener('change', async (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })

    let nominas = await getNominas(e.target.value)

    selectNomina.innerHTML = ''
    let employeePayTableCard = d.getElementById('request-employee-table-card')

    if (employeePayTableCard) employeePayTableCard.remove()

    if (nominas.length > 0)
      nominas.forEach((nomina) => {
        let option = `<option value="${nomina}">${
          nomina || 'Grupo de nómina vacío'
        }</option>`
        selectNomina.insertAdjacentHTML('beforeend', option)
      })
    else
      selectNomina.insertAdjacentHTML(
        'beforeend',
        `<option value="">Grupo de nómina vacío</option>`
      )
  })

  selectNomina.addEventListener('change', async (e) => {
    if (!e.target.value) return
    fieldList.nomina = e.target.value

    selectFrecuencia.insertAdjacentHTML(
      'beforeend',
      `<option value="s1">Semana 1</option>
    <option value="s2">Semana 2</option>
    <option value="s3">Semana 3</option>
    <option value="s4">Semana 4</option>
    <option value="q1">Quincena 1</option>
    <option value="q2">Quincena 2</option>
    <option value="unico">Mensual</option>`
    )
  })

  selectFrecuencia.addEventListener('change', async (e) => {
    if (!fieldList.nomina)
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Elija una nomina.',
      })

    if (!fieldList.grupo)
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Elija un grupo de nomina.',
      })

    let nomina = await calculoNomina({
      nombre: fieldList.nomina,
      identificador: e.target.value,
    })

    requestInfo = nomina

    selectGrupo.value = ''
    selectNomina.value = ''

    let employeePayTableCard = d.getElementById('request-employee-table-card')
    if (employeePayTableCard) employeePayTableCard.remove()

    let columns = Object.keys(nomina.informacion_empleados[0])

    // Insertar tabla en formulario
    employeePayForm.insertAdjacentHTML(
      'beforeend',
      employeePayTableHTML({ nominaData: nomina, columns })
    )
    createTable({ nominaData: nomina, columns })
  })

  d.addEventListener('click', async (e) => {
    if (e.target === showRequestGroupBtn) {
      requestSelectContainer.classList.remove('hide')
    }

    if (e.target.id === 'btn-show-request') {
      e.preventDefault()
      let peticiones = await getPeticionesNomina()
      let peticion = peticiones.find(
        (el) => el.correlativo === e.target.dataset.correlativo
      )

      fieldList.frecuencia = peticion.frecuencia
      employeePayForm.insertAdjacentHTML(
        'beforeend',
        nomReportCard({ data: peticion })
      )
    }

    if (e.target.id === 'btn-close-report') {
      let reportCard = d.getElementById('modal-report')
      reportCard.remove()
    }

    if (e.target.id === 'generar-txt') {
      let descargatxt = await descargarNominaTxt({
        frecuencia: e.target.dataset.frecuencia,
        correlativo: e.target.dataset.correlativo,
      })
    }

    if (e.target.id === 'send-nom-request') {
      e.preventDefault()
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: 'Deseas realizar esta petición?',
        successFunction: enviarCalculoNomina,
        successFunctionParams: requestInfo,
        othersFunctions: [loadRequestTable, resetSelect],
      })
    }
  })

  function resetSelect() {
    let employeePayTableCard = d.getElementById('request-employee-table-card')
    if (employeePayTableCard) employeePayTableCard.remove()
    selectNomina.value = ''
    selectGrupo.value = ''
    selectFrecuencia.innerHTML = `<option value="">Seleccionar una nómina</option>`
  }
}

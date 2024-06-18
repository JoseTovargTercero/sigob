import {
  getGruposNomina,
  getNominas,
  sendCalculoNomina,
} from '../api/peticionesNomina.js'
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
  requestSelectContainerId,
  showRequestGroupBtnId,
  formId,
}) {
  // Cargar tabla de peticiones
  loadRequestTable()

  let selectGrupo = d.getElementById(selectIdGrupo)
  let selectNomina = d.getElementById(selectIdNomina)
  let requestSelectContainer = d.getElementById(requestSelectContainerId)
  let showRequestGroupBtn = d.getElementById(showRequestGroupBtnId)
  let employeePayForm = d.getElementById(formId)

  console.log(requestSelectContainer)

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
    let nomina = await getGruposNomina(e.target.value)

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

    if (e.target.id === 'send-nom-request') {
      e.preventDefault()
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: 'Deseas realizar esta petición?',
        successFunction: sendCalculoNomina,
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
  }
}

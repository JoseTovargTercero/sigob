import { getEmpleadosNomina, getNominas } from '../api/empleadosPagar.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { createTable, employeePayTableHTML } from './empleadosPagarTable.js'

const d = document
const w = window
const urlParameters = new URLSearchParams(w.location.search)
const id = urlParameters.get('id')

console.log('a')
const selectGrupo = d.getElementById('grupo')
const selectNomina = d.getElementById('nomina')
const employeePayForm = d.getElementById('employee-pay-form')

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

function validateEmployeePayForm() {
  selectGrupo.addEventListener('change', async (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })

    let nominas = await getNominas(e.target.value)

    selectNomina.innerHTML = ''

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
    let nomina = await getEmpleadosNomina(e.target.value)
    let employeePayTableCard = d.getElementById('employee-pay-table-card')
    if (employeePayTableCard) employeePayTableCard.remove()
    console.log(nomina)
    if (!nomina || nomina.empleados.length < 1) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Esta nómina no aplica para ningún empleado',
      })
      return
    }

    employeePayForm.insertAdjacentHTML(
      'beforeend',
      employeePayTableHTML({ nominaData: nomina })
    )
    createTable({ nominaData: nomina })
  })
}

validateEmployeePayForm()

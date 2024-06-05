import { getEmpleadosNomina, getNominas } from '../api/empleadosPagar.js'
import { validateInput } from '../helpers/helpers.js'
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
    selectNomina.innerHTML = "<option value=''>Selección</option>"

    nominas.forEach((nomina) => {
      let option = `<option value="${nomina}">${nomina}</option>`
      selectNomina.insertAdjacentHTML('beforeend', option)
    })
  })
  selectNomina.addEventListener('change', async (e) => {
    let nomina = await getEmpleadosNomina(e.target.value)
    let employeePayTableCard = d.getElementById('employee-pay-table-card')
    if (employeePayTableCard) employeePayTableCard.remove()
    console.log(nomina)
    if (nomina['0']) {
      employeePayForm.insertAdjacentHTML(
        'beforeend',
        employeePayTableHTML({ nominaData: nomina })
      )
      createTable({ nominaData: nomina })
    }
  })
}

validateEmployeePayForm()

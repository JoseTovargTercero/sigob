import {
  getGruposNomina,
  getNominas,
  getPeticionesNomina,
} from '../api/peticionesNomina.js'
import { tableListCard } from '../components/tabla_lista_card.js'
import { validateInput } from '../helpers/helpers.js'
import { createTable, employeePayTableHTML } from './peticionesNominaTable.js'

const d = document
const w = window

const selectGrupo = d.getElementById('grupo')
const selectNomina = d.getElementById('nomina')
const requestSelectContainer = d.getElementById('request-employee-container')
const showRequestGroupBtn = d.getElementById('show-request-group')
const closeRequestListBtn = d.getElementById('close-request-list')
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
      let requestInfo = await getPeticionesNomina()
      requestSelectContainer.classList.remove('hide')
    }

    if (e.target.id === 'close-request-list') {
      console.log('e')
      if (tableListCardElement) tableListCardElement.remove()
    }
  })
}

validateEmployeePayForm()

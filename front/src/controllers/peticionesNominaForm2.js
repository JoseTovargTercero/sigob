import { calculoNomina, getNominas } from '../api/peticionesNomina.js'
import {
  loadEmployeeList,
  nom_empleados_list_card,
} from '../components/nom_empleados_list_card.js'
import { closeModal, validateInput } from '../helpers/helpers.js'
import { FRECUENCY_TYPES } from '../helpers/types.js'
import { loadRequestTable } from './peticionesTable.js'

const d = document

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

let employeeNewStatus = []

let nominas

export async function validateRequestForm({
  btnNewRequestId,
  requestTableId,
  newRequestFormId,
  selectNominaId,
  selectGrupoId,
  selectFrecuenciaId,
}) {
  loadRequestTable()
  let requestTable = d.getElementById(requestTableId)
  let newRequestForm = d.getElementById(newRequestFormId)
  let selectNomina = d.getElementById(selectNominaId)
  let selectGrupo = d.getElementById(selectGrupoId)
  let selectFrecuencia = d.getElementById(selectFrecuenciaId)

  let listaEmpleadosFetch = await fetch(
      '/sigob/front/src/api/calculoNomina.json'
    ),
    listaEmpleados = await listaEmpleadosFetch.json()

  requestTable.insertAdjacentHTML('beforeend', nom_empleados_list_card())

  loadEmployeeList({
    listaEmpleados: listaEmpleados.informacion_empleados,
  })

  d.addEventListener('click', (e) => {
    if (e.target.id === btnNewRequestId) {
      requestTable.classList.toggle('hide')
      newRequestForm.classList.toggle('hide')
    }

    if (e.target.id === 'btn-close-employee-list-card') {
      closeModal({ modalId: 'modal-employee-list' })
    }
  })

  d.addEventListener('change', async (e) => {
    if (e.target.dataset.employeeid) {
      let id = e.target.dataset.employeeid
      let defaultValue = e.target.dataset.defaultvalue

      let oldValueIndex = employeeNewStatus.findIndex((el) => el.id === id)
      if (e.target.value === defaultValue) {
        employeeNewStatus = employeeNewStatus.filter((el) => el.id !== id)
        console.log(employeeNewStatus)
        return
      }

      if (employeeNewStatus.some((el) => el.id === id)) {
        employeeNewStatus.splice(oldValueIndex, 1, {
          id,
          value: e.target.value,
        })
      } else {
        employeeNewStatus.push({ id, value: e.target.value })
      }
      console.log(employeeNewStatus)
    }

    if (e.target === selectGrupo) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })

      nominas = await getNominas(e.target.value)
      console.log(nominas)

      selectNomina.innerHTML = ''

      if (nominas.length > 0)
        nominas.forEach((nomina) => {
          let option = `<option value="${nomina.nombre}">${
            nomina.nombre || 'Grupo de nómina vacío'
          }</option>`

          selectNomina.insertAdjacentHTML('beforeend', option)
        })
      else
        selectNomina.insertAdjacentHTML(
          'beforeend',
          `<option value="">Grupo de nómina vacío</option>`
        )
    }

    if (e.target === selectNomina) {
      if (!e.target.value) return
      fieldList.nomina = e.target.value

      fieldList.frecuencia = nominas.find(
        (nomina) => nomina.nombre === e.target.value
      ).frecuencia

      console.log(fieldList.frecuencia)

      selectFrecuencia.innerHTML = ''

      let identificadorOpciones = ''

      switch (fieldList.frecuencia) {
        case '1':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina, index) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Semana ${
                index + 1
              }</option>`
            }
          )
          break
        case '2':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina, index) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Quincena ${
                index + 1
              }</option>`
            }
          )
          break
        case '3':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break
        case '4':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break

        default:
          break
      }

      selectFrecuencia.insertAdjacentHTML('beforeend', identificadorOpciones)
    }
  })
}

import {
  getDependencyData,
  getEmployeeData,
  getJobData,
  getProfessionData,
  sendDependencyData,
  sendEmployeeData,
  updateEmployeeData,
} from '../api/empleados.js'
import {
  closeModal,
  confirmNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window
const urlParameters = new URLSearchParams(w.location.search)
const id = urlParameters.get('id')
function validateEmployeeForm({
  formElement,
  employeeInputClass,
  employeeSelectClass,
  btnId,
  btnAddId,
  fieldList = {},
  fieldListErrors = {},
  selectSearchInput,
  selectSearch,
}) {
  const btnElement = d.getElementById(btnId)
  const btnAddElement = d.getElementById(btnAddId)
  const btnDependencySave = d.getElementById('dependency-save-btn')
  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)

  const employeeInputElement = d.querySelectorAll(`.${employeeInputClass}`)
  const employeeSelectElement = d.querySelectorAll(`.${employeeSelectClass}`)

  let employeeInputElementCopy = [...employeeInputElement]
  let employeeSelectElementCopy = [...employeeSelectElement]

  const loadEmployeeData = async () => {
    let cargos = await getJobData()
    let profesiones = await getProfessionData()
    let dependencias = await getDependencyData()
    insertOptions({ input: 'cargo', data: cargos })
    insertOptions({ input: 'instruccion_academica', data: profesiones })
    insertOptions({ input: 'dependencias', data: dependencias })

    // CÓDIGO PARA OBTENER EMPLEADO EN CASO DE EDITAR
    if (id) {
      let employeeData = await getEmployeeData(id)

      // console.log(employeeData)
      employeeSelectElementCopy.forEach((select) => {
        select.value = employeeData[0][select.name]
      })

      employeeInputElementCopy.forEach((input) => {
        input.value = employeeData[0][input.name]
      })

      employeeData[0].id = employeeData[0].id_empleado

      fieldList = employeeData[0]
      console.log(fieldList, fieldListErrors)
    }
  }

  loadEmployeeData()

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  formElement.addEventListener('focusout', (e) => {
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  // selectSearchInputElement.forEach((input) => {
  //   const parentElement = input.parentNode
  //   const parentChildElement = parentElement.childNodes

  //   console.log(parentElement)
  //   parentElement.addEventListener('click', (e) => {
  //     activateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })

  //   parentElement.addEventListener('focusout', (e) => {
  //     desactivateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })
  // })

  d.addEventListener('click', (e) => {
    if (e.target === btnAddElement) {
      validateModal({
        e: e,
        btnId: btnAddElement.id,
        modalId: 'modal-dependency',
      })
    }

    if (e.target === btnDependencySave) {
      let newDependency = { dependencia: fieldList.dependencia }
      console.log(fieldList)
      if (!fieldListErrors.dependencia.value) {
        validateNewDependency({ newDependency })
      }
    }

    if (e.target === btnElement) {
      employeeSelectElementCopy.forEach((input) => {
        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })
      employeeInputElementCopy.forEach((input) => {
        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete todo el formulario antes de avanzar',
        })
      }
      delete fieldList.dependencia
      if (id) return updateEmployeeData({ data: fieldList })

      // ENVÍO DE INFORMACIÓN
      sendEmployeeData({ data: fieldList })
    }
  })
}

function insertOptions({ input, data }) {
  const selectElement = d.getElementById(`search-select-${input}`)
  selectElement.innerHTML = `<option value="">ELEGIR...</option>`
  const fragment = d.createDocumentFragment()
  data.forEach((el) => {
    const option = d.createElement('option')
    option.setAttribute('value', el.id)
    option.textContent = el.name
    fragment.appendChild(option)
  })

  selectElement.appendChild(fragment)
}

async function validateNewDependency({ newDependency }) {
  let isSend = await sendDependencyData({ newDependency })
  if (isSend) {
    getDependencyData().then((res) => {
      insertOptions({ input: 'dependencias', data: res })
    })
  }
  closeModal({ modalId: 'modal-dependency' })
}

const activateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.remove('hide')
}

const desactivateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.add('hide')
}

export { validateEmployeeForm }

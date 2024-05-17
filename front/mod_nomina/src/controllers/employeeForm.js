import {
  getCargoData,
  getDependenciasData,
  getProfesionesData,
  sendDependencyData,
  sendEmployeeData,
} from '../api/empleados.js'
import {
  closeModal,
  confirmNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
function validateEmployeeForm({
  formElement,
  employeeInputClass,
  btnId,
  btnAddId,
  fieldList = {},
  selectSearchInput,
  selectSearch,
}) {
  const btnElement = d.getElementById(btnId)
  const btnAddElement = d.getElementById(btnAddId)
  const btnDependencySave = d.getElementById('dependency-save-btn')
  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)
  const employeeInputElement = d.querySelectorAll(`.${employeeInputClass}`)

  let cargoData = []

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains('employee-input')) {
      fieldList = validateInput({
        e,
        fieldList,
        type: fieldList.errors[e.target.name].type,
      })
    }
    if (e.target.classList.contains('employee-select')) {
      fieldList = validateInput({
        e,
        fieldList,
        type: fieldList.errors[e.target.name].type,
      })
    }
    console.log(fieldList)
  })

  getCargoData().then((res) => {
    insertOptions({ input: 'cargo', data: res })
  })

  getProfesionesData().then((res) =>
    insertOptions({ input: 'instruccion_academica', data: res })
  )
  getDependenciasData().then((res) =>
    insertOptions({ input: 'dependencias', data: res })
  )

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
    if (e.target === btnElement) {
      // if (Object.values(fieldList.errors).some((el) => el.value)) {
      //   return confirmNotification({
      //     type: NOTIFICATIONS_TYPES.fail,
      //     message: 'Complete todo el formulario antes de avanzar',
      //   })
      // }
      delete fieldList.errors
      sendEmployeeData({ data: fieldList })
    }

    if (e.target === btnDependencySave) {
      let newDependency = { dependencia: fieldList.dependencia }
      console.log(fieldList)
      if (!fieldList.errors.dependencia.value) {
        validateNewDependency({ newDependency })
      }
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
    getDependenciasData().then((res) => {
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

import {
  getCargoData,
  getDependenciasData,
  getProfesionesData,
  sendEmployeeData,
} from '../api/empleados.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
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
  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)

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

  btnElement.addEventListener('click', () => {
    // if (Object.values(fieldList.errors).some((el) => el.value)) {
    //   return confirmNotification({
    //     type: NOTIFICATIONS_TYPES.fail,
    //     message: 'Complete todo el formulario antes de avanzar',
    //   })
    // }

    delete fieldList.errors

    sendEmployeeData({ data: fieldList })
  })
}

function insertOptions({ input, data }) {
  const selectElement = d.getElementById(`search-select-${input}`)
  const fragment = d.createDocumentFragment()
  data.forEach((el) => {
    const option = d.createElement('option')
    option.setAttribute('value', el.id)
    option.textContent = el.name
    fragment.appendChild(option)
  })

  selectElement.appendChild(fragment)
}

const activateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.remove('hide')
}

const desactivateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.add('hide')
}

export { validateEmployeeForm }

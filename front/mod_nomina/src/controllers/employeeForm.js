import { getCargoData } from '../api/empleados.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
function validateEmployeeForm({
  formElement,
  employeeInputClass,
  btnId,
  fieldList = {},
}) {
  const btnElement = d.getElementById(btnId)

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    validateInput({ e, fieldList, type: fieldList.errors[e.target.name].type })
  })

  d.addEventListener('focusin', (e) => {
    if (e.target.name === 'cargo') {
      getCargoData().then((res) =>
        validateSearchSelect({ element: e.target, data: res })
      )
    }
  })
  d.addEventListener('focusout', (e) => {})

  btnElement.addEventListener('click', () => {
    if (Object.values(fieldList.errors).some((el) => el.value)) {
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Complete todo el formulario antes de avanzar',
      })
    }
  })
}

function validateSearchSelect({ element, data }) {
  if (document.getElementById(`search-select-${element.name}`)) {
    selectElement.classList.remove('hide')
    return
  }
  const selectElement = d.createElement('select')
  selectElement.setAttribute('size', 5)
  selectElement.id = `search-select-${element.name}`

  const fragment = d.createDocumentFragment()
  data.forEach((el) => {
    const option = d.createElement('option')
    option.setAttribute('value', el.cod_cargo)
    option.textContent = el.cargo
    fragment.appendChild(option)
  })
  selectElement.appendChild(fragment)

  element.insertAdjacentElement('afterend', selectElement)
  console.log('hola')
}

export { validateEmployeeForm }

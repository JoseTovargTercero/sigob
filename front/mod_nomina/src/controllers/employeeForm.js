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
  btnElement.addEventListener('click', () => {
    if (Object.values(fieldList.errors).some((el) => el.value)) {
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Complete todo el formulario antes de avanzar',
      })
    }
  })
  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    validateInput({ e, fieldList, type: fieldList.errors[e.target.name].type })
  })
}

export { validateEmployeeForm }

import { NOTIFICATIONS_TYPES } from './types.js'
import { regularExpressions } from './regExp.js'

const d = document

function validateInput({ target, fieldList = {}, fieldListErrors = {}, type }) {
  let value

  if (target) {
    value = target.value
  }

  try {
    if (type === 'reset') {
      let inputErrors = d.querySelectorAll('.input-error')
      let inputErrorsMessages = d.querySelectorAll('.input-error-message')
      inputErrors.forEach((input) => {
        input.classList.remove('input-error')
      })
      inputErrorsMessages.forEach((inputMessage) => {
        inputMessage.remove()
      })
      return
    }

    if (type === 'matrixCell') {
      let isFloat = regularExpressions.FLOAT.test(value)

      if (!isFloat || value <= 0 || value === undefined) {
        return target.classList.add('input-error')
      } else {
        return target.classList.remove('input-error')
      }
    }
    let message = fieldListErrors[target.name].message
      ? fieldListErrors[target.name].message
      : 'Contenido inválido'
    let errorValue = fieldListErrors[target.name].value

    // VALIDAR QUE EL INPUT NO ESTÉ VACÍO
    if (!target.checkValidity() || value === '') {
      target.classList.add('input-error')
      fieldListErrors[target.name].value = true
      errorMessage(target, message)
      fieldList = {
        ...fieldList,
        [target.name]: target.value,
      }
    } else {
      fieldListErrors[target.name].value = false
      target.classList.remove('input-error')
      errorMessage(target, message)
      fieldList = {
        ...fieldList,
        [target.name]: target.value,
      }
    }

    // VALIDACIÓN PARA CUENTAS BANCARIAS Y QUE CORRESPONDA AL BANCO SELECCIONADO

    if (type === 'cuenta_bancaria') {
      let isNumber = regularExpressions.NUMBER.test(value)
      let secuenciaBanco = fieldList['banco']
      let isBank = regularExpressions.NUMBER.test(secuenciaBanco)
      let numeroCuenta = fieldList['cuenta_bancaria']
      let isBankSelected = numeroCuenta.startsWith(secuenciaBanco)

      if (!isBank) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, `Elija un banco`)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else if (!isBankSelected) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, `La cuenta debe empezar por ${secuenciaBanco}`)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else if (!isNumber) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)

        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else if (!numeroCuenta.startsWith(secuenciaBanco)) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, `El numero debe empezar por ${secuenciaBanco}`)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'number') {
      let isNumber = regularExpressions.NUMBER.test(value)

      if (!isNumber || value <= 0) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'cedula') {
      let isNumber = regularExpressions.NUMBER.test(value)

      if (!isNumber || value <= 0) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    // VALIDACIÓN NUMÉRICA IGUAL A 0 O MAYOR

    if (type === 'number2') {
      let isNumber = regularExpressions.NUMBER.test(value)

      if (!isNumber || value < 0) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'text') {
      let isText = regularExpressions.TEXT.test(value)
      if (!isText) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'email') {
      let isText = regularExpressions.EMAIL.test(value)
      if (!isText) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'password') {
      let isText = regularExpressions.PASSWORD.test(value)
      if (!isText) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else if (value.length < 8) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, 'Mínimo 8 carácteres')
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'confirm_password') {
      if (value !== fieldList.password) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }

    if (type === 'date') {
      if (!validarFecha(value)) {
        target.classList.add('input-error')
        fieldListErrors[target.name].value = true
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      } else {
        fieldListErrors[target.name].value = false
        target.classList.remove('input-error')
        errorMessage(target, message)
        fieldList = {
          ...fieldList,
          [target.name]: target.value,
        }
      }
    }
  } catch (error) {
    console.error(error)
    return fieldList
  }

  return fieldList
}

const convertStringOrNumber = (string) =>
  isNaN(Number(string)) ? string : Number(string)

function validarFecha(valorInput) {
  // Convertir el valor del input a una fecha
  var fechaIngresada = new Date(valorInput)
  // Obtener la fecha actual
  var fechaActual = new Date()
  // Comprobar si el valor ingresado es una fecha válida
  if (isNaN(fechaIngresada)) return false
  // Comprobar si la fecha ingresada es mayor que la fecha actual
  if (fechaIngresada > fechaActual) return false
  // Si pasa ambas validaciones, la fecha es válida y no es mayor que la fecha actual
  return true
}

// Mensaje de error para inputs
const errorMessage = (target, message) => {
  // Verificar si el mensaje de error existe:
  const errorSpan = document.getElementById(`${target.name}-error-message`)
  if (errorSpan) {
    errorSpan.remove()
  }

  if (target.classList.contains('input-error')) {
    const errorSpan = `<span id='${target.name}-error-message' class='input-error-message slide-up-animation'>${message}</span>`

    // Añadir error al input
    target.insertAdjacentHTML('afterend', errorSpan)
  }
}

// FUNCIÓN PARA MODALES

function validateModal({ e, btnId, modalId }) {
  const modalElement = d.getElementById(modalId)

  if (e.target.matches(`#${btnId}`)) {
    if (modalElement.classList.contains('hide')) {
      modalElement.classList.remove('hide')
    } else {
      modalElement.classList.add('hide')
    }
  }
}

function closeModal({ modalId }) {
  const modalElement = d.getElementById(modalId)
  modalElement.classList.add('hide')
}

function openModal({ modalId }) {
  const modalElement = d.getElementById(modalId)
  modalElement.classList.remove('hide')
}

// FUNCIÓNES PARA LOADERS

function showLoader(loaderId) {
  let loader = d.getElementById('cargando')
  loader.style.display = 'grid'
  loader.focus()
}
function hideLoader(loaderId) {
  let loader = d.getElementById('cargando')
  loader.style.display = 'none'
}

function confirmNotification({
  type,
  successFunction,
  successFunctionParams,
  message = '',
  othersFunctions = [],
}) {
  // Manejar las acciones de las notificaciones
  const notificationAction = async ({
    successFunction,
    successFunctionParams,
  }) => {
    if (successFunctionParams) {
      await successFunction(successFunctionParams)
      if (othersFunctions.length > 0) {
        othersFunctions.forEach((functions) => {
          functions()
        })
      }

      return
    }
    if (successFunction) {
      await successFunction()
      if (othersFunctions.length > 0) {
        othersFunctions.forEach((functions) => {
          functions()
        })
      }

      return
    }

    return
  }

  if (type === NOTIFICATIONS_TYPES.send)
    return Swal.fire({
      title: 'Alerta!',
      text: message || '¿Estás seguro de esta acción?',
      icon: 'warning',
      showConfirmButton: true,
      showDenyButton: true,
      confirmButtonText: 'ENVIAR',
      denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed) {
        notificationAction({
          successFunction,
          successFunctionParams,
          othersFunctions,
        })

        return result.isConfirmed
      }
    })

  if (type === NOTIFICATIONS_TYPES.done) {
    return Swal.fire({
      title: 'Envio realizado',
      text: message || 'Acción realizada',
      icon: 'success',
      showConfirmButton: true,
      // showDenyButton: true,
      confirmButtonText: 'ACEPTAR',
      denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed) {
        notificationAction({ successFunction, successFunctionParams })
        return result.isConfirmed
      }
    })
  }
  if (type === NOTIFICATIONS_TYPES.fail) {
    return Swal.fire({
      title: 'Hubo un error',
      text: message || 'Error al procesar',
      icon: 'error',
      showConfirmButton: true,
      // showDenyButton: true,
      confirmButtonText: 'ACEPTAR',
      // denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed)
        notificationAction({ successFunction, successFunctionParams })
    })
  }

  if (type === NOTIFICATIONS_TYPES.delete) {
    return Swal.fire({
      title: '¡Alerta!',
      text: message || '¿Estás seguro de eliminar este registro?',
      icon: 'warning',
      showConfirmButton: true,
      showDenyButton: true,
      confirmButtonText: 'ELIMINAR',
      denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed) {
        notificationAction({ successFunction, successFunctionParams })
        return true
      }
    })
  }
}

const validateStatusText = ({ value, confirmText, NegativeText }) => {
  return value ? confirmText : NegativeText
}

export {
  validateInput,
  validateModal,
  closeModal,
  openModal,
  showLoader,
  hideLoader,
  confirmNotification,
  errorMessage,
  validateStatusText,
}

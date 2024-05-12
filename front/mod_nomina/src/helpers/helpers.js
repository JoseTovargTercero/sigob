import { NOTIFICATIONS_TYPES } from './types.js'

const d = document

function validateInput(e, fieldList = {}) {
  //   inputElement.addEventListener('input', function() {
  //     const value = parseFloat(this.value);

  //     if (!isNaN(value) && (value * 100 % 1 === 0)) {
  //         this.setCustomValidity('');
  //     } else {
  //         this.setCustomValidity('Ingrese un número válido con al menos 2 decimales.');
  //     }
  // });

  let errorValue = fieldList.errors[e.target.name].value
  let message = fieldList.errors[e.target.name].message
  let inputValue = e.target.value
  if (Object.values(fieldList.errors).every((el) => el.value)) {
  }

  // Validar si el input es válido desde el html
  if (!e.target.checkValidity()) {
    e.target.classList.add('input-error')
    fieldList.errors[e.target.name].value = true
    errorMessage(e.target, message)
  } else {
    e.target.classList.remove('input-error')
    fieldList.errors[e.target.name].value = false
    errorMessage(e.target, message)
  }

  fieldList = {
    ...fieldList,
    [e.target.name]: convertStringOrNumber(e.target.value),
  }

  return fieldList
}

const convertStringOrNumber = (string) =>
  isNaN(Number(string)) ? string : Number(string)

// Mensaje de error para inputs
const errorMessage = (target, message) => {
  // Validaciones personalizadas
  // if(type !== ""){
  //   if(type === text){

  //   }
  // }

  // Verificar si el mensaje de error existe:
  const errorSpan = document.getElementById(`${target.name}-error-message`)
  if (errorSpan) {
    errorSpan.remove()
  }

  if (target.classList.contains('input-error')) {
    const errorSpan = document.createElement('span')
    errorSpan.classList.add('input-error-message')
    errorSpan.id = `${target.name}-error-message`
    errorSpan.textContent = message
    target.parentNode.insertBefore(errorSpan, target.nextSibling)
  } else {
    const errorSpan = document.getElementById(`${target.name}-error-message`)
    if (errorSpan) {
      errorSpan.remove()
    }
  }
}

function validateModal(e, btnId, modalClass) {
  const modalElement = d.querySelector(`.${modalClass}`)
  if (e.target.matches(`#${btnId}`) || e.target.matches(`#${btnId} *`)) {
    if (modalElement.classList.contains('hide')) {
      modalElement.classList.remove('hide')
    } else {
      modalElement.classList.add('hide')
    }
  }
}

function confirmNotification({
  type,
  successFunction,
  successFunctionParams,
  message = '',
}) {
  // Manejar las acciones de las notificaciones
  const notificationAction = ({ successFunction, successFunctionParams }) => {
    if (successFunctionParams) {
      return successFunction(successFunctionParams)
    }
    if (successFunction) {
      successFunction()
    }
    return
  }

  if (type === NOTIFICATIONS_TYPES.send)
    return Swal.fire({
      title: 'Alerta!',
      text: '¿Estás seguro de esta acción?',
      icon: 'warning',
      showConfirmButton: true,
      showDenyButton: true,
      confirmButtonText: 'ENVIAR',
      denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed)
        notificationAction({ successFunction, successFunctionParams })
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
      if (result.isConfirmed)
        notificationAction({ successFunction, successFunctionParams })
    })
  }

  if (type === NOTIFICATIONS_TYPES.delete) {
    return Swal.fire({
      title: '¡Alerta!',
      text: '¿Estás seguro de eliminar este registro?',
      icon: 'warning',
      showConfirmButton: true,
      showDenyButton: true,
      confirmButtonText: 'ELIMINAR',
      denyButtonText: 'CANCELAR',
    }).then((result) => {
      if (result.isConfirmed)
        notificationAction({ successFunction, successFunctionParams })
    })
  }
}

export { validateInput, validateModal, confirmNotification }

import { validateInput } from '../front/mod_nomina/src/helpers/helpers.js'
import {
  validateEmail,
  validateNewPassword,
  validateToken,
} from './assets/api/recoveryFormApi.js'

const d = document

const recoveryForm = d.getElementById('recovery-form')
const recoveryFormPart1 = d.getElementById('recovery-form-part-1')
const recoveryFormPart2 = d.getElementById('recovery-form-part-2')
const recoveryFormPart3 = d.getElementById('recovery-form-part-3')

const nextBtn = d.getElementById('btn-next')
const previusBtn = d.getElementById('btn-previus')
const consultBtn = d.getElementById('btn-consult')
let formFocus = 1

let fieldList = {
  email: 'corro@correo.com',
  password: '',
  confirm_password: '',
  token: '123456',
}
let fieldListErrors = {
  email: {
    type: 'email',
    message: 'Introduzca un email correcto',
    value: true,
  },
  token: {
    message: 'No puede haber un token vacío',
    value: true,
  },
  password: {
    type: 'password',
    message: 'No cumple los requisitos',
    value: true,
  },
  confirm_password: {
    type: 'confirm_password',
    message: 'Contraseñas no coinciden',
    value: true,
  },
}

recoveryForm.addEventListener('submit', (e) => {
  e.preventDefault()
})

recoveryForm.addEventListener('input', (e) => {
  fieldList = validateInput({
    target: e.target,
    fieldList,
    fieldListErrors,
    type: fieldListErrors[e.target.name].type,
  })
  console.log(fieldList)

  if (e.target.name === 'password') {
    validatePassword(e.target.value)
  }
})

d.addEventListener('click', (e) => {
  if (e.target === consultBtn) {
    if (formFocus === 1) {
      validateInput({
        target: recoveryForm.email,
        fieldList,
        fieldListErrors,
        type: fieldListErrors.email.type,
      })

      if (fieldListErrors.email.value) return

      // VALIDAR SI EL EMAIL ES VÁLIDO Y EXISTE
      validateEmail(fieldList.email).then((res) => {
        if (res) {
          recoveryFormPart1.classList.add('d-none')
          recoveryFormPart2.classList.remove('d-none')
          previusBtn.classList.remove('d-none')

          nextBtn.classList.remove('d-none')
          consultBtn.classList.add('d-none')
          return formFocus++
        }
      })
    }
  }

  // SIGUIENTE FORMULARIO

  if (e.target === nextBtn) {
    if (formFocus === 2) {
      validateInput({
        target: recoveryForm.token,
        fieldList,
        fieldListErrors,
        type: fieldListErrors.token.type,
      })

      if (fieldListErrors.token.value) return

      //  VALIDAR SI EL TOKEN ES CORRECTO
      validateToken(fieldList.email, fieldList.token).then((res) => {
        if (res) {
          recoveryFormPart2.classList.add('d-none')
          recoveryFormPart3.classList.remove('d-none')

          formFocus++
          e.target.textContent = 'Guardar'
        }
      })

      return
    }
    if (formFocus === 3) {
      validateInput({
        target: recoveryForm.password,
        fieldList,
        fieldListErrors,
        type: fieldListErrors.password.type,
      })

      validateInput({
        target: recoveryForm.confirm_password,
        fieldList,
        fieldListErrors,
        type: fieldListErrors.confirm_password.type,
      })

      validatePassword(fieldList.password)

      // console.log(fieldListErrors)
      if (
        fieldListErrors.password.value ||
        fieldListErrors.confirm_password.value
      )
        return

      // ENVIAR NUEVA CONTRASEÑA
      validateNewPassword(
        fieldList.password,
        fieldList.confirm_password,
        fieldList.token
      )
    }
  }
  if (e.target === previusBtn) {
    if (formFocus === 1) return

    if (formFocus === 2) {
      nextBtn.classList.add('d-none')
      previusBtn.classList.add('d-none')
      consultBtn.classList.remove('d-none')
      recoveryFormPart2.classList.add('d-none')
      recoveryFormPart1.classList.remove('d-none')
      formFocus--
      return
    }
    if (formFocus === 3) {
      recoveryFormPart3.classList.add('d-none')
      recoveryFormPart2.classList.remove('d-none')
      nextBtn.textContent = 'Siguiente'
      formFocus--
      return
    }
  }
})

const listValidateMayus = d.querySelector('.password-validations-mayus')
const listValidateNumber = d.querySelector('.password-validations-number')
const listValidateEspecial = d.querySelector('.password-validations-especial')
const listValidateLength = d.querySelector('.password-validations-length')

function validatePassword(password) {
  const hasMayus = /^(?=.*[A-Z])/.test(password)
  const hasNumber = /^(?=.*\d)/.test(password)
  const hasEspecial = /^(?=.*[!@#$%^&*])/.test(password)
  const hasLength = password.length >= 8

  if (hasMayus) {
    listValidateMayus.classList.add('valid')
    listValidateMayus.classList.remove('invalid')
  } else {
    listValidateMayus.classList.add('invalid')
    listValidateMayus.classList.remove('valid')
  }

  if (hasNumber) {
    listValidateNumber.classList.add('valid')
    listValidateNumber.classList.remove('invalid')
  } else {
    listValidateNumber.classList.add('invalid')
    listValidateNumber.classList.remove('valid')
  }

  if (hasEspecial) {
    listValidateEspecial.classList.add('valid')
    listValidateEspecial.classList.remove('invalid')
  } else {
    listValidateEspecial.classList.add('invalid')
    listValidateEspecial.classList.remove('valid')
  }

  if (hasLength) {
    listValidateLength.classList.add('valid')
    listValidateLength.classList.remove('invalid')
  } else {
    listValidateLength.classList.add('invalid')
    listValidateLength.classList.remove('valid')
  }

  return {
    hasMayus,
    hasNumber,
    hasEspecial,
    hasLength,
  }
}

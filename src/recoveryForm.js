import {
  confirmNotification,
  validateInput,
} from '../front/mod_nomina/src/helpers/helpers.js'
const recoveryUrl = '../../sigob/back/mod_global/glob_recuperar_back.php'

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
  email: '',
  nuevaContraseña: '',
  confirmarContraseña: '',
  token: '',
}
let fieldListErrors = {
  email: {
    type: 'email',
    mesasage: 'Introduzca un email correcto',
    value: true,
  },
  token: {
    mesasage: 'No puede haber un token vacío',
    value: true,
  },
  nuevaContraseña: {
    type: 'password',
    mesasage: 'Introduzca un email correcto',
    value: true,
  },
  confirmarContraseña: {
    type: 'password',
    mesasage: '',
    value: true,
  },
}

recoveryForm.addEventListener('submit', (e) => {
  e.preventDefault()
})

// recoveryFormPart1.addEventListener('input', (e) => {
//   if (formFocus === 1) {
//     validateInput({
//       target: e.target,
//       fieldList,
//       fieldListErrors,
//       type: fieldListErrors[e.target.name].type,
//     })
//   }
// })

d.addEventListener('click', (e) => {
  if (e.target === consultBtn) {
    // LÓGICA PARA CONSULTAR CORREO
    // IF VALIDATECORREO()
    // SI EL CORREO ESTÁ REGISTRADO, MOSTRAR BOTON DE SIGUIENTE
    // SINO, DAR MENSAJE DE ERROR

    if (formFocus === 1) {
      // Validar si la estructura del Email es correcta
      validateInput({
        target: recoveryForm.email,
        fieldList,
        fieldListErrors,
        type: fieldListErrors.email.type,
      })

      if (fieldListErrors.email.value) return

      validateEmail(recoveryForm.email.value).then((res) => {
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

      // LÓGICA PARA VALIDAR TOKKEN
      // IF VALIDATETOKEN()
      // SI EL TOKEN ES VALIDADO SE PASA AL SIGUIENTE
      // SINO DAR UN MENSAJE DE ERROR

      validateToken(recoveryForm.email.value, recoveryForm.token.value).then(
        (res) => {
          if (res) {
            recoveryFormPart2.classList.add('d-none')
            recoveryFormPart3.classList.remove('d-none')

            formFocus++
            e.target.textContent = 'Guardar'
          }
        }
      )

      return
    }
    if (formFocus === 3) {
      console.log(3)

      // FUNCIÓN PARA GUARDAR NUEVA CONTRASEÑA
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
      console.log(3)
      recoveryFormPart3.classList.add('d-none')
      recoveryFormPart2.classList.remove('d-none')
      nextBtn.textContent = 'Siguiente'
      formFocus--
      return
    }
  }
})

const validateEmail = async (email) => {
  let data = new FormData()

  data.append('accion', 'consulta')
  data.append('email', email)
  try {
    let res = await fetch(recoveryUrl, {
      method: 'POST',
      body: data,
    })

    let json = await res.json()

    if (json.valid) {
      toast_s('success', json.response)
      return true
    } else {
      toast_s('error', json.response)
      return false
    }
  } catch (e) {
    return toast_s('error', 'Error: verifique sus credenciales')
  }
}

const validateToken = async (email, token) => {
  let data = new FormData()

  data.append('accion', 'token')
  data.append('token', token)
  data.append('email', email)
  try {
    let res = await fetch(recoveryUrl, {
      method: 'POST',
      body: data,
    })

    let text = await res.text()
    console.log(text)
    let json = await res.json()
    if (json.valid) {
      toast_s('success', json.response)
      return true
    } else {
      toast_s('error', json.response)
      return false
    }
  } catch (e) {
    return toast_s('error', 'Error: verifique sus credenciales')
  }
}

const validateNewPassword = async (password, confirmPassword, token) => {
  let data = new FormData()

  data.append('accion', 'pass')
  data.append('token', token)
  data.append('password', password)
  data.append('confirm_password', confirmPassword)
  try {
    let res = await fetch(recoveryUrl, {
      method: 'POST',
      body: data,
    })

    let text = await res.text()
    console.log(text)
    let json = await res.json()
    if (json.valid) {
      toast_s('success', json.response)
      return true
    } else {
      toast_s('error', json.response)
      return false
    }
  } catch (e) {
    return toast_s('error', 'Error: verifique sus credenciales')
  }
}

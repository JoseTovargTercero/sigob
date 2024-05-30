import { validateInput } from '../front/mod_nomina/src/helpers/helpers.js'

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
    type: 'email',
    mesasage: 'Introduzca un email correcto',
    value: true,
  },
  confirmarContraseña: {
    type: 'email',
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

      recoveryFormPart1.classList.add('d-none')
      recoveryFormPart2.classList.remove('d-none')
      previusBtn.classList.remove('d-none')

      nextBtn.classList.remove('d-none')
      consultBtn.classList.add('d-none')
      return formFocus++
    }
    formFocus++
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

      recoveryFormPart2.classList.add('d-none')
      recoveryFormPart3.classList.remove('d-none')

      // LÓGICA PARA VALIDAR TOKKEN
      // IF VALIDATETOKEN()
      // SI EL TOKEN ES VALIDADO SE PASA AL SIGUIENTE
      // SINO DAR UN MENSAJE DE ERROR

      formFocus++
      e.target.textContent = 'Guardar'
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

const searchEmail = async (email) => {
  // try {
  //   let res = await fetch('consultar_correo.php', { method: 'POST' })
  //   if (!res.ok) return false
  //   else return true
  // } catch (e) {
  //   return confirmNotification({
  //     type: NOTIFICATIONS_TYPES.fail,
  //     message: 'Error al obtener empleados',
  //   })
  // }
}

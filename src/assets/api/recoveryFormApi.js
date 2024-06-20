import { confirmNotification } from '../../../front/src/helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../../../front/src/helpers/types.js'

const recoveryUrl = '../../../../sigob/back/mod_global/glob_recuperar_back.php'

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
    console.log(e)
    return toast_s('error', 'Error: verifique susss credenciales')
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

    let json = await res.json()
    console.log(json)
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

  console
  try {
    let res = await fetch(recoveryUrl, {
      method: 'POST',
      body: data,
    })

    let json = await res.json()
    if (json.valid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.response,
      })
      setTimeout(() => {
        location.assign('/sigob')
      }, 2000)
      return true
    } else {
      toast_s('error', json.response)
      return false
    }
  } catch (e) {
    return toast_s('error', 'Error: verifique sus credenciales')
  }
}

export { validateEmail, validateNewPassword, validateToken }

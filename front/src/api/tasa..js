import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const tasaUrl = `${APP_URL}${config.MODULE_NAMES.GLOBAL}sistema_global/tasa.php`

const obtenerTasa = async () => {
  try {
    let res = await fetch(tasaUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()
    // console.log(json)

    if (json.success) {
      return json.success
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tasa',
    })
  }
}

const crearTasa = async () => {
  try {
    let res = await fetch(tasaUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'insertar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    let clone = res.clone()
    let text = await clone.text()
    console.log(text)

    const json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar tasa',
    })
  }
}

const obtenerHistorialTasa = async () => {
  try {
    let res = await fetch(tasaUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'historial' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    let clone = res.clone()
    let text = await clone.text()
    // console.log(text)

    const json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success
    }
    if (json.error) {
      // toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar tasa',
    })
  }
}

const actualizarTasa = async () => {
  showLoader()
  try {
    let res = await fetch(tasaUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'actualizar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    let clone = res.clone()
    let text = await clone.text()
    console.log(text)

    const json = await res.json()
    console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })

      return json.success
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al actualizar tasa',
    })
  } finally {
    hideLoader()
  }
}

const actualizarTasaManual = async ({ informacion }) => {
  showLoader()
  try {
    let res = await fetch(tasaUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'actualizar',
        informacion: informacion,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    let clone = res.clone()
    let text = await clone.text()
    console.log(text)

    const json = await res.json()
    console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })

      return json.success
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al actualizar tasa manualmente',
    })
  } finally {
    hideLoader()
  }
}

export {
  obtenerTasa,
  crearTasa,
  actualizarTasa,
  obtenerHistorialTasa,
  actualizarTasaManual,
}

import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const getMovimientosUrl =
  '../../../../sigob/back/modulo_nomina/nom_movimientos_datos.php'

const getRegConMovimientosUrl =
  '../../../../sigob/back/modulo_registro_control/regcon_movimientos_datos.php'

const updateRegConMovimientosUrl =
  '../../../../sigob/back/modulo_registro_control/regcon_movimientos_datos.php'

const getMovimientos = async () => {
  try {
    let res = await fetch(getMovimientosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      return json
    }

    if (json.error) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener movimientos',
    })
  }
}

const getMovimiento = async (id) => {
  try {
    let res = await fetch(`${getMovimientosUrl}?id="${id}"`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success[0]
    }

    if (json.error) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener movimiento',
    })
  }
}

const getRegConMovimientos = async () => {
  try {
    let res = await fetch(getRegConMovimientosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success
    }

    if (json.error) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener movimientos',
    })
  }
}

const getRegConMovimiento = async (id) => {
  try {
    let res = await fetch(`${getRegConMovimientosUrl}?id="${id}"`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success[0]
    }

    if (json.error) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener movimiento',
    })
  }
}

const updateRegConMovimiento = async ({ accion, informacion }) => {
  try {
    let res = await fetch(updateRegConMovimientosUrl, {
      method: 'POST',
      body: { accion, informacion },
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }

    if (json.error) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener movimiento',
    })
  }
}

export {
  getMovimientos,
  getMovimiento,
  getRegConMovimientos,
  getRegConMovimiento,
  updateRegConMovimiento,
}

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

const rechazarPeticionUrl =
  '../../../../sigob/back/modulo_registro_control/regcon_peticion_rechazar.php'

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

const getRegConMovimientos = async ({ id_nomina }) => {
  try {
    let res = await fetch(`${getRegConMovimientosUrl}?id_nomina="${id_nomina}"`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let json = await res.json()
    console.log(json)
    if (json.success) {
      return json.success
    }

    if (json.error) {
      toastNotification({
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

const getRegConMovimiento = async (id, id_nomina) => {
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

const rechazarPeticion = async ({ peticion, movimientos, correcciones }) => {
  // PETICION: ID DE PETICION RECHAZADA + CORRECION
  // MOVIMIENTOS ES UN ARRAY DE ID CON LOS MOVIMIENTOS, SE LES CAMBIARÁ SU STATUS
  // CORRECCIONES, SERÁ UN ARRAY DE ARRAYS, DONDE EL INDICE 0 SERÁ EL ID DEL MOVIMIENTO A CORREGIR, Y EL INDICE 1, LA DESCRIPCIÓN

  try {
    let res = await fetch(rechazarPeticionUrl, {
      method: 'POST',
      body: JSON.stringify({ peticion, movimientos, correcciones }),
    })

    console.log({ peticion, correcciones, movimientos })

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
  rechazarPeticion,
}

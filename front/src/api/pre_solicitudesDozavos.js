import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const solicitudesDozavosUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_solicitud_dozavos.php'

const getSolicitudesDozavos = async (id) => {
  showLoader()
  try {
    let res
    if (id)
      res = await fetch(solicitudesDozavosUrl, {
        method: 'POST',
        body: JSON.stringify({ accion: 'consulta_id', id }),
      })
    else
      res = await fetch(solicitudesDozavosUrl, {
        method: 'POST',
        body: JSON.stringify({ accion: 'consulta' }),
      })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    console.log(json)

    if (id) {
      return json.success
    }

    if (json.success) {
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
      message: 'Error al obtener solicitudes',
    })
  } finally {
    hideLoader()
  }
}

const deleteSolicitudDozavo = async (id) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'rechazar', id }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener solicitudes',
    })
  } finally {
    hideLoader()
  }
}

export { getSolicitudesDozavos, deleteSolicitudDozavo }

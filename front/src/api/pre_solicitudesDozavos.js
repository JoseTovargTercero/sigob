import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const solicitudesDozavosUrl = `${APP_URL}${config.MODULE_NAMES.EJECUCION}pre_solicitud_dozavos.php`
// const solicitudesDozavosUrl = `${APP_URL}${config.MODULE_NAMES.EJECUCION}pre_solicitud_dozavos.php`
// const solicitudesDozavosUrl =
// '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_solicitud_dozavos.php'

const getSolicitudesDozavos = async (id_ejercicio) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta', id_ejercicio }),
    })

    // console.log(res)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()
    // const text = await clone.text()

    // console.log(text)

    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
      return
    }

    if (json.success) {
      return json.success
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return
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

const getSolicitudDozavos = async (id) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta_id', id }),
    })
    // console.log(res)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()
    // const text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)

    if (id) {
      if (json.error) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: json.error,
        })
        return
      }

      return json.success
    }

    if (json.success) {
      return json.success
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return
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

const registrarSolicitudDozavo = async (data) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'registrar', ...data }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()
    // const text = await clone.text()

    // console.log(text)
    const json = await res.json()

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar solicitud de dozavo',
    })
  } finally {
    hideLoader()
  }
}

const aceptarDozavo = async (id, codigo) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'gestionar',
        id,
        accion_gestion: 'aceptar',
        codigo,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()
    const text = await clone.text()

    console.log(text)
    const json = await res.json()
    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al aceptar solicitud solicitudes',
    })
  } finally {
    hideLoader()
  }
}

const rechazarDozavo = async (id, codigo) => {
  showLoader()
  try {
    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'gestionar',
        id,
        accion_gestion: 'rechazar',
        codigo,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()
    const text = await clone.text()

    console.log(text)
    const json = await res.json()

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al rechazar solicitud solicitudes',
    })
  } finally {
    hideLoader()
  }
}

const entregarSolicitud = async (id) => {
  showLoader()
  try {
    console.log(id)

    let res = await fetch(solicitudesDozavosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'entregar',
        id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()
    const text = await clone.text()

    console.log(text)
    const json = await res.json()

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al rechazar solicitud solicitudes',
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

export {
  getSolicitudesDozavos,
  getSolicitudDozavos,
  deleteSolicitudDozavo,
  registrarSolicitudDozavo,
  aceptarDozavo,
  rechazarDozavo,
  entregarSolicitud,
}

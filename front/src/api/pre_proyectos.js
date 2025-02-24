import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { config, APP_URL } from './urlConfig.js'

const proyectosUrl = `${APP_URL}${config.MODULE_NAMES.EJECUCION}pre_proyectos_creditos.php`

const getProyectos = async (id_ente, id_ejercicio) => {
  showLoader()
  try {
    let res = await fetch(proyectosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta', id_ejercicio }),
    })

    const clone = res.clone()

    let text = await clone.text()
    console.log(text)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    console.log(json)
    if (json.hasOwnProperty('success')) {
      return json.success
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return json
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener creditos',
    })
  } finally {
    hideLoader()
  }
}

const getProyecto = async () => {
  showLoader()
  try {
    let res = await fetch(proyectosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta_id', id }),
    })

    const clone = res.clone()

    let text = await clone.text()
    console.log(text)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    console.log(json)
    if (json.hasOwnProperty('success')) {
      return json.success
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return json
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener crédito',
    })
  } finally {
    hideLoader()
  }
}

export { getProyecto, getProyectos }

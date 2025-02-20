import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { config, APP_URL } from './urlConfig.js'

const entesPlanOperativoUrl = `${APP_URL}${config.MODULE_NAMES.FORMULACION}form_plan_operativo.php`

const getEntePlanOperativo = async (id_ente, id_ejercicio) => {
  showLoader()
  try {
    let res = await fetch(entesPlanOperativoUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta', id_ente, id_ejercicio }),
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
      message: 'Error al planes operativos del ente',
    })
  } finally {
    hideLoader()
  }
}

const getEntePlanOperativos = async (id_ejercicio) => {
  showLoader()
  try {
    let res = await fetch(entesPlanOperativoUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta_todos', id_ejercicio }),
    })

    // const clone = res.clone()

    // let text = await clone.text()
    // console.log(text)

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
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al planes operativos del ente',
    })
  } finally {
    hideLoader()
  }
}

const getEntePlanOperativoId = async (id) => {
  showLoader()
  try {
    console.log(id)

    let res = await fetch(entesPlanOperativoUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consulta_id', id }),
    })

    // const clone = res.clone()

    // let text = await clone.text()
    // console.log(text)

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
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al planes operativos del ente',
    })
  } finally {
    hideLoader()
  }
}

const registrarPlanOperativo = async (data) => {
  showLoader()
  try {
    let res = await fetch(entesPlanOperativoUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'registrar', ...data }),
    })

    const clone = res.clone()

    let text = await clone.text()
    console.log(text)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    console.log(json)
    if (json.hasOwnProperty('success')) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
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
      message: 'Error al planes operativos del ente',
    })
  } finally {
    hideLoader()
  }
}

const actualizarPlanOperativo = async (data) => {
  showLoader()
  try {
    console.log(data)

    let res = await fetch(entesPlanOperativoUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'update', ...data }),
    })

    const clone = res.clone()

    let text = await clone.text()
    console.log(text)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    console.log(json)
    if (json.hasOwnProperty('success')) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
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
      message: 'Error al planes operativos del ente',
    })
  } finally {
    hideLoader()
  }
}
export {
  getEntePlanOperativo,
  registrarPlanOperativo,
  getEntePlanOperativoId,
  actualizarPlanOperativo,
  getEntePlanOperativos,
}

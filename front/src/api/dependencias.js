import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const dependenciasUrl =
  '../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php'

const getDependencias = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${dependenciasUrl}?id=${id}`)
    else res = await fetch(dependenciasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'dependencia',
        id: 'id_dependencia',
      })

      return { mappedData, fullInfo: json.success }
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)
    return toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'No se encontraron unidades',
    })
  }
}

const sendDependencia = async ({ informacion }) => {
  console.log(informacion)
  try {
    let res = await fetch(dependenciasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'insertar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()
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
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar unidad',
    })
  }
}

const updateDependencia = async ({ informacion }) => {
  console.log(informacion)
  try {
    let res = await fetch(dependenciasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'actualizar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()
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
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar unidad',
    })
  }
}

const deleteDependencia = async ({ informacion }) => {
  try {
    let res = await fetch(dependenciasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'eliminar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
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
      message: 'Error al registrar unidad',
    })
  }
}

export {
  getDependencias,
  sendDependencia,
  updateDependencia,
  deleteDependencia,
}

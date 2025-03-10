import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const categoriasUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_categoria_datos.php`

const getCategorias = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${categoriasUrl}?id=${id}`)
    else res = await fetch(categoriasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'categoria_nombre',
        id: 'id',
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
      message: 'No se encontraron categorias',
    })
  }
}

const sendCategoria = async ({ informacion }) => {
  try {
    let res = await fetch(categoriasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'insertar' }),
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
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar categoria',
    })
  }
}

const updateCategoria = async ({ informacion }) => {
  try {
    let res = await fetch(categoriasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'actualizar' }),
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
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al actualizar categoria',
    })
  }
}

const deleteCategoria = async ({ informacion }) => {
  try {
    let res = await fetch(categoriasUrl, {
      method: 'POST',
      body: JSON.stringify({ informacion, accion: 'eliminar' }),
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

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al eliminar categoria',
    })
  }
}

export { getCategorias, sendCategoria, updateCategoria, deleteCategoria }

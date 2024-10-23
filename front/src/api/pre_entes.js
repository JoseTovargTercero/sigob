import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const entesDistribucionUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_asignacion_entes.php'

const getPreAsignacionEntes = async () => {
  showLoader()
  try {
    let res = await fetch(entesDistribucionUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    console.log(json)
    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'ente_nombre',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }

    if (json.error) {
      return json
      // toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener distribucion de partidas',
    })
  } finally {
    hideLoader()
  }
}

const getPreAsignacionEnte = async (id) => {
  showLoader()
  try {
    let res = await fetch(entesDistribucionUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar_por_id', id }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    console.log(json)
    if (json.success) {
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
      message: 'Error al obtener distribucion de partidas',
    })
  } finally {
    hideLoader()
  }
}

export { getPreAsignacionEntes, getPreAsignacionEnte }

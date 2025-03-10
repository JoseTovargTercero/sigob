import {
  confirmNotification,
  hideLoader,
  mapData,
  mapDataManual,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const partidasFormUrl = `${APP_URL}${config.MODULE_NAMES.FORMULACION}form_sectores.php`

const getSectores = async () => {
  showLoader()
  try {
    let res = await fetch(partidasFormUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_todos' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // let clone = res.clone()
    // let text = await clone.text()
    // console.log(text)

    const json = await res.json()

    // console.log(json)

    if (json.success) {
      let mappedData = mapDataManual({
        obj: json.success,
        name: ['sector', 'programa', 'proyecto'],
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener sectores',
    })
  } finally {
    hideLoader()
  }
}

const getSector = async (id) => {
  try {
    let res = await fetch(partidasFormUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar_id', id }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // let clone = res.clone()
    // let text = await clone.text()
    // console.log(text)

    const json = await res.json()

    // console.log(json)

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
      message: 'Error al obtener sector',
    })
  }
}

export { getSector, getSectores }

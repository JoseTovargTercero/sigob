import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const gastosUrl = '../../../../sigob/front/src/api/gastos.json'

const getGastos = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${gastosUrl}?id=${id}`)
    else res = await fetch(gastosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    console.log(json)

    if (id) {
      let data = json.find((objeto) => objeto.id == id)

      return data
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
  }
}

export { getGastos }

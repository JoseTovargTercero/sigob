import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const gastosUrl = '../../../../sigob/front/src/api/gastos.json'
const tipoGastosUrl = '../../../../sigob/front/src/api/tipo_gastos.json'

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
      message: 'Error al obtener gastos registrados',
    })
  }
}

const getTiposGastos = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${tipoGastosUrl}?id=${id}`)
    else res = await fetch(tipoGastosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    // console.log(json)

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
    let mappedData = mapData({
      obj: json,
      name: 'nombre',
      id: 'id',
    })

    return { mappedData, fullInfo: json }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gastos',
    })
  }
}

export { getGastos, getTiposGastos }

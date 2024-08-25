import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const partidasUrl =
  '../../../../sigob/back/modulo_nomina/nom_partidas_datos.php'

const getPartidas = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${partidasUrl}?id=${id}`)
    else res = await fetch(partidasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'descripcion',
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
      message: 'Error al obtener partidas',
    })
  }
}

const consultarPartida = async ({ informacion }) => {
  try {
    let res = await fetch(partidasUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar', informacion }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()
    console.log(json)

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener categorias',
    })
  }
}

export { getPartidas, consultarPartida }

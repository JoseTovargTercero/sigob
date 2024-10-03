import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const ejercicioFiscalUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_ejercicio_fiscal.php'

const distribucionPresupuestariUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_distribucion.php'
const getEjecicios = async (id) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_todos' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    console.log(json)
    if (json.success) {
      if (id) {
        return json.success
      }
      let mappedData = mapData({
        obj: json.success,
        name: 'ano',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener ejercicios fiscales',
    })
  } finally {
    hideLoader()
  }
}

const getEjecicio = async (id) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_por_id', id }),
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
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener ejercicio fiscal',
    })
  } finally {
    hideLoader()
  }
}

const enviarDistribucionPresupuestaria = async ({ arrayDatos }) => {
  showLoader()
  try {
    let res = await fetch(distribucionPresupuestariUrl, {
      method: 'POST',
      body: JSON.stringify({ arrayDatos, accion: 'crear' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    console.log(json)

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
      message: 'Error al enviar datos',
    })
  } finally {
    hideLoader()
  }
}

export { getEjecicio, getEjecicios, enviarDistribucionPresupuestaria }

import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const sendTabulatorUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_tabulador_registro.php`

const updateTabulatorUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_tabulador_modif.php`

const getTabulatorsDataUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_tabulador_datos.php`

const getTabulatorDataUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_tabuladorEst_Info.php`

const deleteTabulatorUrl = `${APP_URL}${config.MODULE_NAMES.NOMINA}nom_tabulador_delete.php`

const sendTabulatorData = async ({ tabulatorData }) => {
  try {
    const res = await fetch(sendTabulatorUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(tabulatorData),
    })
    console.log(res)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
      })

      setTimeout(() => {
        location.assign('nom_tabulador_tabla.php')
      }, 1000)
    }

    const json = await res.text()
  } catch (e) {
    console.error(e)
  }
}

const getTabulatorsData = async () => {
  try {
    const res = await fetch(getTabulatorsDataUrl)
    console.log(res)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return json
  } catch (e) {
    return toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'No se han encontrado tabuladores',
    })
  }
}
const getTabulatorData = async (id) => {
  try {
    const res = await fetch(`${getTabulatorDataUrl}?id=${id}`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return json
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tabuladores',
    })
  }
}

const updateTabulatorData = async ({ tabulatorData }) => {
  console.log(tabulatorData)
  try {
    const res = await fetch(`${updateTabulatorUrl}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(tabulatorData),
    })
    console.log(res)
    let resText = await res.text()
    console.log(resText)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Tabulador actualizado',
      })
      setTimeout(() => {
        location.assign('nom_tabulador_tabla.php')
      }, 1500)
    }
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

const deleteTabulator = async (id) => {
  try {
    const res = await fetch(`${deleteTabulatorUrl}?id=${id}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    let resText = await res.text()
    console.log(resText)
    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Registro eliminado',
      })
    }
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

export {
  sendTabulatorData,
  getTabulatorsData,
  getTabulatorData,
  updateTabulatorData,
  deleteTabulator,
}

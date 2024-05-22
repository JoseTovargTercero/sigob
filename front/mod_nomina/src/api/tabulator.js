import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const sendTabulatorUrl =
  '../../../../../sigob/back/modulo_nomina/nom_tabulador_registro.php'

const getTabulatorsDataUrl =
  '../../../../../sigob/back/modulo_nomina/nom_tabulador_datos.php'

const getTabulatorDataUrl =
  '../../../../../sigob/back/modulo_nomina/nom_tabuladorEst_Info.php'

const deleteTabulatorUrl =
  '../../../../../sigob/back/modulo_nomina/nom_tabulador_delete.php'

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
        location.reload()
      }, 1000)
    }

    const json = await res.json()
    console.log(json)
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
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tabuladores',
    })
  }
}
const getTabulatorData = async (id) => {
  try {
    const res = await fetch(`${getTabulatorDataUrl}?id=${id}`)
    console.log(res)

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

const deleteTabulator = async (id) => {
  console.log(id)
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
  deleteTabulator,
}

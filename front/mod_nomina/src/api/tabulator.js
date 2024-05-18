import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const tabuladorRegistrarUrl =
  '../../../../../sigob/back/modulo_nomina/nom_tabulador_registro.php'

async function sendTabulatorData({ tabulatorData }) {
  try {
    const res = await fetch(tabuladorRegistrarUrl, {
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

export { sendTabulatorData }

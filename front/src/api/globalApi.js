import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const apiUrl = `${APP_URL}${config.MODULE_NAMES.GLOBAL}/_DBH-select.php`

const selectTables = async (table, config = null) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        table,
        config,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    // console.log(res)

    // let clone = res.clone()
    // let text = await clone.text()
    // console.log(text)

    const json = await res.json()

    // console.log(json)

    if (json.success) {
      return json.success
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: `Error al obtener información de ${table}`,
    })
  } finally {
    hideLoader()
  }
}

export { selectTables }

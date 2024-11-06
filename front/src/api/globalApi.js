import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const apiUrl = '../../../sigob/back/sistema_global/_DBH-select.php'

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

    let clone = res.clone()
    let text = await clone.text()
    console.log(text)

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
      message: `Error al obtener información de ${table}`,
    })
  } finally {
    hideLoader()
  }
}

function dbh_select(tabla, config = null) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: sistema.tablas,
      type: 'POST',
      contentType: 'application/json',
      dataType: 'json',
      data: JSON.stringify({
        table: tabla,
        config: config,
      }),
      success: resolve,
      error: function (xhr, status, error) {
        console.log(xhr.responseText)
        reject(error)
      },
    })
  })
}

export { selectTables }

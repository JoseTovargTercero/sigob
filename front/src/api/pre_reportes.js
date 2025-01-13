import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const reportesPdfUrl = `${APP_URL}${config.MODULE_NAMES.EJECUCION}pre_reportes.php`

const generarReporte = async ({ ejercicio_fiscal, tipo, nombreArchivo }) => {
  showLoader()
  try {
    let res = await fetch(reportesPdfUrl, {
      method: 'POST',
      body: JSON.stringify({ data: { ejercicio_fiscal, tipo } }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // let clone = res.clone()
    // let text = await clone.text()

    const blob = await res.blob()
    console.log(blob)

    const url = URL.createObjectURL(blob)

    // Crear un enlace temporal
    const enlace = document.createElement('a')
    enlace.href = url
    enlace.download = nombreArchivo

    // Simular un clic en el enlace para iniciar la descarga
    document.body.appendChild(enlace)
    enlace.click()

    // Limpiar el DOM
    document.body.removeChild(enlace)

    // Liberar la URL del Blob
    URL.revokeObjectURL(url)
    // if (json.success) {
    //   return json.success
    // }

    // return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al descargar compromiso',
    })
  } finally {
    hideLoader()
  }
}

export { generarReporte }

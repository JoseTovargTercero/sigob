import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const compromisosPdfUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_compromisos_pdf.php'

const generarCompromisoPdf = async (id, nombreArchivo) => {
  showLoader()
  try {
    let res = await fetch(`${compromisosPdfUrl}?id_compromiso=${id}`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()

    const blob = await res.blob()
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

export { generarCompromisoPdf }

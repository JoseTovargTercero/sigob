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

const compromisosUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_compromisos_registrar.php'

const generarCompromisoPdf = async (id, nombreArchivo) => {
  showLoader()
  try {
    console.log(id)

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

const registrarCompromiso = async (data) => {
  showLoader()
  try {
    console.log(data)

    let res = await fetch(compromisosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'insert', ...data }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()
    // const text = await clone.text()

    // console.log(text)
    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al registrar compromiso',
    })
  } finally {
    hideLoader()
  }
}

const consultarCompromiso = async (data) => {
  showLoader()
  try {
    console.log(data)

    let res = await fetch(compromisosUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar', ...data }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()
    // const text = await clone.text()

    // console.log(text)
    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
      return
    }

    if (json.hasOwnProperty('success')) {
      return json.success
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al consultar compromiso',
    })
  } finally {
    hideLoader()
  }
}

export { generarCompromisoPdf, registrarCompromiso, consultarCompromiso }

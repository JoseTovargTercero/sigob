import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { APP_URL, config } from './urlConfig.js'

const ejercicioFiscalUrl =
  '../../../../sigob/back/sistema_global/ejercicio_fiscal.php'

const distribucionPresupuestariaUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_distribucion.php'

const distribucionPresupuestariaEntesUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_distribucion_entes.php'

const getEjecicios = async (id) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_todos' }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      if (id) {
        return json.success
      }
      return json.success
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
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

    // const clone = res.clone()

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
      message: 'Error al obtener ejercicio fiscal',
    })
  } finally {
    hideLoader()
  }
}

const enviarDistribucionPresupuestaria = async ({ arrayDatos }) => {
  showLoader()
  try {
    let res = await fetch(distribucionPresupuestariaUrl, {
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
      window.location.reload()
    }
    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
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

const eliminarDistribucion = async (id) => {
  try {
    let res = await fetch(distribucionPresupuestariaUrl, {
      method: 'POST',
      body: JSON.stringify({ id, accion: 'eliminar' }),
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
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al eliminar distribucion ',
    })
  } finally {
    hideLoader()
  }
}

const modificarMontoDistribucion = async ({
  id_ejercicio,
  id_distribucion1,
  id_distribucion2,
  id_sector,
  id_partida,
  monto,
}) => {
  showLoader()
  try {
    let res = await fetch(distribucionPresupuestariaUrl, {
      method: 'POST',
      body: JSON.stringify({
        id_ejercicio,
        id_distribucion1,
        id_distribucion2,
        id_sector,
        id_partida,
        monto,
        accion: 'actualizar_monto_distribucion',
      }),
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
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
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

const enviarDistribucionPresupuestariaEntes = async ({ data, tipo }) => {
  showLoader()
  try {
    console.log({ accion: 'insert', informacion: data })
    let res = await fetch(distribucionPresupuestariaEntesUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'insert', informacion: data }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
    }
    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
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

const obtenerDistribucionPositiva = async ({ id_ejercicio }) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtener_distribucion_positiva',
        id_ejercicio,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }

    if (json.success) {
      return json.success
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

const obtenerEntes = async () => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtener_entes',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    // console.log(json)

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }

    if (json.success) {
      return json.success
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener entes',
    })
  } finally {
    hideLoader()
  }
}

const obtenerDistribucionEntes = async (id_ejercicio, id_ente) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtener_distribuciones_entes',
        id_ejercicio,
        id_ente,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }

    if (json.success) {
      return json.success[0]
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

const obtenerDistribuciones = async (id_ejercicio) => {
  showLoader()
  try {
    let res = await fetch(ejercicioFiscalUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtener_distribuciones',
        id_ejercicio,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    console.log(json)

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      })
    }

    if (json.success) {
      return json.success
    }

    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener informacion de distribucion',
    })
  } finally {
    hideLoader()
  }
}

export {
  getEjecicio,
  getEjecicios,
  obtenerDistribucionPositiva,
  enviarDistribucionPresupuestaria,
  modificarMontoDistribucion,
  enviarDistribucionPresupuestariaEntes,
  eliminarDistribucion,
  obtenerDistribucionEntes,
  obtenerDistribuciones,
  obtenerEntes,
}

import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const gastosUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_gastos.php'
const tipoGastosUrl =
  '../../../../sigob/back/modulo_ejecucion_presupuestaria/pre_tipo_gastos.php'

const getGastos = async () => {
  showLoader()
  try {
    let res = await fetch(gastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtener',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

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
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const getGasto = async (id) => {
  showLoader()
  try {
    let res = await fetch(gastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'obtenerPorId',
        id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()

    // console.log(text)

    const json = await res.json()
    // console.log(json)

    if (json.success) {
      return json.success
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const registrarGasto = async ({ data }) => {
  showLoader()
  try {
    let res = await fetch(gastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'crear',
        ...data,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()

    console.log(text)

    const json = await res.json()
    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const aceptarGasto = async (id) => {
  showLoader()
  try {
    let res = await fetch(gastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'gestionar',
        id,
        accion_gestion: 'aceptar',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()

    console.log(text)

    const json = await res.json()
    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const rechazarGasto = async (id) => {
  showLoader()
  try {
    console.log({
      accion: 'gestionar',
      accion_gestion: 'rechazar',
      id,
    })

    let res = await fetch(gastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'gestionar',
        accion_gestion: 'rechazar',
        id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    let clone = res.clone()
    let text = await clone.text()

    console.log(text)

    const json = await res.json()
    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const getTiposGastos = async () => {
  showLoader()
  try {
    let res = await fetch(tipoGastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    if (json.success) {
      return json.success
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const getTipoGasto = async (id) => {
  showLoader()
  try {
    let res = await fetch(tipoGastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'consultar_id',
        id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      return json.success
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const registrarTipoGasto = async ({ nombre }) => {
  showLoader()
  try {
    let res = await fetch(tipoGastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'insert',
        nombre,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

const eliminatTipoGasto = async (id) => {
  showLoader()
  try {
    let res = await fetch(tipoGastosUrl, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'delete',
        id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      })
      return json
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener tipos de gatos',
    })
  } finally {
    hideLoader()
  }
}

export {
  getGastos,
  getGasto,
  registrarGasto,
  aceptarGasto,
  rechazarGasto,
  getTiposGastos,
  getTipoGasto,
  registrarTipoGasto,
  eliminatTipoGasto,
}

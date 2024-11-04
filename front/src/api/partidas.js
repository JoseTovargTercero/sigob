import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const partidasUrl =
  '../../../../sigob/back/modulo_nomina/nom_partidas_datos.php'

const partidasOrdinariasUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_registro_ordinarias.php'

const partidasFormUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_partidas.php'

const getPartidas = async (id) => {
  try {
    let res
    if (id) res = await fetch(`${partidasUrl}?id=${id}`)
    else res = await fetch(partidasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'descripcion',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener partidas',
    })
  }
}

const consultarPartida = async ({ informacion }) => {
  try {
    let res = await fetch(partidasUrl, {
      method: 'POST',
      body: JSON.stringify({ accion: 'consultar', informacion }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()
    console.log(json)

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener categorias',
    })
  }
}

const getFormPartidas = async (id) => {
  try {
    let res
    if (id)
      res = await fetch(partidasFormUrl, {
        method: 'POST',
        body: JSON.stringify({ accion: 'consultar_id', id }),
      })
    else
      res = await fetch(partidasFormUrl, {
        method: 'POST',
        body: JSON.stringify({ accion: 'consultar' }),
      })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // let clone = res.clone()
    // let text = await clone.text()
    // console.log(text)

    const json = await res.json()

    // console.log(json)

    if (json.success) {
      if (id) {
        return json.success
      }
      let mappedData = mapData({
        obj: json.success,
        name: 'descripcion',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener partidas',
    })
  }
}

const guardarPartida = async ({ partida, nombre, descripcion }) => {
  console.log({ partida, nombre, descripcion })
  showLoader()
  try {
    let res = await fetch(partidasFormUrl, {
      method: 'POST',
      body: JSON.stringify({ partida, nombre, descripcion, accion: 'insert' }),
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
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener partidas',
    })
  } finally {
    hideLoader()
  }
}

const guardarPartidaOrdinaria = async ({
  partida,
  ordinaria,
  denominacion,
}) => {
  showLoader()
  try {
    let res = await fetch(partidasOrdinariasUrl, {
      method: 'POST',
      body: JSON.stringify({
        partida,
        ordinaria,
        denominacion,
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
        message: `Partida creada`,
      })
      return json
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener partidas',
    })
  } finally {
    hideLoader()
  }
}

const actualizarPartida = async ({ partida, nombre, descripcion, id }) => {
  console.log({ partida, nombre, descripcion, id })
  showLoader()
  try {
    let res = await fetch(partidasFormUrl, {
      method: 'POST',
      body: JSON.stringify({
        id,
        partida,
        nombre,
        descripcion,
        accion: 'update',
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
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al actualizar partidas',
    })
  } finally {
    hideLoader()
  }
}

const eliminarPartida = async (id) => {
  showLoader()
  try {
    let res = await fetch(partidasFormUrl, {
      method: 'POST',
      body: JSON.stringify({
        id,
        accion: 'delete',
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
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al actualizar partidas',
    })
  } finally {
    hideLoader()
  }
}

export {
  getPartidas,
  consultarPartida,
  getFormPartidas,
  guardarPartida,
  guardarPartidaOrdinaria,
  actualizarPartida,
  eliminarPartida,
}

import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const apiUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_pdf_informacion_crud.php'

const getGobernacionData = async () => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_gobernacion',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'nombre',
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
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const getGobernacionDataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_gobernacion',
        accion: 'consultar_por_id',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      return json.success[0]
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const registrarGobernacionData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_gobernacion',
        accion: 'registrar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha realizado el registro',
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const actualizarGobernacionData = async (info, id) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_gobernacion',
        accion: 'actualizar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha actualizado el registro',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const eliminarGobernacionId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_gobernacion',
        accion: 'borrar',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Registro eliminado',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

// CONTRALORIA
// CONTRALORIA
// CONTRALORIA

const getContraloriaData = async () => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_contraloria',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'nombre',
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
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const getContraloriaDataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_contraloria',
        accion: 'consultar_por_id',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      return json.success[0]
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const registrarContraloriaData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_contraloria',
        accion: 'registrar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha realizado el registro',
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const actualizarContraloriaData = async (info, id) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_contraloria',
        accion: 'actualizar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha actualizado el registro',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const eliminarContraloriaId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_contraloria',
        accion: 'borrar',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Registro eliminado',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

// CONSEJO
// CONSEJO
// CONSEJO

const getConsejoData = async () => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_consejo',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)
    const json = await res.json()

    console.log(json)

    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'nombre',
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
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const getConsejoDataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_consejo',
        accion: 'consultar_por_id',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      return json.success[0]
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const registrarConsejoData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_consejo',
        accion: 'registrar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha realizado el registro',
      })
    }
    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const actualizarConsejoData = async (info, id) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_consejo',
        accion: 'actualizar',
        info,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Se ha actualizado el registro',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const eliminarConsejoId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_consejo',
        accion: 'borrar',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Registro eliminado',
      })
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

// PERSONAL DIRECTIVO
// PERSONAL DIRECTIVO
// PERSONAL DIRECTIVO

const getPersonalDirectivo = async () => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'personal_directivo',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)

    const json = await res.json()

    // console.log(json)
    if (json.success) {
      let mappedData = mapData({
        obj: json.success,
        name: 'nombre',
        id: 'id',
      })

      return { mappedData, fullInfo: json.success }
    }

    if (json.error) {
      toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    }
    return json
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

const getPersonalDirectivoId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'personal_directivo',
        accion: 'consultar_por_id',
        id: id,
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

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
      message: 'Error al obtener información',
    })
  } finally {
    hideLoader()
  }
}

export {
  getGobernacionData,
  getGobernacionDataId,
  registrarGobernacionData,
  actualizarGobernacionData,
  eliminarGobernacionId,
  getContraloriaData,
  getContraloriaDataId,
  registrarContraloriaData,
  eliminarContraloriaId,
  actualizarContraloriaData,
  getConsejoData,
  getConsejoDataId,
  registrarConsejoData,
  eliminarConsejoId,
  actualizarConsejoData,
}

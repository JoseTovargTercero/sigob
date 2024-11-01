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

const apiPresupuestoUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_pdf_presupuesto_crud.php'

const apiDescripcionProgramaUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_pdf_descripcion_crud.php'

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

const actualizarGobernacionData = async ({ info }) => {
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

const actualizarContraloriaData = async ({ info }) => {
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

const actualizarConsejoData = async ({ info }) => {
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

const getDirectivoData = async () => {
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

const getDirectivoDataId = async (id) => {
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

const registrarDirectivoData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'personal_directivo',
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

const actualizarDirectivoData = async ({ info }) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'personal_directivo',
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

const eliminarDirectivoId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'personal_directivo',
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

const getPersonaData = async () => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_personas',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    console.log(res)

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)
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

const getPersonaDataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_personas',
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

const registrarPersonaData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_personas',
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

const actualizarPersonaData = async ({ info }) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_personas',
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

const eliminarPersonaId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'informacion_personas',
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

const getTitulo1Data = async () => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'titulo_1',
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

const getTitulo1DataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'titulo_1',
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

const registrarTitulo1Data = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'titulo_1',
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

const actualizarTitulo1Data = async ({ info }) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'titulo_1',
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

const eliminarTitulo1Id = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiPresupuestoUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'titulo_1',
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
const getDescripcionProgramaData = async () => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
        accion: 'consultar_todos',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)
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

const getSectoresData = async () => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
        accion: 'consultar_sector',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)
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
const getProgramasData = async () => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
        accion: 'consultar_programa',
      }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const clone = res.clone()

    let text = await clone.text()

    console.log(text)
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

const getDescripcionProgramaDataId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
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

const registrarDescripcionProgramaData = async ({ info }) => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
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

const actualizarDescripcionProgramaData = async ({ info }) => {
  console.log(info)
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
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

const eliminarDescripcionProgramaId = async (id) => {
  showLoader()
  try {
    let res = await fetch(apiDescripcionProgramaUrl, {
      method: 'POST',
      body: JSON.stringify({
        tabla: 'descripcion_programas',
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
  getDirectivoData,
  getDirectivoDataId,
  registrarDirectivoData,
  actualizarDirectivoData,
  eliminarDirectivoId,
  getPersonaData,
  getPersonaDataId,
  registrarPersonaData,
  actualizarPersonaData,
  eliminarPersonaId,
  getTitulo1Data,
  getTitulo1DataId,
  registrarTitulo1Data,
  actualizarTitulo1Data,
  eliminarTitulo1Id,
  getDescripcionProgramaData,
  getProgramasData,
  getSectoresData,
  getDescripcionProgramaDataId,
  registrarDescripcionProgramaData,
  actualizarDescripcionProgramaData,
  eliminarDescripcionProgramaId,
}

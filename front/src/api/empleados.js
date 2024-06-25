import {
  confirmNotification,
  hideLoader,
  showLoader,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const cargosUrl = '../../../../../sigob/back/modulo_nomina/nom_cargos_info.php'

const dependenciasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php'

const bancosUrl =
  '../../../../../sigob/back/modulo_nomina/nom_bancos_disponibles.php'

const profesionesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_profesion_info.php'

const sendEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_registro.php'

const updateEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_modif.php'

const updateRequestEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_editar_solicitud.php'

const getEmployeesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_datos.php'

const getEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleado_datos.php'

const deleteEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_delete.php'

const sendDependencyUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencia_registro.php'

const mapData = ({ obj, name, id }) => {
  return obj.map((el) => {
    return { name: el[name], id: el[id] }
  })
}

// const mapEmployee = ({ obj, name, id }) => {}

const getEmployeesData = async () => {
  showLoader()
  try {
    const res = await fetch(getEmployeesUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return json
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener empleados',
    })
  } finally {
    hideLoader()
  }
}

const getEmployeeData = async (id) => {
  try {
    const res = await fetch(`${getEmployeeUrl}?id=${id}`)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    const json = await res.json()

    return json
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener empleados',
    })
  }
}

const sendEmployeeData = async ({ data }) => {
  console.log(data)
  try {
    const res = await fetch(sendEmployeeUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Datos guardados',
      })
    }
    const json = await res.text()
    console.log(json)
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al guardar datos del empleado',
    })
  }
}

const updateEmployeeData = async ({ id }) => {
  console.log(id)
  try {
    const res = await fetch(updateEmployeeUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id }),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Datos guardados',
      })
      // setTimeout(() => {
      //   location.assign('nom_empleados_tabla.php')
      // }, 1500)
    }
    const json = await res.text()
    console.log(json)
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

const updateRequestEmployeeData = async ({ data = [] }) => {
  console.log(data)
  try {
    const res = await fetch(updateRequestEmployeeUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Datos guardados',
      })
      // setTimeout(() => {
      //   location.assign('nom_empleados_tabla.php')
      // }, 1500)
    }

    const json = await res.json()
    console.log(json)

    if (json.errores.length > 0) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: `Los siguientes datos ya están en revisión: ${json.errores.join(
          ', '
        )} Los demás se enviaron.`,
      })
    } else {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: `Solicitud de modificación enviada para su revisión`,
      })
    }

    return json
  } catch (e) {
    console.error(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

const deleteEmployee = async (id) => {
  console.log(id)
  try {
    const res = await fetch(`${deleteEmployeeUrl}?id=${id}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Registro eliminado',
      })
    }
    const json = await res.text()
    return json
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

const getJobData = async () => {
  showLoader()
  try {
    const res = await fetch(cargosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return mapData({ obj: json, name: 'cargo', id: 'cod_cargo' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener cargos',
    })
  } finally {
    hideLoader()
  }
}

const getProfessionData = async () => {
  showLoader()
  try {
    const res = await fetch(profesionesUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return mapData({ obj: json, name: 'profesion', id: 'id_profesion' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener profesiones',
    })
  } finally {
    hideLoader()
  }
}

const getDependencyData = async () => {
  showLoader()
  try {
    const res = await fetch(dependenciasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    return mapData({ obj: json, name: 'dependencia', id: 'id_dependencia' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener dependencias',
    })
  } finally {
    hideLoader()
  }
}

const getBankData = async () => {
  showLoader()
  try {
    const res = await fetch(bancosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    return mapData({ obj: json, name: 'nombre', id: 'prefijo' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener bancos',
    })
  } finally {
    hideLoader()
  }
}

const sendDependencyData = async ({ newDependency }) => {
  try {
    const dependencyData = await getDependencyData()
    if (
      dependencyData.some(
        (el) =>
          el.name.toUpperCase() === newDependency.dependencia.toUpperCase()
      )
    )
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Dependencia ya existe',
      })

    const res = await fetch(sendDependencyUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(newDependency),
    })

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Dependencia añadida',
      })
      return newDependency
    }
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}
export {
  getEmployeesData,
  getEmployeeData,
  sendEmployeeData,
  updateEmployeeData,
  updateRequestEmployeeData,
  deleteEmployee,
  getJobData,
  getProfessionData,
  getDependencyData,
  getBankData,
  sendDependencyData,
}

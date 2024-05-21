import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const cargosUrl = '../../../../../sigob/back/modulo_nomina/nom_cargos_info.php'

const depdendenciasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php'

const profesionesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_profesion_info.php'

const sendEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_registro.php'

const updateEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_modif.php'

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

const getEmployeesData = async () => {
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
      setTimeout(() => {
        location.assign('nom_empleados_tabla')
      }, 1500)
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

const updateEmployeeData = async ({ data }) => {
  console.log(data)
  try {
    const res = await fetch(updateEmployeeUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })

    console.log(data)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }
    else {
      console.log(res)
      confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Datos guardados',
      })
      setTimeout(() => {
        location.assign('nom_empleados_tabla.php')
      }, 1500)
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
  }
}

const getProfessionData = async () => {
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
  }
}

const getDependencyData = async () => {
  try {
    const res = await fetch(depdendenciasUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()

    return mapData({ obj: json, name: 'dependencia', id: 'id_dependencia' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener dependencias',
    })
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
  deleteEmployee,
  getJobData,
  getProfessionData,
  getDependencyData,
  sendDependencyData,
}

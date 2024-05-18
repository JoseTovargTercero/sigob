import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const cargosUrl = '../../../../../sigob/back/modulo_nomina/nom_cargos_info.php'

const depdendenciasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php'

const profesionesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_profesion_info.php'

const sendEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_registro.php'

const getEmployeesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_datos.php'

const deleteEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_eliminar.php'

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
        message: 'Datos enviados',
      })
      setTimeout(() => {
        location.reload()
      }, 2000)
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

const deleteEmployee = async ({ id }) => {
  try {
    const res = await fetch(deleteEmployeeUrl, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(id),
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
    console.log(json)
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al enviar datos del empleado',
    })
  }
}

const getCargoData = async () => {
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

const getProfesionesData = async () => {
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

const getDependenciasData = async () => {
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
    const dependencyData = await getDependenciasData()
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
  sendEmployeeData,
  deleteEmployee,
  getCargoData,
  getProfesionesData,
  getDependenciasData,
  sendDependencyData,
}

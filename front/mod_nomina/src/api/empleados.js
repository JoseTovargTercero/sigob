import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const cargosUrl = '../../../../../sigob/back/modulo_nomina/nom_cargos_info.php'
const depdendenciasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php'
const profesionesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_profesion_info.php'

const sendEmployeeUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_registro.php'

const mapData = ({ obj, name, id }) => {
  return obj.map((el) => {
    return { name: el[name], id: el[id] }
  })
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
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'Datos enviados',
      })
    }
    console.log(res)
    // const json = await res.json()
    // console.log(json)
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
    console.log(json)

    return mapData({ obj: json, name: 'dependencia', id: 'id_dependencia' })
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener dependencias',
    })
  }
}

export {
  getCargoData,
  getProfesionesData,
  getDependenciasData,
  sendEmployeeData,
}

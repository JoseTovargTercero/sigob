import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const obtenerNominasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_pagar_back.php'
const calculoNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_calculonomina.php'

const getNominas = async (grupo) => {
  const data = new FormData()
  data.append('select', true)
  data.append('grupo', grupo)
  try {
    let res = await fetch(obtenerNominasUrl, {
      method: 'POST',
      body: data,
    })

    let json = await res.json()
    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  }
}

const getEmpleadosNomina = async (data) => {
  console.log(data)
  try {
    let res = await fetch(calculoNominaUrl, {
      method: 'POST',
      body: JSON.stringify({ nombre: data }),
    })

    let json = await res.json()

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  }
}

export { getNominas, getEmpleadosNomina }

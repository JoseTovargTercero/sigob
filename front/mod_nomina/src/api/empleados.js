import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const cargosUrl = '../../../../../sigob/back/modulo_nomina/nom_cargos_info.php'
const depdendenciasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_dependencias_datos.php.php'
const profesionesUrl =
  '../../../../../sigob/back/modulo_nomina/nom_profesion_info.php'

const getCargoData = async () => {
  try {
    const res = await fetch(cargosUrl)

    if (!res.ok) throw { status: res.status, statusText: res.statusText }

    const json = await res.json()
    return json
  } catch (e) {
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al optener cargos',
    })
  }
}

export { getCargoData }

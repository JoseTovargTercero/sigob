import {
  confirmNotification,
  hideLoader,
  mapData,
  showLoader,
  toastNotification,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

let datos = [
  {
    id: 0,
    id_ente: 0,
    tipo_ente: 'J',
    ente_nombre: 'Ente 1',
    id_poa: 0,
    partidas: [{ id: 1, monto: 5000 }],
    monto: 15000,
  },
  {
    id: 1,
    id_ente: 1,
    tipo_ente: 'J',
    ente_nombre: 'Ente 2',
    partidas: [
      { id: 1, monto: 7000 },
      { id: 2, monto: 12000 },
    ],
    monto: 19000,
  },
  {
    id: 2,
    id_ente: 2,
    tipo_ente: 'D',
    ente_nombre: 'Ente 3',
    id_poa: 2,
    partidas: [{ id: 1, monto: 6000 }],
    monto: 21000,
  },
  {
    id: 3,
    id_ente: 3,
    tipo_ente: 'J',
    ente_nombre: 'Ente 4',
    id_poa: 3,
    partidas: [
      { id: 1, monto: 8000 },
      { id: 2, monto: 11000 },
    ],
    monto: 19000,
  },
  {
    id: 4,
    id_ente: 4,
    tipo_ente: 'D',
    ente_nombre: 'Ente 5',
    id_poa: 4,
    partidas: [{ id: 1, monto: 9000 }],
    monto: 17000,
  },
  {
    id: 5,
    id_ente: 5,
    tipo_ente: 'J',
    ente_nombre: 'Ente 6',
    id_poa: 5,
    partidas: [
      { id: 1, monto: 7000 },
      { id: 2, monto: 17000 },
    ],
    monto: 24000,
  },
  {
    id: 6,
    id_ente: 6,
    tipo_ente: 'D',
    ente_nombre: 'Ente 7',
    id_poa: 6,
    partidas: [{ id: 2, monto: 12000 }],
    monto: 22000,
  },
  {
    id: 7,
    id_ente: 7,
    tipo_ente: 'J',
    ente_nombre: 'Ente 8',
    id_poa: 7,
    partidas: [
      { id: 1, monto: 9500 },
      { id: 2, monto: 10500 },
    ],
    monto: 20000,
  },
  {
    id: 8,
    id_ente: 8,
    tipo_ente: 'D',
    ente_nombre: 'Ente 9',
    id_poa: 8,
    partidas: [{ id: 2, monto: 13500 }],
    monto: 22000,
  },
  {
    id: 9,
    id_ente: 9,
    tipo_ente: 'J',
    ente_nombre: 'Ente 10',
    id_poa: 9,
    partidas: [
      { id: 1, monto: 9200 },
      { id: 2, monto: 9800 },
    ],
    monto: 19000,
  },
]

const ejercicioFiscalUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_ejercicio_fiscal.php'

const distribucionPresupuestariUrl =
  '../../../../sigob/back/modulo_pl_formulacion/form_distribucion.php'
const getEntesPlanes = async () => {
  showLoader()
  try {
    // let res = await fetch(ejercicioFiscalUrl, {
    //   method: 'POST',
    //   body: JSON.stringify({ accion: 'obtener_todos' }),
    // })

    // if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    // const json = await res.json()

    // console.log(json)
    // if (json.success) {
    //   if (id) {
    //     return json.success
    //   }
    //   let mappedData = mapData({
    //     obj: json.success,
    //     name: 'ano',
    //     id: 'id',
    //   })

    //   return { mappedData, fullInfo: json.success }
    // }

    // if (json.error) {
    //   toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    // }
    return datos
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener ejercicios fiscales',
    })
  } finally {
    hideLoader()
  }
}

const getEntesPlan = async (id) => {
  showLoader()
  try {
    // let res = await fetch(ejercicioFiscalUrl, {
    //   method: 'POST',
    //   body: JSON.stringify({ accion: 'obtener_por_id', id }),
    // })

    // if (!res.ok) throw { status: res.status, statusText: res.statusText }

    // const clone = res.clone()

    // let text = await clone.text()

    // console.log(text)

    // const json = await res.json()

    // console.log(json)

    // if (json.success) {
    //   return json.success
    // }
    // if (json.error) {
    //   toastNotification({ type: NOTIFICATIONS_TYPES.fail, message: json.error })
    // }

    let planEncontrado = datos.find((plan) => plan.id === id)

    return planEncontrado
  } catch (e) {
    console.log(e)

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener ejercicio fiscal',
    })
  } finally {
    hideLoader()
  }
}

export { getEntesPlan, getEntesPlanes }

import {
  confirmNotification,
  hideLoader,
  showLoader,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  getDependencyData,
  getJobData,
  getProfessionData,
} from './empleados.js'

const obtenerNominasUrl =
  '../../../../../sigob/back/modulo_nomina/nom_empleados_pagar_back.php'

const calculoNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_calculonomina.php'

const comparacionNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_comparacion_nominas.php'

const obtenerPeticionesNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_peticiones.php'

const enviarCalculoNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_calculonomina_registro.php'

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

const getGruposNomina = async (data) => {
  showLoader('employee-pay-loader')
  try {
    let res = await fetch(calculoNominaUrl, {
      method: 'POST',
      body: JSON.stringify({ nombre: data }),
    })

    let json = await res.json()

    json.informacion_empleados = await mapData(json.informacion_empleados)

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  } finally {
    hideLoader('employee-pay-loader')
  }
}

const getPeticionesNomina = async () => {
  showLoader('employee-pay-loader')
  try {
    let res = await fetch(obtenerPeticionesNominaUrl)

    let data = await res.json()
    data.forEach((el) => {
      el.empleados = JSON.parse(el.empleados)
      el.asignaciones = JSON.parse(el.asignaciones)
      el.deducciones = JSON.parse(el.deducciones)

      el.total_a_pagar = JSON.parse(el.total_pagar)
    })

    // data.informacion_empleados = JSON.parse(data.informacion_empleados)

    return data
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  } finally {
    hideLoader('employee-pay-loader')
  }
}

const getComparacionNomina = async (obj) => {
  let { correlativo, nombre_nomina } = obj

  showLoader('employee-pay-loader')
  try {
    let res = await fetch(comparacionNominaUrl, {
      method: 'POST',
      body: JSON.stringify({ correlativo, nombre_nomina }),
    })

    let data = await res.json()

    let { registro_actual, registro_anterior } = data

    if (data.registro_anterior.id !== 0) {
      registro_anterior = mapComparationRequest(registro_anterior)
    } else {
      data.registro_anterior = false
    }

    registro_actual = mapComparationRequest(registro_actual)

    return data
  } catch (e) {
    console.log(e.message)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  } finally {
    hideLoader('employee-pay-loader')
  }
}

const sendCalculoNomina = async (requestInfo) => {
  try {
    let res = await fetch(enviarCalculoNominaUrl, {
      method: 'POST',
      body: JSON.stringify(requestInfo),
    })

    let json = await res.json()

    await confirmNotification({
      type: NOTIFICATIONS_TYPES.done,
      message: json.success,
    })
    setTimeout(() => {
      location.reload()
    }, 1000)
    return
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  }
}

export {
  getNominas,
  getGruposNomina,
  getPeticionesNomina,
  sendCalculoNomina,
  getComparacionNomina,
}

async function mapComparationRequest(obj) {
  for (let key in obj) {
    if (
      typeof obj[key] === 'string' &&
      (obj[key].startsWith('[') || obj[key].startsWith('{'))
    ) {
      obj[key] = JSON.parse(obj[key])
    }
  }

  return obj
}

async function mapData(data) {
  let cargos = await getJobData()

  let dependencias = await getDependencyData()
  let profesiones = await getProfessionData()
  return data.map((empleado) => {
    let {
      id,
      nacionalidad,
      cedula,
      cod_empleado,
      nombres,
      fecha_ingreso,
      otros_años,
      status,
      observacion,
      cod_cargo,
      banco,
      cuenta_bancaria,
      hijos,
      instruccion_academica,
      discapacidades,
      tipo_cuenta,
      tipo_nomina,
      id_dependencia,
      salario_base,
      salario_integral,
      total_a_pagar,
      RPE,
    } = empleado

    nacionalidad = 'V' ? 'VENEZOLANO' : 'E'
    status = 1 ? 'ACTIVO' : 'INACTIVO'
    discapacidades = 1 ? 'SI' : 'NO'
    tipo_cuenta = 1 ? 'AHORRO' : 'CORRIENTE'
    id_dependencia = dependencias.find((el) => el.id == id_dependencia).name
    instruccion_academica = profesiones.find(
      (el) => el.id == instruccion_academica
    ).name
    cod_cargo = cargos.find((el) => el.id === cod_cargo).name

    let CONTRIBUCION_POR_DISCAPACIDAD =
        empleado['CONTRIBUCION POR DISCAPACIDAD'],
      PRIMA_POR_HIJO_EMPLEADOS = empleado['PRIMA POR HIJO EMPLEADOS'],
      PRIMA_POR_TRANSPORTE = empleado['PRIMA POR TRANSPORTE'],
      PRIMA_POR_ANTIGUEDAD_EMPLEADOS =
        empleado['PRIMA POR ANTIGUEDAD EMPLEADOS'],
      PRIMA_POR_ESCALAFON = empleado['PRIMA POR ESCALAFON'],
      PRIMA_POR_FRONTERA = empleado['PRIMA POR FRONTERA'],
      PRIMA_POR_PROFESIONALES = empleado['PRIMA POR PROFESIONALES'],
      S_S_O = empleado['S. S. O'],
      A_P_S_S_O = empleado['A/P S.S.O'],
      A_P_RPE = empleado['A/P RPE'],
      PAGO_DE_BECA = empleado['PAGO DE BECA'],
      PRIMA_P_DED_AL_S_PUBLICO_UNICO_DE_SALUD =
        empleado['PRIMA P/DED AL S/PUBLICO UNICO DE SALUD']
    return {
      id,
      nacionalidad,
      cedula,
      cod_empleado,
      nombres,
      fecha_ingreso,
      otros_años,
      status,
      cod_cargo,
      banco,
      cuenta_bancaria,
      hijos,
      instruccion_academica,
      discapacidades,
      tipo_cuenta,
      tipo_nomina,
      id_dependencia,
      salario_base,
      salario_integral,
      CONTRIBUCION_POR_DISCAPACIDAD,
      PRIMA_POR_HIJO_EMPLEADOS,
      PRIMA_POR_TRANSPORTE,
      PRIMA_POR_ANTIGUEDAD_EMPLEADOS,
      PRIMA_POR_ESCALAFON,
      PRIMA_POR_FRONTERA,
      PRIMA_POR_PROFESIONALES,
      S_S_O,
      RPE,
      A_P_S_S_O,
      A_P_RPE,
      PAGO_DE_BECA,
      PRIMA_P_DED_AL_S_PUBLICO_UNICO_DE_SALUD,
      total_a_pagar,
    }
  })
}

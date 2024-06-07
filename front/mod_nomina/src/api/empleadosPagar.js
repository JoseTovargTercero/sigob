import { confirmNotification } from '../helpers/helpers.js'
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
  }
}

async function mapData(data) {
  // let cargos = await getJobData()
  // let dependencias = await getDependencyData()
  // let profesiones = await getProfessionData()

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

    // let cargo = cargos.filter((el) => el.id == empleado.cod_cargo)[0].name
    // console.log(id_dependencia)
    // let dependencia = dependencias.filter(
    //   (el) => el.id == empleado.id_dependencia
    // )[0].name

    // let profesion = profesiones.filter(
    //   (el) => el.id == instruccion_academica
    // )[0].name
    // console.log(cargo, dependencia, profesion)

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

export { getNominas, getEmpleadosNomina }

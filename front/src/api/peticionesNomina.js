import { loadRequestTable } from '../controllers/peticionesTable.js'
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

const obtenerNominasTxtUrl =
  '../../../../../sigob/back/modulo_nomina/nom_txt_return.php'

const creacionNominasTxtUrl =
  '../../../../../sigob/back/modulo_nomina/nom_creacion_txt.php'

const descargarNominaTxtUrl = (correlativo) =>
  `../../../../../sigob/back/modulo_nomina/nom_txt_descargas.php?correlativo=${correlativo}`

const enviarCalculoNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_calculonomina_registro.php'

const confirmarPeticionNominaUrl =
  '../../../../../sigob/back/modulo_nomina/nom_status_peticiones.php'

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
    console.log(json)
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
  let loader = document.getElementById('select-request-loader')
  if (loader) {
    showLoader('select-request-loader')
  }

  try {
    let res = await fetch(obtenerPeticionesNominaUrl)

    let data = await res.json()
    data.forEach((el) => {
      el.empleados = JSON.parse(el.empleados)
      el.asignaciones = JSON.parse(el.asignaciones)
      el.deducciones = JSON.parse(el.deducciones)
      el.aportes = JSON.parse(el.aportes)

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
    if (loader) {
      hideLoader('select-request-loader')
    }
  }
}

const getNominaTxt = async (data) => {
  let loader = document.getElementById('pay-nom-loader')
  if (loader) {
    showLoader('pay-nom-loader')
  }

  try {
    let res = await fetch(obtenerNominasTxtUrl, {
      method: 'POST',
      body: JSON.stringify(data),
    })

    // data.informacion_empleados = JSON.parse(data.informacion_empleados)

    let json = await res.json()

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })
  } finally {
    if (loader) {
      hideLoader('pay-nom-loader')
    }
  }
}

const generarNominaTxt = async ({ correlativo, identificador }) => {
  let loader = document.getElementById('pay-nom-loader')
  if (loader) {
    showLoader('pay-nom-loader')
  }

  try {
    let res = await fetch(creacionNominasTxtUrl, {
      method: 'POST',
      body: JSON.stringify({ correlativo, identificador }),
    })

    let json = await res.text()

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error en descargat TXT',
    })
  } finally {
    if (loader) {
      hideLoader('pay-nom-loader')
    }
  }
}

const descargarNominaTxt = async (correlativo) => {
  let loader = document.getElementById('pay-nom-loader')
  if (loader) {
    showLoader('pay-nom-loader')
  }

  console.log(correlativo)
  try {
    let res = await fetch(descargarNominaTxtUrl(correlativo))

    let json = await res.text()

    return json
  } catch (e) {
    console.log(e)
    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error en descargat TXT',
    })
  } finally {
    if (loader) {
      hideLoader('pay-nom-loader')
    }
  }
}

const getComparacionNomina = async (obj) => {
  if (!obj) return
  let { correlativo, nombre_nomina } = obj

  showLoader('request-comparation-loader')
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
      message: 'Error al obtener peticiones',
    })
  } finally {
    hideLoader('request-comparation-loader')
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

    // RECARGAR TABLA DE PETICIONES AL ENVIAR PETICIÓN DE CÁLCULO DE NOMINA
    // loadRequestTable()

    return json
  } catch (e) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Error al obtener nominas',
    })

    return false
  }
}

const confirmarPeticionNomina = async (correlativo) => {
  let formData = new FormData()
  formData.append('correlativo', correlativo)
  try {
    let res = await fetch(confirmarPeticionNominaUrl, {
      method: 'POST',
      body: formData,
    })

    let text = await res.text()

    return await confirmNotification({
      type: NOTIFICATIONS_TYPES.done,
      message: text,
    })
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
  getNominaTxt,
  generarNominaTxt,
  descargarNominaTxt,
  sendCalculoNomina,
  getComparacionNomina,
  confirmarPeticionNomina,
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
      nacionalidad,
      status,
      discapacidades,
      id_dependencia,
      instruccion_academica,
      cod_cargo,
    } = empleado

    // Datos dinámicos

    empleado.nacionalidad = nacionalidad == 1 ? 'EXTRANJERO' : 'VENEZOLANO'
    empleado.status = status == 1 ? 'ACTIVO' : 'INACTIVO'
    discapacidades = discapacidades == 1 ? 'SI' : 'NO'

    id_dependencia = dependencias.find((el) => el.id == id_dependencia)
    instruccion_academica = profesiones.find(
      (el) => el.id == instruccion_academica
    )
    cod_cargo = cargos.find((el) => el.id == cod_cargo)

    empleado.id_dependencia = id_dependencia
      ? id_dependencia.name
      : 'No disponible'

    empleado.instruccion_academica = instruccion_academica
      ? instruccion_academica.name
      : 'No disponible'

    empleado.cod_cargo = cod_cargo ? cod_cargo.name : 'No disponible'

    return empleado
  })
}

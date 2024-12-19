import { getDistribucionEntes } from '../api/form_entes.js'
import {
  getPreAsignacionEnte,
  getPreAsignacionEntes,
} from '../api/pre_entes.js'
import { registrarSolicitudDozavo } from '../api/pre_solicitudesDozavos.js'
import { loadSolicitudesDozavosTable } from '../controllers/pre_solicitudesDozavosTable.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separadorLocal,
  tableLanguage,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const pre_solicitudEnte_card = async ({
  elementToInsert,
  ejercicioId,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('solicitud-ente-card')
  if (oldCardElement) oldCardElement.remove()

  let distribucionEntes = await getPreAsignacionEntes()

  console.log(distribucionEntes)
  if (distribucionEntes.fullInfo.length === 0) {
    toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'No hay entes con distribucion asignada',
    })
    return
  }
  const crearFilas = () => {
    let fila = distribucionEntes.fullInfo
      .filter(
        (distribucion) =>
          Number(distribucion.status) === 1 &&
          Number(ejercicioId) === Number(distribucion.id_ejercicio)
      )
      .map((distribucion) => {
        return `  <tr>
              <td>${distribucion.ente_nombre}</td>
              <td>${
                distribucion.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'
              }</td>
              <td>
                <button class='btn btn-secondary btn-sm' data-validarid="${
                  distribucion.id
                }">Detalles</button>
              </td>
            </tr>`
      })

    return fila
  }

  let card = ` <div class='card slide-up-animation' id='solicitud-ente-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Distribución de entes</h5>
          <small class='mt-0 text-muted'>
            Elija la distribución de entes para realizar solicitud de dozavo
          </small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <table id='entes-elegir-table' class='table table-striped table-xs'>
          <thead>
            <th>NOMBRE</th>
            <th>TIPO</th>
            <th>ACCIÓN</th>
          </thead>
          <tbody>${crearFilas()}</tbody>
        </table>
      </div>
      
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  validarEntesTabla()

  let cardElement = d.getElementById('solicitud-ente-card')
  // let formElement = d.getElementById('solicitud-ente')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  async function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.dataset.validarid) {
      closeCard()
    }
  }

  async function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  // cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)

  function validarEntesTabla() {
    let entesTable = new DataTable('#entes-elegir-table', {
      scrollY: 200,
      colums: [
        { data: 'entes_nombre' },
        { data: 'entes_tipo' },
        { data: 'acciones' },
      ],
      language: tableLanguage,
      layout: {
        topStart: function () {
          let toolbar = document.createElement('div')
          toolbar.innerHTML = `
                  <h5 class="text-center mb-0">Lista de entes con distribución confirmada:</h5>
                            `
          return toolbar
        },
        topEnd: { search: { placeholder: 'Buscar...' } },
        bottomStart: 'info',
        bottomEnd: 'paging',
      },
    })
  }
}

// OTRO COMPONENTE

const mesesOptions = () => {
  let mesActual = new Date().getMonth()

  const meses = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
  ]

  let mesesOptionsElement = meses.map((mes, index) => {
    if (index)
      if (mesActual === index)
        return `<option value='${index}' selected>${mes}</option>`
    return `<option value='${index}'>${mes}</option>`
  })

  let options = [`<option value=''>Elegir...</option>`, ...mesesOptionsElement]

  return options.join('')
}

export const pre_solicitudGenerar_card = async ({
  elementToInsert,
  enteId,
  ejercicioId,
}) => {
  let fieldList = { mes: '', descripcion: '' }
  let fieldListErrors = {
    mes: {
      value: true,
      message: 'Elija un mes válido',
      type: 'text',
    },
    descripcion: {
      value: true,
      message: 'Escriba descripción de la solicitud',
      type: 'text',
    },
  }

  const oldCardElement = d.getElementById('solicitud-distribucion-form-card')
  if (oldCardElement) oldCardElement.remove()

  let asignacionEnte = await getPreAsignacionEnte(enteId)

  let dependencias = asignacionEnte.dependencias

  let actividadesEnte = asignacionEnte.actividades_entes
  console.log(asignacionEnte)

  const listaDependencias = () => {
    let dozavoMontoTotal = 0
    let montosActividadDistribuido = {}
    let montosActividadDozavo = {}
    actividadesEnte.forEach((distribucion) => {
      montosActividadDozavo[distribucion.actividad] = 0
      montosActividadDistribuido[distribucion.actividad] = 0
      distribucion.distribucion_partidas.forEach((partida) => {
        let doceavaParte = Number(partida.monto) / 12

        dozavoMontoTotal += doceavaParte
        montosActividadDozavo[distribucion.actividad] += doceavaParte
        montosActividadDistribuido[distribucion.actividad] += partida.monto
      })
    })

    // Guardar total de dozavo
    fieldList.dozavoMontoTotal = dozavoMontoTotal

    let dependenciasElement =
      dependencias.length > 0
        ? dependencias
            .filter((dependencia) =>
              actividadesEnte.some(
                (distribucionActividad) =>
                  distribucionActividad.actividad === dependencia.actividad
              )
            )
            .sort((a, b) => a.actividad - b.actividad)
            .map((dependencia) => {
              return `<li class='list-group-item'>
                  <p class='mb-2'>${dependencia.ente_nombre}</p>

                  <p class='mb-0'>
                    <b>Actividad: </b>
                    <span class='px-2 rounded text-secondary'>
                      ${dependencia.actividad}
                    </span>
                  </p>
                  <p class="mb-0">
                    <b>Distribuido: </b>
                    <span class='px-2 rounded text-secondary'>
                      ${separadorLocal(
                        montosActividadDistribuido[dependencia.actividad]
                      )}
                      Bs
                    </span>
                  </p>
                  <p class="mb-0">
                    <b>Dozavo:</b>
                    <span class='px-2 rounded text-secondary'>
                      ${separadorLocal(
                        montosActividadDozavo[dependencia.actividad]
                      )}
                      Bs
                    </span>
                  </p>
                </li>`
            })
            .join('')
        : ''

    return `
          <ul class='list-group mb-4'>${dependenciasElement}</ul>        
      `
  }

  const crearFilas = () => {
    let fila = []

    actividadesEnte.forEach((distribucion) => {
      distribucion.distribucion_partidas.forEach((partida) => {
        let dozavo = partida.monto / 12
        let codigo = `${
          partida.sector_informacion ? partida.sector_informacion.sector : '0'
        }.${
          partida.programa_informacion
            ? partida.programa_informacion.programa
            : '0'
        }.${
          // partida.proyecto_informacion == 0
          //   ? '00'
          //   : partida.proyecto_informacion.proyecto
          '00'
        }.${distribucion.actividad == 0 ? '00' : distribucion.actividad}`

        fila.push(`        <td>${codigo}</td>
        <td>${partida.partida}</td>
        <td>${separadorLocal(partida.monto)} Bs</td>
        <td>${separadorLocal(dozavo.toFixed(3))} Bs</td>
        </tr>`)
      })
    })

    return fila.join('')
  }

  let card = `    <div class='card slide-up-animation' id='solicitud-distribucion-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Generar solicitud de dozavo</h5>
          <small class='mt-0 text-muted'>
            Valide la información para generar la solicitud del dozavo
          </small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class='card-body'>
        <div class='row'>
          <div class='text-center col-sm-6'>
            <h5>Actividades:</h5>${listaDependencias()}
           
          </div>
          <div class='col-sm-6'>
            <form id='solicitud-distribucion-form'>
             <h5>
              Monto total de dozavo:
              <span class='px-2 rounded text-green-600 bg-green-100'>
                ${separadorLocal(fieldList.dozavoMontoTotal)} Bs
              </span>
            </h5>
              <h4>Información para generar solicitud</h4>
              <div class='form-group'>
                <label for='mes' class='form-label'>
                  Mes de solicitud (mes actual por defecto)
                </label>
                <select
                  class='form-control solicitud-input chosen-select'
                  type='text'
                  name='mes'
                  id='mes'
                >
                  ${mesesOptions()}
                </select>
              </div>

              <div class='form-group'>
                <label for='mes' class='form-label'>
                  Descripción
                </label>
                <textarea
                  class='form-control solicitud-input'
                  name='descripcion'
                  id='descripcion'
                  rows='2'
                ></textarea>
              </div>
            </form>
          </div>
        </div>
        <div>
          <table
            id='distribucion-ente-table'
            class='table table-striped table-sm'
          >
            <thead>
              <th>S/P/P/A</th>
              <th>PARTIDA</th>
              <th>MONTO</th>
              <th>DOZAVO</th>
            </thead>
            <tbody>${crearFilas()}</tbody>
          </table>
        </div>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='solicitud-generar'>
          Generar solicitud
        </button>
        <button class='btn btn-danger' id='solicitud-cancelar'>
          Cancelar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  validarEntesTabla()

  let cardElement = d.getElementById('solicitud-distribucion-form-card')
  // let formElement = d.getElementById('solicitud-distribucion-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  async function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === 'solicitud-cancelar') {
      closeCard()
    }

    if (e.target.id === 'solicitud-generar') {
      let inputs = d.querySelectorAll('.solicitud-input')

      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Hay campos inválidos',
        })
        return
      }

      if (Object.values(fieldList).some((el) => !el)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No se ha completado la información necesaria',
        })
        return
      }

      let dozavoMontoTotal = 0

      let partidasDozavos = []

      actividadesEnte.forEach((distribucion) => {
        distribucion.distribucion_partidas.forEach((partida) => {
          let monto = partida.monto / 12
          partidasDozavos.push({
            id: Number(partida.id),
            monto: Number(monto.toFixed(2)),
          })
        })
      })

      // let dozavoPartidas = asignacionEnte.distribucion_partidas.map(
      //   (distribucion) => {
      //     dozavoMontoTotal += Number(distribucion.monto)

      //     let dozavaParte = Number(distribucion.monto) / 12

      //     return {
      //       id_distribucion: Number(distribucion.id_distribucion),
      //       monto: dozavaParte.toFixed(2),
      //     }
      //   }
      // )

      let dozavoInformacion = {
        id_ente: asignacionEnte.id_ente,
        descripcion: fieldList.descripcion,
        monto: fieldList.dozavoMontoTotal,
        partidas: partidasDozavos,
        id_ejercicio: ejercicioId,
        tipo: 'D',
        mes: fieldList.mes,
      }

      console.log(dozavoInformacion)
      enviarInformacion(dozavoInformacion)
    }
  }

  async function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: `¿Desea generar la solicitud de dozavo del ente ${asignacionEnte.ente_nombre}`,
      successFunction: async function () {
        let res = await registrarSolicitudDozavo(data)
        if (res.success) {
          closeCard()
          loadSolicitudesDozavosTable()
        }
      },
    })
  }

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)

  function validarEntesTabla() {
    let entesTable = new DataTable('#distribucion-ente-table', {
      scrollY: 200,

      language: tableLanguage,
      layout: {
        topStart: function () {
          let toolbar = document.createElement('div')
          toolbar.innerHTML = `
                  <h5 class="text-center mb-0">Detalles de la distribucion presupuestaria del ente:</h5>
                            `
          return toolbar
        },
        topEnd: { search: { placeholder: 'Buscar...' } },
        bottomStart: 'info',
        bottomEnd: 'paging',
      },
    })
  }
}

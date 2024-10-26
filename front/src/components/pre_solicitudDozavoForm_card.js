import { getDistribucionEntes } from '../api/form_entes.js'
import {
  getPreAsignacionEnte,
  getPreAsignacionEntes,
} from '../api/pre_entes.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const tableLanguage = {
  decimal: '',
  emptyTable: 'No hay datos disponibles en la tabla',
  info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
  infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
  infoFiltered: '(filtrado de _MAX_ entradas totales)',
  infoPostFix: '',
  thousands: ',',
  lengthMenu: 'Mostrar _MENU_',
  loadingRecords: 'Cargando...',
  processing: '',
  search: 'Buscar:',
  zeroRecords: 'No se encontraron registros coincidentes',
  paginate: {
    first: 'Primera',
    last: 'Última',
    next: 'Siguiente',
    previous: 'Anterior',
  },
  aria: {
    orderable: 'Ordenar por esta columna',
    orderableReverse: 'Orden inverso de esta columna',
  },
}
const d = document

export const pre_solicitudEnte_card = async ({ elementToInsert }) => {
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

  const crearFilas = () => {
    let fila = distribucionEntes.fullInfo
      .filter((distribucion) => Number(distribucion.status) === 1)
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
        <table id='entes-elegir-table' class='table table-striped table-sm'>
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
      pre_solicitudGenerar_card({
        elementToInsert: 'solicitudes-dozavos-view',
        enteId: e.target.dataset.validarid,
      })
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

  cardElement.addEventListener('input', validateInputFunction)
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

export const pre_solicitudGenerar_card = async ({
  elementToInsert,
  enteId,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('solicitud-distribucion-form-card')
  if (oldCardElement) oldCardElement.remove()

  let asignacionEnte = await getPreAsignacionEnte(enteId)

  const crearFilas = () => {
    let fila = asignacionEnte.distribucion_partidas.map((distribucion) => {
      let sector_codigo = `${distribucion.sector_informacion.sector}.${distribucion.sector_informacion.programa}.${distribucion.sector_informacion.proyecto}`

      return `<tr><td>${distribucion.sector_informacion.nombre}</td>
      <td>${sector_codigo}</td>
      <td>${distribucion.partida}</td>
      <td>${distribucion.monto}</td></tr>`
    })

    return fila.join('')
  }

  let card = ` <div class='card slide-up-animation' id='solicitud-distribucion-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Distribución presupuestaria de ente</h5>
          <small class='mt-0 text-muted'>
            Visualice la información inicial para generar la solicitud de dozavo del ete
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
        <table id='distribucion-ente-table' class='table table-striped table-sm'>
          <thead>
            <th>SECTOR NOMBRE</th>
            <th>SECTOR CODIGO</th>
            <th>PARTIDA</th>
            <th>MONTO</th>
            
            
          </thead>
          <tbody>${crearFilas()}</tbody>
        </table>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='solicitud-generar'>
          Generar Dozavo
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

    if (e.target.id === 'solicitud-generar') {
      let dozavoMontoTotal = 0

      let dozavoPartidas = asignacionEnte.distribucion_partidas.map(
        (distribucion) => {
          dozavoMontoTotal += Number(distribucion.monto)

          let doceavaParte = Number(distribucion.monto) / 12

          return {
            id_distribucion: Number(distribucion.id_distribucion),
            monto: doceavaParte,
          }
        }
      )

      let dozavoInformacion = {
        id_ente: asignacionEnte.id_ente,
        descripcion: 'DESCRIPCION EJEMPLO',
        monto: dozavoMontoTotal,
        partidas: dozavoPartidas,
      }

      console.log(dozavoInformacion)
      // confirmNotification({
      //   type: NOTIFICATIONS_TYPES.send,
      //   message: `¿Desea generar la solicitud de dozavo del ente ${asignacionEnte.ente_nombre}`,
      //   successFunction: function () {
      //     console.log('hola')
      //   },
      // })
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
                  <h5 class="text-center mb-0">Distribución presupuestaria del ente:</h5>
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

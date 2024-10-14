import {
  aceptarDistribucionEnte,
  getDistribucionEnte,
  getDistribucionEntes,
  rechazarDistribucionEnte,
} from '../api/form_entes.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

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

export const form_distribucion_ente_card = async ({
  elementToInset,
  distribucionId,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('distribucion-ente-form-card')
  if (oldCardElement) oldCardElement.remove()

  let distribucionEnte = await getDistribucionEnte(distribucionId)

  console.log(distribucionEnte)

  const crearFilasPartidas = () => {
    let fila = distribucionEnte.partidas.map((partida) => {
      return `  <tr>
                <td>${partida.partida}</td>
                <td>${partida.monto}</td>
                <td>${partida.descripcion}</td>
               
              </tr>`
    })

    return fila
  }

  let card = `   <div class='card slide-up-animation' id='distribucion-ente-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Validar información de plan operativo</h5>
          <small class='mt-0 text-muted'>
            Introduzca los datos para la verificar el plan operativo
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
        <h5>Nombre: ${distribucionEnte.ente_nombre}</h5>
        <h6>
          Tipo: ${
            distribucionEnte.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'
          }
        </h6>
        <h6>
          Cantidad de partidas utilizadas:
          <b id='monto-total-asignado'>${distribucionEnte.partidas.length}</b>
        </h6>
        <div class='d-flex gap-2 justify-content-between'>
          <h6>
            Monto total asignado:
            <b id='monto-total-asignado'>
              ${separarMiles(distribucionEnte.monto_total)}
            </b>
          </h6>
        </div>
        <div>
          <table
            id='distribucion-ente-table'
            class='table table-striped table-sm'
          >
            <thead>
              <th>partida</th>
              <th>MONTO SOLICITADO</th>
              <th>DESCRIPCION</th>
            </thead>
            <tbody>${crearFilasPartidas()}</tbody>
          </table>
        </div>
      </div>
      <div class='card-footer d-flex justify-content-center gap-2'>
      ${
        distribucionEnte.status === 0
          ? `<button class='btn btn-primary' id='distribucion-ente-aceptar'>
          Guardar
        </button>
        <button class='btn btn-danger' id='distribucion-ente-rechazar'>
          Rechazar
        </button>`
          : distribucionEnte.status === 1
          ? `<span class='btn btn-success'>Esta distribucion fue aprobada</span>`
          : `<span class='btn btn-warning'>Esta distribucion fue rechazada</span>`
      }
        
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  validarEntesTabla()

  let cardElement = d.getElementById('distribucion-ente-form-card')
  let formElement = d.getElementById('distribucion-ente-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }
    if (e.target.id === 'distribucion-ente-aceptar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: `¿Desea aceptar esta distribución de presupuesto?`,
        successFunction: async function () {
          let res = await aceptarDistribucionEnte({
            id: distribucionId,
          })

          if (res.success) {
            closeCard()
          }
        },
      })
    }
    if (e.target.id === 'distribucion-ente-rechazar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: `¿Desea aceptar esta distribución de presupuesto?`,
        successFunction: async function () {
          let res = await rechazarDistribucionEnte({
            id: distribucionId,
          })

          if (res.success) {
            closeCard()
          }
        },
      })
    }
  }

  async function validateInputFunction(e) {
    // fieldList = validateInput({
    //   target: e.target,
    //   fieldList,
    //   fieldListErrors,
    //   type: fieldListErrors[e.target.name].type,
    // })
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

let entesTable
function validarEntesTabla() {
  entesTable = new DataTable('#distribucion-ente-table', {
    scrollY: 150,
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
                <h5 class="text-center mb-0">Lista de partidas solicitadas por el ente:</h5>
                          `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

import { getPartidas } from '../api/partidas.js'
import { deleteSolicitudDozeavo } from '../controllers/pre_solicitudesDozavosTable.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
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
export const pre_solicitudDozavo_card = async ({ elementToInsert, data }) => {
  const modalElemet = d.getElementById('card-solicitud-dozavo')
  if (modalElemet) modalElemet.remove()

  let listaPartidas = await getPartidas()

  console.log(listaPartidas.fullInfo)

  let {
    id,
    numero_orden,
    numero_compromiso,
    ente,
    tipo_ente,
    descripcion,
    tipo,
    monto,
    fecha,
    partidas,
  } = data

  let partidasLi = partidas
    ? partidas
        .map((partida) => {
          let partidaEncontrada = listaPartidas.fullInfo.find(
            (par) => par.id === partida.id
          )
          let informacion = { ...partida, ...partidaEncontrada }

          return ` <tr class=''>
              <td class=''>
                ${informacion.partida}
              </td>
               <td class=''>
                ${informacion.monto}
              </td>
              <td class=''>
                ${informacion.descripcion}
              </td>
             
            </tr>`
        })
        .join('')
    : `   <li>No posee partidas asociadas</li>`

  let card = `  <div class='card slide-up-animation pb-2' id='card-solicitud-dozavo'>
      <header class='card-header'>
        
        <button
          data-close='btn-close-report'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </header>
      <div class='card-body text-center'>
        <div class='w-100 align-self-center fs-5'>
          <div class='row'>
            <div class='col'>
              <b>Ente: </b>
              <p>${ente}</p>
            </div>
            <div class='col'>
              <b>Tipo de ente: </b>
              <p>${tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'}</p>
            </div>
            <div class='col'>
              <b>Monto total: </b>
              <p>${monto}</p>
            </div>
            <div class='col'>
              <b>descripcion: </b>
              <p>${descripcion}</p>
            </div>
          </div>

          <div class='row'>
            <div class='col'>
              <b>Fecha de orden: </b>
              <p>${fecha}</p>
            </div>
            <div class='col'>
              <b>Orden: </b>
              <p>${numero_orden}</p>
            </div>
            <div class='col'>
              <b>Tipo: </b>
              <p>${tipo == 'A' ? 'Aumenta' : 'Disminuye'}</p>
            </div>
            <div class='col'>
              <b>Compromiso: </b>
              <p>${numero_compromiso}</p>
            </div>
          </div>
        </div>
        <div class='w-100'>
          <table
            class='table table-sm table-responsive mx-auto'
            style='width: 100%'
            id="solicitud-partidas"
          >
            <thead>
              <th>Partida</th>
              <th>Monto</th>
              <th>descripcion</th>
            </thead>
            <tbody>${partidasLi}</tbody>
          </table>
        </div>
      </div>
      <div class='card-footer d-flex align-items-center justify-content-center gap-2 py-0'>
        <button
          data-confirmarid='${id}'
          class='btn btn-primary size-change-animation'
        >
          Aceptar
        </button>
        <button
          data-rechazarid='${id}'
          class='btn btn-danger size-change-animation'
        >
          Rechazar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforebegin', card)

  let listDataTable = new DataTable('#solicitud-partidas', {
    responsive: true,
    scrollY: 120,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
                  `
        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
  const closeModalCard = () => {
    let cardElement = d.getElementById('card-solicitud-dozavo')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    // formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.rechazarid) {
      let id = e.target.dataset.rechazarid
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Seguro de rechazar esta solicitud de dozavo?',
        successFunction: async function () {
          let row = d.querySelector(`[data-detalleid="${id}"]`).closest('tr')

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Solicitud rechazada',
          })

          deleteSolicitudDozeavo({ row, id })
          closeModalCard()
        },
      })
    }

    if (e.target.dataset.close) {
      closeModalCard()
    }
  }

  d.addEventListener('click', validateClick)
}

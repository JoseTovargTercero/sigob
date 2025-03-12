import {
  consultarCompromiso,
  generarCompromisoPdf,
} from '../api/pre_compromisos.js'
import {
  aceptarDozavo,
  entregarSolicitud,
  rechazarDozavo,
} from '../api/pre_solicitudesDozavos.js'
import { deleteSolicitudDozeavoRow } from '../controllers/pre_solicitudesDozavosTable.js'
import { confirmNotification, separadorLocal } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES, meses } from '../helpers/types.js'
import { pre_identificarCompromiso } from './pre_identificarCompromiso.js'

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
export const pre_solicitudDozavo_card = async ({
  elementToInsert,
  data,
  reset,
}) => {
  const oldCardElement = d.getElementById('card-solicitud-dozavo')

  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  let {
    id,
    numero_orden,
    ente,
    id_ejercicio,
    tipo_ente,
    descripcion,
    tipo,
    monto,
    fecha,
    partidas,
    status,
    mes,
  } = data

  let compromiso = await consultarCompromiso({
    id_registro: id,
    nombre_tabla: 'solicitud_dozavos',
  })

  console.log(data)

  let partidasLi = partidas
    ? partidas
        .map((partida) => {
          return ` <tr class=''>
              <td class=''>
                ${partida.partida}
              </td>
               <td class=''>
                ${separadorLocal(partida.monto)}
              </td>
              <td class=''>
                ${partida.descripcion}
              </td>
             
            </tr>`
        })
        .join('')
    : `   <li>No posee partidas asociadas</li>`

  const validateStatus = () => {
    if (status === 0) {
      return `  
            <button class='btn btn-secondary' data-compromisoid='${id}'>Descargar compromiso</button>
            <button class='btn btn-primary' data-entregarid='${id}'>Entregar</button>
            `
    }
    if (status === 1) {
      return `<button class="btn btn-danger" data-rechazarid="${id}">Rechazar</button>
       <button class="btn btn-primary" data-confirmarid="${id}">Aceptar</button>`
    }
    if (status === 3) {
      return ` <span class='p-2 rounded text-white bg-red-600 text-bold'>
              Rechazado
            </span>`
    }
    if (status === 4) {
      return ` <span class='btn btn-info'>
              Entregado
            </span> <button class='btn btn-secondary' data-compromisoid='${id}'>Descargar compromiso</button>`
    }
  }

  let card = ` <div class='card slide-up-animation pb-2' id='card-solicitud-dozavo'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Información de solicitud de dozavo</h5>
          <small class='mt-0 text-muted'>
            Certifique la información y proceda a realizar una acción
          </small>
        </div>
        <button
          data-close='btn-close-report'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>

      <div class='card-body text-center'>
        <div class='w-100 align-self-center fs-5'>
          <div class='row'>
            <div class='col-sm'>
              <b>Ente: </b>
              <p>${ente.ente_nombre}</p>
            </div>
            <div class='col-sm'>
              <b>Tipo de ente: </b>
              <p>${ente.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'}</p>
            </div>
            <div class='col-sm'>
              <b>Monto total: </b>
              <p>${separadorLocal(monto)}</p>
            </div>
            <div class='col-sm'>
              <b>Descripción: </b>
              <p>${descripcion}</p>
            </div>
          </div>

          <div class='row'>
            <div class='col-sm'>
              <b>Fecha de orden / Mes: </b>
              <p>${fecha} - ${meses[mes]}</p>
            </div>
            <div class='col-sm'>
              <b>Orden: </b>
              <p>${numero_orden}</p>
            </div>
            <div class='col-sm'>
              <b>Tipo: </b>
              <p>${tipo == 'A' ? 'Aumenta' : 'Disminuye'}</p>
            </div>
            <div class='col-sm'>
              <b>Compromiso: </b>
              <p>
                ${compromiso ? compromiso.numero_compromiso : 'No registrado'}
              </p>
            </div>
          </div>
        </div>
        <div class='w-100'>
          <table
            class='table table-xs table-responsive mx-auto'
            style='width: 100%'
            id='solicitud-partidas'
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
        ${validateStatus()}
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforebegin', card)

  let cardElement = d.getElementById('card-solicitud-dozavo')

  let listDataTable = new DataTable('#solicitud-partidas', {
    responsive: true,
    scrollY: 120,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
        <h5 class="text-center">Lista de partidas afectadas</h5>
                  `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
  function closeCard(card) {
    card.remove()
    d.removeEventListener('click', validateClick)
    // formElement.removeEventListener('input', validateInputFunction)
    return false
  }

  async function validateClick(e) {
    if (e.target.dataset.confirmarid) {
      pre_identificarCompromiso({
        id: e.target.dataset.confirmarid,
        elementToInsert: 'solicitudes-dozavos-view',
        registerCompromise: {
          id,
          nombre_tabla: 'solicitud_dozavos',
          descripcion,
          id_ejercicio,
        },
        acceptFunction: async function (codigo) {
          let res = await aceptarDozavo(e.target.dataset.confirmarid, codigo)

          if (res.success) {
            closeCard(cardElement)
          }
          return res
        },
        reset,
      })
    }

    if (e.target.dataset.rechazarid) {
      let id = e.target.dataset.rechazarid
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Seguro de rechazar esta solicitud de dozavo?',
        successFunction: async function () {
          let response = await rechazarDozavo(id)
          console.log(response)
          if (response.success) {
            let row = d.querySelector(`[data-detalleid="${id}"]`).closest('tr')
            deleteSolicitudDozeavoRow({ row, id })
            closeCard(cardElement)
          }
        },
      })
    }

    if (e.target.dataset.compromisoid) {
      generarCompromisoPdf(compromiso.id, compromiso.correlativo)
    }

    if (e.target.dataset.entregarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          '¿Seguro de entregar la confirmación de esta solicitud al ente?',
        successFunction: async function () {
          let response = await entregarSolicitud(e.target.dataset.entregarid)

          console.log(response)
          if (response.success) {
            closeCard(cardElement)
            reset()
          }
        },
      })
    }

    if (e.target.dataset.close) {
      closeCard(cardElement)
    }
  }

  cardElement.addEventListener('click', validateClick)
}

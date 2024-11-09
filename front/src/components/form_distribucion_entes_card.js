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

export const form_distribucion_entes_card = ({
  elementToInsert,
  informacion,
}) => {
  //   let fieldList = { ejemplo: '' }
  //   let fieldListErrors = {
  //     ejemplo: {
  //       value: true,
  //       message: 'mensaje de error',
  //       type: 'text',
  //     },
  //   }

  let nombreCard = 'distribucion-ente'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) oldCardElement.remove()

  let rows = informacion.distribuciones.map((distribucion) => {
    let sector = distribucion.sector,
      programa = distribucion.programa,
      proyecto = distribucion.proyecto,
      actividad = informacion.actividad,
      partida = distribucion.partida,
      monto = distribucion.monto

    return `<tr class=''>
                  <td>${sector}</td>
                  <td>${programa}</td>
                  <td>${proyecto}</td>
                  <td>${actividad}</td>
                  <td>${partida}</td>
                  <td>${separarMiles(monto)}</td>
              </tr>`
  })

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>
            Distribucion de partidas para la actividad: ${informacion.actividad}
          </h5>
          <small class='mt-0 text-muted'>CAMBIAR TEXTO</small>
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
        <table

          id='distribucion-ente-table'
              class='table table-striped table-sm'
              style='width:100%'
        >
          <thead class='w-100'>
            <th>SECTOR</th>
            <th>PROGRAMA</th>
            <th>PROYECTO</th>
            <th>ACTIVIDAD</th>
            <th>PARTIDA</th>
            <th>MONTO</th>
          </thead>
          <tbody>${rows.join('')}</tbody>
        </table>
      </div>
    </div>`

  let modal = `  <div class='modal-window' id='${nombreCard}-form-card'>
      <div class=' slide-up-animation'>${card}</div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', modal)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  let distribucionEnteTable = new DataTable('#distribucion-ente-table', {
    scrollY: 200,
    responsive: false,
    colums: [
      { data: 'elegir' },
      { data: 'sector_nombre' },
      { data: 'sector_codigo' },
      { data: 'partida' },
      { data: 'nombre' },
      { data: 'descripcion' },
    ],
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
        <h5 class="text-center text-blue-800">Lista de partidas: <b id="partidas-seleccionadas">${informacion.distribuciones.length}</b></h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    // cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
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

  //   formElement.addEventListener('submit', (e) => e.preventDefault())

  //   cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

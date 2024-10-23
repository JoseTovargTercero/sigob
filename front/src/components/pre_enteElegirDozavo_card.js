import { getAsignacionesEntes, getEntes } from '../api/form_entes.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { form_asignacion_entes_monto_card } from './form_asignacion_entes_monto_card.js'
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

export const pre_enteElegirDozavo_card = async ({
  elementToInset,
  ejercicioFiscal,
  nextComponentFunction,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }
  const oldCardElement = d.getElementById('entes-elegir-form-card')
  if (oldCardElement) oldCardElement.remove()

  let entes = await getEntes()
  let asignacionesEntes = await getAsignacionesEntes()

  console.log(asignacionesEntes)

  const crearFilas = () => {
    let fila = entes.fullInfo
      .filter(
        (ente) =>
          !asignacionesEntes.fullInfo.some(
            (asignaciones) => Number(ente.id) === Number(asignaciones.id_ente)
          )
      )
      .map((ente) => {
        return `  <tr>
              <td>${ente.ente_nombre}</td>
              <td>${
                ente.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'
              }</td>
              <td>
                <button class='btn btn-secondary btn-sm' data-detalleid="${
                  ente.id
                }">Asignar</button>
              </td>
            </tr>`
      })

    return fila
  }

  let card = `<div class='card slide-up-animation' id='entes-elegir-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Lista de entes registrados</h5>
          <small class='mt-0 text-muted'>
            Asigne a un ente el mondo a utilizar anual para sus planes operativos
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
              <div>
          <table id="entes-elegir-table" class='table table-striped table-sm'>
            <thead>
              <th>NOMBRE</th>
              <th>TIPO</th>
              <th>ACCIÓN</th>
            </thead>
            <tbody>${crearFilas()}</tbody>
          </table>
        </div>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='entes-elegir-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  validarEntesTabla()
  let cardElement = d.getElementById('entes-elegir-form-card')
  let formElement = d.getElementById('entes-elegir-form')

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

    if (e.target.dataset.detalleid) {
      closeCard()
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
  entesTable = new DataTable('#entes-elegir-table', {
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
                <h5 class="text-center mb-0">Lista de entes pendientes por asignación anual:</h5>
                          `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

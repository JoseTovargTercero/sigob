import { generarCompromisoPdf } from '../api/pre_compromisos.js'
import { aceptarGasto, rechazarGasto } from '../api/pre_gastos.js'
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
import { pre_gastos_form_card } from './pre_gastos_form_card.js'
const d = document

export const pre_gastosDetalles = ({
  elementToInsert,
  ejercicioFiscal,
  data,
  recargarEjercicio,
}) => {
  console.log(data)

  // let sector_programa_proyecto = `${
  //   data.informacion_distribucion ? data.informacion_distribucion.sector : '0'
  // }.${
  //   data.informacion_distribucion ? data.informacion_distribucion.programa : '0'
  // }.${
  //   data.informacion_distribucion.id_actividad == 0
  //     ? '00'
  //     : data.informacion_distribucion.id_actividad
  // }`

  let nombreCard = 'gastos-detalles'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) closeCard(oldCardElement)

  const distribucionLista = () => {
    let filas = data.informacion_distribuciones.map((el) => {
      return `  <tr>
          <td>
            ${el.sector}.${el.programa}.${el.sector}.${el.partida}
          </td>
          <td>${separadorLocal(el.monto)}</td>
        </tr>`
    })

    return filas.join('')
  }

  let validarFooter = () => {
    return Number(data.status_gasto) === 0
      ? ` <button class='btn btn-danger' data-rechazarid="${data.id}">
  Cancelar
</button>
 <button class='btn btn-primary' data-aceptarid="${data.id}">
  Confirmar
</button>
<button class='btn btn-secondary'
    data-compromisoid='${data.id_compromiso}'>
    Descargar compromiso
  </button>
`
      : Number(data.status_gasto) === 1
      ? ` <button class='btn btn-secondary'
    data-compromisoid='${data.id_compromiso}'>
    Descargar compromiso
  </button>`
      : Number(data.status_gasto) === 3
      ? `<span class='btn btn-success'>Entregado</span>`
      : ` <button class='btn btn-danger' data-eliminarid='${data.id}'>
          Eliminar
        </button>
        <button class='btn btn-warning' data-editar='${data.id}'>
          Editar
        </button>`
  }

  let card = `    <div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Detalles de gasto de funcionamiento</h5>
          <small class='mt-0 text-muted'>
            Visualice los detalles del gasto y el beneficiario
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


        <div class='col-sm-4 d-flex flex-column align-items-center text-center'>
        <ul class="list-group list-group-flush w-100" style="max-height: 100vh !important">
        <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-user' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Beneficiario</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${data.beneficiario}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
                    <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-id-card' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Identificacion</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${data.identificador}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-paperclip' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Compromiso</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${
                                  data.correlativo || 'No registrado'
                                }</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

                    <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-collection' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Tipo de gasto</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${
                                  data.nombre_tipo_gasto || 'No obtenido'
                                }</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>


                    <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-purchase-tag-alt' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Monto</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${
                                  separadorLocal(data.monto_gasto) ||
                                  'No obtenido'
                                }</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

                    <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-calendar-alt' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Fecha</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">${
                                  data.fecha || 'No obtenido'
                                }</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

        <li class="list-group-item px-0">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar avtar-s border">
                        <i class='bx bx-check-double' ></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="row g-1">
                            <div class="col-6">
                                <h6 class="mb-0 text-left">Estatus</h6>
                            </div>
                            <div class="col-6 text-end">
                                <h6 class="mb-1">  ${
                                  Number(data.status_gasto) === 0
                                    ? ` <span class='badge badge-sm bg-secondary'>Pendiente</span>`
                                    : Number(data.status_gasto) === 1
                                    ? `<span class='badge badge-sm bg-success'>Procesado</span>`
                                    : Number(data.status_gasto) === 3
                                    ? `<span class='badge badge-sm bg-info'>Entregado</span>`
                                    : `<span class='badge badge-sm bg-danger'>Rechazado</span>`
                                }</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
            <div class="mt-3">
               <div class="mt-3">
            <b>Descripción:</b> ${data.descripcion_gasto || 'No obtenido'}
        </div>
        </div>
          </div>

          <div class='col-sm-8'>
            <h4 class='text-center mb-0'>Partidas afectadas:</h4>
            <div class='table-responsive'>
              <table
                id='distribuciones-table'
                class='table table-striped table-sm'
                style='width:100%'
              >
                <thead class='w-100'>
                  <th class=''>S/P/P/A</th>
                  <th class=''>Monto</th>
                </thead>
                <tbody>${distribucionLista()}</tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class='card-footer d-flex justify-content-center gap-2'>
        ${validarFooter()}
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

  let personaTable = new DataTable('#distribuciones-table', {
    responsive: true,
    scrollY: 100,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
             
                        `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  function closeCard(card) {
    // validateEditButtons()

    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
    }
    if (e.target.dataset.compromisoid) {
      console.log(e.target.dataset.compromisoid)
      generarCompromisoPdf(e.target.dataset.compromisoid, data.correlativo)
    }

    if (e.target.dataset.aceptarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al aceptar este gasto se descontará del presupuesto actual ¿Desea continuar?',
        successFunction: async function () {
          let res = await aceptarGasto(data.id)
          if (res.success) {
            // generarCompromisoPdf(
            //   res.compromiso.id_compromiso,
            //   res.compromiso.correlativo
            // )
            recargarEjercicio()
            closeCard(cardElement)
          }
        },
      })
    }
    if (e.target.dataset.rechazarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Rechazar este gasto hará que se elimine y reintegre el monto al presupuesto ¿Desea continuar?',
        successFunction: async function () {
          let res = await rechazarGasto(data.id)
          if (res.success) {
            recargarEjercicio()
            closeCard(cardElement)
          }
        },
      })
    }

    if (e.target.dataset.editar) {
      closeCard(cardElement)
      pre_gastos_form_card({
        elementToInsert,
        id: e.target.dataset.editar,
        recargarEjercicio,
        ejercicioFiscal,
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

  cardElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

const form_aceptarGasto = ({ elementToInsert, id, reset }) => {
  let fieldList = { codigo: '' }
  let fieldListErrors = {
    codigo: {
      value: true,
      message: 'El compromiso necesita una identificación',
      type: 'textarea',
    },
  }

  let nombreCard = 'aceptar-gasto'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) oldCardElement.remove()

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>

  <div class="card-header d-flex justify-content-between">
        <div class="">
          <h5 class="mb-0">Identificar compromiso antes de proceder</h5>
          
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-sm btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>

      <div class='card-body'>
        <form id='${nombreCard}-form'>
          <div class='form-group'>
            <label for='codigo' class='form-label'>
              Identificar compromiso
            </label>
            <input
              class='form-control partida-partida chosen-distribucion'
              type='text'
              name='codigo'
              id='codigo'
              placeholder='Identificación para el compromiso'
            />
          </div>
        </form>
        <div class='card-footer'>
          <button class='btn btn-primary' id='${nombreCard}-guardar'>
            Aceptar
          </button>
        </div>
      </div>
    </div>`

  let modal = `  <div class='modal-window' id='${nombreCard}-form-card'>
      <div class='w-60 slide-up-animation' style="max-width:400px">${card}</div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', modal)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

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

    if (e.target.id === `${nombreCard}-guardar`) {
      let input = d.getElementById('codigo')
      fieldList = validateInput({
        target: input,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[input.name].type,
      })

      if (fieldListErrors.codigo.value) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Campo invalido',
        })
        return
      }

      enviarInformacion()
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

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message:
        'Al aceptar este gasto se descontará del presupuesto actual ¿Desea continuar?',
      successFunction: async function () {
        console.log(id)

        let res = await aceptarGasto(id, fieldList.codigo)
        if (res.success) {
          generarCompromisoPdf(
            res.compromiso.id_compromiso,
            res.compromiso.correlativo
          )
          reset()
          closeCard()
        }
      },
    })
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

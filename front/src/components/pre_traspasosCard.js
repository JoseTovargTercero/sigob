import { aceptarTraspaso, rechazarTraspaso } from '../api/pre_traspasos.js'
import { loadTraspasosTable } from '../controllers/pre_traspasosTable.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separadorLocal,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const pre_traspasosCard = ({
  elementToInsert,
  data,
  ejercicioFiscal,
}) => {
  //   let fieldList = { ejemplo: '' }
  //   let fieldListErrors = {
  //     ejemplo: {
  //       value: true,
  //       message: 'mensaje de error',
  //       type: 'text',
  //     },
  //   }

  let nombreCard = 'traspasos'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  let informacion = {
    añadir: [],
    restar: [],
  }

  const documentoLabel = data.tipo === 1 ? 'Traslado' : 'Traspaso'

  const resumenPartidas = () => {
    data.detalles.forEach((distribucion) => {
      if (distribucion.tipo === 'A') {
        informacion.añadir.push(distribucion)
      } else {
        informacion.restar.push(distribucion)
      }
    })
    let filasAumentar = informacion.añadir.map((partida) => {
      let sppa = `
      ${partida.sector_denominacion ? partida.sector_denominacion : '00'}.${
        partida.programa_denominacion ? partida.programa_denominacion : '00'
      }.${
        partida.proyecto_denominacion ? partida.proyecto_denominacion : '00'
      }.${partida.id_actividad ? partida.id_actividad : '00'}`

      let montoFinal = Number(partida.monto) + Number(partida.monto_traspaso)

      return `  <tr>
          <td>${sppa}.${partida.partida}</td>
        ${data.status === 1 ? '' : `<td>${separadorLocal(partida.monto)}</td>`}
          <td class="table-success">+${separadorLocal(
            partida.monto_traspaso
          )}</td>
          <td class="table-primary">${
            data.status === 1
              ? `${separadorLocal(partida.monto)} Bs`
              : `${separadorLocal(montoFinal)} Bs`
          }</td>
        </tr>`
    })

    let filasDisminuir = informacion.restar.map((partida) => {
      let sppa = `
      ${partida.sector_denominacion ? partida.sector_denominacion : '00'}.${
        partida.programa_denominacion ? partida.programa_denominacion : '00'
      }.${
        partida.proyecto_denominacion ? partida.proyecto_denominacion : '00'
      }.${partida.id_actividad ? partida.id_actividad : '00'}`

      let montoFinal = Number(partida.monto) - Number(partida.monto_traspaso)

      return ` <tr>
          <td>${sppa}.${partida.partida}</td>
          ${
            data.status === 1 ? '' : `<td>${separadorLocal(partida.monto)}</td>`
          }
          
          <td class="table-danger">-${separadorLocal(
            partida.monto_traspaso
          )}</td>
           
          <td class="table-primary">${
            data.status === 1
              ? `${separadorLocal(partida.monto)} Bs`
              : `${separadorLocal(montoFinal)} Bs`
          }</td>
        </tr>`
    })

    let tablaAumentar = `   <table class="table table-xs">
        <thead>
          <th class="w-50">Distribucion</th>
          ${data.status === 1 ? '' : `<th class="w-10">Monto actual</th>`}
          
          <th class="w-10">Cambio</th>
          <th class="w-50">Monto Final</th>
        </thead>
        <tbody>${filasDisminuir.join('')}${filasAumentar.join('')}</tbody>
      </table>`

    return `<div id='card-body-part-3' class="slide-up-animation">
        <h5 class='text-center text-blue-600 mb-4'>Resumen de partidas</h5>
        ${tablaAumentar}
        
      </div>`
  }

  let validarFooter = () => {
    if (data.status === 0) {
      return `  <button class='btn btn-danger' id='btn-rechazar'>
      Rechazar
    </button>
    <button class='btn btn-primary' id='btn-aceptar'>
      Aceptar
    </button>`
    }
    if (data.status === 1) {
      return `<span class='btn btn-success'>Aceptado</span>`
    }
    if (data.status === 2) {
      return `<span class='btn btn-danger'>Rechazado</span>`
    }
  }

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
          <div class='card-header d-flex justify-content-between'>
            <div class=''>
              <h5 class='mb-0'>Detalles de ${documentoLabel}</h5>
              <small class='mt-0 text-muted'>Visualice los detalles del ${documentoLabel}</small>
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
          
          <h6>Numero de documento: <b>${data.n_orden}</b></h6>
          <h6>Fecha de creación del documento: <b>${data.fecha
            .split('-')
            .reverse()
            .join('/')}</b></h6>
          ${resumenPartidas()}
          </div>
          <div class='card-footer text-center'>

          
       ${validarFooter()}
      </div>
        </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)

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
    if (e.target.id === 'btn-aceptar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Está seguro de aceptar el traspaso?',
        successFunction: async () => {
          let res = await aceptarTraspaso(data.id)
          if (res.success) {
            closeCard(cardElement)
            loadTraspasosTable(ejercicioFiscal.id)
          }
        },
      })
    }
    if (e.target.id === 'btn-rechazar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Está seguro de rechazar el traspaso?',
        successFunction: async () => {
          let res = await rechazarTraspaso(data.id)
          if (res.success) {
            closeCard(cardElement)
            loadTraspasosTable(ejercicioFiscal.id)
          }
        },
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

  //   function enviarInformacion(data) {}

  //   formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

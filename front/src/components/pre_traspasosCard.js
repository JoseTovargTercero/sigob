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

  const resumenPartidas = () => {
    data.detalles.forEach((distribucion) => {
      if (distribucion.tipo === 'A') {
        informacion.añadir.push(distribucion)
      } else {
        informacion.restar.push(distribucion)
      }
    })

    let filasAumentar = informacion.añadir.map((el) => {
      let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
        (partida) => Number(partida.id) === el.id_distribucion
      )

      let sppa = `${
        partidaEncontrada.sector_informacion
          ? partidaEncontrada.sector_informacion.sector
          : '0'
      }.${
        partidaEncontrada.programa_informacion
          ? partidaEncontrada.programa_informacion.programa
          : '0'
      }.${
        partidaEncontrada.proyecto_informacion == 0
          ? '00'
          : partidaEncontrada.proyecto_informacion.proyecto
      }.${
        partidaEncontrada.id_actividad == 0
          ? '00'
          : partidaEncontrada.id_actividad
      }`

      let montoFinal = Number(partidaEncontrada.monto_actual) + el.monto

      return `  <tr>
          <td>${sppa}.${partidaEncontrada.partida}</td>
        <td>${separadorLocal(partidaEncontrada.monto_actual)}</td>
          <td class="table-success">+${separadorLocal(el.monto)}</td>
          <td class="table-primary">${separadorLocal(montoFinal)}</td>
        </tr>`
    })

    let filasDisminuir = informacion.restar.map((el) => {
      let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
        (partida) => Number(partida.id) === el.id_distribucion
      )

      let sppa = `${
        partidaEncontrada.sector_informacion
          ? partidaEncontrada.sector_informacion.sector
          : '0'
      }.${
        partidaEncontrada.programa_informacion
          ? partidaEncontrada.programa_informacion.programa
          : '0'
      }.${
        partidaEncontrada.proyecto_informacion == 0
          ? '00'
          : partidaEncontrada.proyecto_informacion.proyecto
      }.${
        partidaEncontrada.id_actividad == 0
          ? '00'
          : partidaEncontrada.id_actividad
      }`

      let montoFinal = Number(partidaEncontrada.monto_actual) - el.monto

      return ` <tr>
          <td>${sppa}.${partidaEncontrada.partida}</td>
          <td>${separadorLocal(partidaEncontrada.monto_actual)} Bs</td>
          <td class="table-danger">-${separadorLocal(el.monto)} Bs</td>
          <td class="table-primary">${separadorLocal(montoFinal)} Bs</td>
        </tr>`
    })

    let tablaAumentar = `   <table class="table table-xs">
        <thead>
          <th class="w-50">Distribucion</th>
          <th class="w-10">Monto actual</th>
          <th class="w-10">Cambio</th>
          <th class="w-50">Monto Final</th>
        </thead>
        <tbody>${filasDisminuir.join('')}${filasAumentar.join('')}</tbody>
      </table>`

    return `<div id='card-body-part-3' class="slide-up-animation">
        <h5 class='text-center text-blue-600 mb-4'>Lista de partidas</h5>
        ${tablaAumentar}
        
      </div>`
  }

  let card = `<div class='card slide-up-animation' id='${nombreCard}-form-card'>
          <div class='card-header d-flex justify-content-between'>
            <div class=''>
              <h5 class='mb-0'>Detalles de CAMBIAR SEGÚN TIPO</h5>
              <small class='mt-0 text-muted'>Visualice los detalles del {TIPO}</small>
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
          <div class='card-body'>${resumenPartidas()}</div>
          <div class='card-footer text-center'>
        <button class='btn btn-danger' id='btn-rechazar'>
          Rechazar
        </button>
        <button class='btn btn-primary' id='btn-aceptar'>
          Aceptar
        </button>
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
      console.log('aceptar')
    }
    if (e.target.id === 'btn-rechazar') {
      console.log('rechazar')
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

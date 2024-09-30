// QUEDA PENDIENTE ESTRUCTURAR EL ENVÍO DE DATOS
// VALIDAR ANTES DE ENVIAR TODOS LOS INPUTS
// MEJORAR EL DISEÑO
// DIFERENTES MENSAJES DE ERROR AL MOMENTO DE ENVIAR
// REALIZAR PRUEBAS

import { getFormPartidas } from '../api/partidas.js'
import {
  confirmNotification,
  hideLoader,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document
export const form_distribucion_form_card = ({ elementToInset }) => {
  let fieldList = {}
  let fieldListErrors = {}
  const oldCardElement = d.getElementById('distribucion-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = ` <div class='card slide-up-animation' id='distribucion-form-card'>
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
        <small class='text-muted'>
          Monto total restante dada la asignación por partida
        </small>
        <h3 class=''>
          Monto total: <b id='monto-total'>Sin definir monto</b>
        </h3>
        <h3 class=''>
          Monto restante: <b id='monto-restante'>Sin definir monto</b>
        </h3>
        <form class='mt-4' id='distribucion-form' autocomplete='off'>
          <div class='form-group'>
            <label for='monto' class='form-label'>
              Descripción del plan operativo
            </label>
            <textarea
              class='form-control'
              name='descripcion'
              id='descripcion'
              cols='10'
              rows='2'
              placeholder='Escriba la descripción del plan operativo dado por el ente...'
            ></textarea>
          </div>

          <div class='form-group'>
            <label for='monto' class='form-label'>
              Monto total a asignar
            </label>
            <input
              class='form-control'
              type='text'
              name='monto'
              id='monto'
              placeholder='Monto a asignar al plan operativo y partidas.'
            />
          </div>

          <h3>Distribución de partidas presupuestarias</h3>

          <div id='lista-partidas'></div>

          <div class='d-flex gap-2 justify-content-center'>
            <button class='btn btn-success' id='add-row'>
              +
            </button>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='partida-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)
  let numsRows = 0

  let cardElement = d.getElementById('distribucion-form-card')
  let montoRestanteElemet = d.getElementById('monto-restante')
  let montoTotalElement = d.getElementById('monto-total')
  let partidalist = d.getElementById('lista-partidas')
  let formElement = d.getElementById('distribucion-form')

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

    if (e.target.id === 'add-row') {
      console.log('hola', numsRows)
      let montoTotalInput = d.getElementById('monto')
      if (!montoTotalInput.value || montoTotalInput.value < 1) {
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Asigne un monto antes de asignar monto a partidas',
        })
      }
      addRow()
    }
    if (e.target.dataset.deleteRow) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al eliminar esta fila se actualizará el monto restante ¿Desea continuar?',
        successFunction: function () {
          let row = d.querySelector(
            `[data-row="${e.target.dataset.deleteRow}"]`
          )
          if (row) numsRows--
          row.remove()
          actualizarMontoRestante()
        },
      })
    }
  }

  function validateInputFunction(e) {
    if (e.target.id === 'monto') {
      if (!e.target.value) {
        console.log('hola')

        montoTotalElement.textContent = `Sin definir`
      } else {
        montoTotalElement.textContent = e.target.value
      }
      actualizarMontoRestante()
    }

    if (e.target.classList.contains('partida-input')) {
      actualizarMontoRestante()
    }
    // if(e.target.)
    // fieldList = validateInput({
    //   target: e.target,
    //   fieldList,
    //   fieldListErrors,
    //   type: fieldListErrors[e.target.name].type,
    // })
  }

  function addRow() {
    let newNumRow = numsRows + 1
    numsRows++

    partidalist.insertAdjacentHTML('beforeend', partidaRow(newNumRow))

    getFormPartidas().then((res) => {
      let partidasList = d.getElementById(`partidas-list-${newNumRow}`)
      console.log(res)
      partidasList.innerHTML = ''
      let options = res.fullInfo
        .map((option) => {
          return `<option value="${option.partida}">${option.descripcion}</option>`
        })
        .join('')

      partidasList.innerHTML = options
      return
    })

    return
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

function actualizarMontoRestante() {
  let montoRestanteElemet = d.getElementById('monto-restante')

  let montoTotal = d.getElementById('monto')
    ? d.getElementById('monto').value
    : 0

  let inputsPartidasMontos = d.querySelectorAll('.partida-input')
  inputsPartidasMontos.forEach((input) => {
    montoTotal -= input.value
  })

  if (montoTotal < 0) {
    return (montoRestanteElemet.innerHTML = `<span class="text-danger">${montoTotal}</span>`)
  } else if (montoTotal > 0) {
    return (montoRestanteElemet.innerHTML = `<span class="text-success">${montoTotal}</span>`)
  } else {
    return (montoRestanteElemet.innerHTML = `<span class="text-secondary">${montoTotal}</span>`)
  }
}

function partidaRow(partidaNum) {
  let row = ` <div class='row slide-up-animation' data-row='${partidaNum}'>
      <div class='col'>
        <div class='form-group'>
          <label for='monto' class='form-label'>
            Partida
          </label>
          <input
            class='form-control partida-partida'
            type='text'
            placeholder='Partida...'
            list='partidas-list-${partidaNum}'
            name='partida-${partidaNum}'
            id='partida-${partidaNum}'
          ></input>
          <datalist id='partidas-list-${partidaNum}'></datalist>
        </div>
      </div>
      <div class='col'>
        <div class='form-group'>
          <label for='monto' class='form-label'>
            Monto de partida
          </label>
          <div class='row'>
            <div class='col'>
              <input
                class='form-control partida-input'
                type='number'
                name='partida-monto-${partidaNum}'
                id='partida-monto'
                placeholder='Monto a asignar...'
              />
            </div>
            <div class='col'>
              <button class='btn btn-danger' data-delete-row='${partidaNum}'>
                ELIMINAR
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>`

  return row
}

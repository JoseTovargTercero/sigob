// QUEDA PENDIENTE ESTRUCTURAR EL ENVÍO DE DATOS
// VALIDAR ANTES DE ENVIAR TODOS LOS INPUTS
// MEJORAR EL DISEÑO
// DIFERENTES MENSAJES DE ERROR AL MOMENTO DE ENVIAR
// REALIZAR PRUEBAS

import { getFormPartidas } from '../api/partidas.js'
import {
  enviarDistribucionPresupuestaria,
  getEjecicio,
  getEjecicios,
} from '../api/pre_distribucion.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document
export const form_distribucion_form_card = ({ elementToInset }) => {
  let montos = { total: 0, restante: 0 }
  let partidas

  let fieldList = { id_ejercicio: '' }
  let fieldListErrors = {
    id_ejercicio: {
      value: true,
      message: 'Eleccione un ejercicio fiscal',
      type: 'number',
    },
    // descripcion: {
    //   value: true,
    //   message: 'Añada una descripción al plan operativo',
    //   type: 'text',
    // },
  }

  // ESTOS ESTADOS SE ACTUALIZARAN DE FORMA AUTOMÁTICA SEGÚN SE VAYAN GENERANDO LAS PARTIDAS
  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  const oldCardElement = d.getElementById('distribucion-form-card')
  if (oldCardElement) oldCardElement.remove()

  let card = `    <div class='card slide-up-animation' id='distribucion-form-card'>
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
        <form id='distribucion-form' autocomplete='off'>
          <div class='row mb-4'>
            <div class='col'>
              <h6 class='mb-0'>
                Monto total: <b id='monto-total'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>
                Monto total restante dada la asignación por partida
              </small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Monto restante: <b id='monto-restante'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>
                Monto total restante dada la asignación por partida
              </small>
            </div>
          </div>
          <div class='form-group'>
            <label for='monto' class='form-label'>
              Seleccionar ejercicio fiscal
            </label>
            <select class='form-select distribucion-input' name='id_ejercicio' id='search-select-ejercicio'>
              <option>Elegir...</option>
            </select>
          </div>

          <h5 class="mb-0">Distribución de presupuesto por partida</h5>
          <small class='text-muted'>
          Añada las partidas para realizar la distribución presupuestaria.
          </small>
          <div id='lista-partidas'></div>

          <div class='d-flex gap-2 justify-content-center'>
            <button class='btn btn-success' id='add-row'>
              Añadir +
            </button>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='distribucion-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  // Cargar select de ejercicios

  cargarSelectEjercicios()
  let numsRows = 0

  let cardElement = d.getElementById('distribucion-form-card')
  let montoTotalElement = d.getElementById('monto-total')
  let montoRestanteElement = d.getElementById('monto-restante')

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

    // AÑADIR NUEVA FILA DE PARTIDA

    if (e.target.id === 'add-row') {
      if (!montos.total || montos.total < 1) {
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione primero un ejercicio fiscal',
        })
      }
      addRow()
    }

    // VALIDAR DATOS ANTES DE ENVIAR

    if (e.target.id === 'distribucion-guardar') {
      let inputs = d.querySelectorAll('.distribucion-input')
      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          type: fieldListErrors[input.name].type,
          fieldList,
          fieldListErrors,
        })
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Faltan campos por completar',
        })
      }
      let partidasValidadas = validarPartidas()
      console.log(partidasValidadas)
      if (!partidasValidadas) {
        return
      }

      if (montos.restante < 0) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Se ha consumido más allá del situado presupuestario. Valide las asignaciones nuevamente',
        })
        return
      }

      enviarInformacion(partidasValidadas, closeCard)
    }

    // ELIMINAR FILA

    if (e.target.dataset.deleteRow) {
      let id = e.target.dataset.deleteRow
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al eliminar esta fila se actualizará el monto restante ¿Desea continuar?',
        successFunction: function () {
          let row = d.querySelector(`[data-row="${id}"]`)

          // ELIMINAR ESTADO Y ERRORES DE INPUTS
          delete fieldListPartidas[`partida-${id}`]
          delete fieldListErrorsPartidas[`partida-${id}`]

          delete fieldListPartidas[`partida-monto-${id}`]
          delete fieldListErrorsPartidas[`partida-monto-${id}`]

          if (row) numsRows--
          row.remove()
          montos.restante = actualizarMontoRestante(montos.total)
        },
      })
    }
  }

  async function validateInputFunction(e) {
    if (e.target.name === 'id_ejercicio') {
      if (!e.target.value) {
        montos.total = 0
        montoTotalElement.textContent = 'Ejercicio fiscal no seleccionado'
        montoRestanteElement.textContent = 'Ejercicio fiscal no seleccionado'
        let partidasListContainer = d.getElementById(`lista-partidas`)

        let rows = d.querySelectorAll('[data-row]')
        if (rows.length > 0)
          confirmNotification({
            type: NOTIFICATIONS_TYPES.done,
            message: 'Se eliminarán las filas de partidas añadidas',
          })
        partidasListContainer.innerHTML = ''
        return
      }

      let ejercicio = await getEjecicio(e.target.value)

      montos.total = ejercicio.situado
      montoTotalElement.textContent = montos.total
      montos.restante = actualizarMontoRestante(montos.total)
      cargarPartidas()
    }
    if (e.target.classList.contains('partida-monto')) {
      montos.restante = actualizarMontoRestante(montos.total)
    }
    // if(e.target.)
    // fieldListPartidas = validateInput({
    //   target: e.target,
    //   fieldListPartidas,
    //   fieldListErrorsPartidas,
    //   type: fieldListErrorsPartidas[e.target.name].type,
    // })

    if (e.target.classList.contains('partida-input')) {
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })
    } else {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
      console.log(e.target.value)
    }

    // console.log(fieldListPartidas, fieldListErrorsPartidas)
  }

  // CARGAR LISTA DE PARTIDAS

  async function cargarPartidas() {
    partidas = await getFormPartidas()
  }

  // AÑADIR FILA DE PARTIDA

  async function addRow() {
    let newNumRow = numsRows + 1
    numsRows++

    partidalist.insertAdjacentHTML('beforeend', partidaRow(newNumRow))

    // AÑADIR ESTADO Y ERRORES A INPUTS
    fieldListPartidas[`partida-${newNumRow}`] = ''
    fieldListErrorsPartidas[`partida-${newNumRow}`] = {
      value: true,
      message: 'Partida inválida',
      type: 'partida',
    }
    fieldListPartidas[`partida-monto-${newNumRow}`] = ''
    fieldListErrorsPartidas[`partida-monto-${newNumRow}`] = {
      value: true,
      message: 'Monto inválido',
      type: 'number',
    }

    console.log(fieldListPartidas, fieldListErrorsPartidas)

    let partidasList = d.getElementById(`partidas-list-${newNumRow}`)
    partidasList.innerHTML = ''
    let options = partidas.fullInfo
      .map((option) => {
        return `<option value="${option.partida}">${option.descripcion}</option>`
      })
      .join('')

    partidasList.innerHTML = options

    return
  }

  function validarPartidas() {
    let rows = d.querySelectorAll('[data-row]')
    let rowsArray = Array.from(rows)

    let montoRestante = 0

    // VALIDAR LOS INPUTS DE CADA FILA
    rows.forEach((el) => {
      let partidaInput = el.querySelector(`#partida-${el.dataset.row}`)
      let montoInput = el.querySelector(`#partida-monto-${el.dataset.row}`)

      validateInput({
        target: partidaInput,
        type: fieldListErrorsPartidas[partidaInput.name].type,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
      })

      validateInput({
        target: montoInput,
        type: fieldListErrorsPartidas[montoInput.name].type,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
      })
    })

    if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
      return toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message:
          'La distribución de partidas posee datos erróneos. Elimine o actualice las filas',
      })
    }

    let mappedPartidas = rowsArray.map((el) => {
      let partidaInput = el.querySelector(`#partida-${el.dataset.row}`)
      let montoInput = el.querySelector(`#partida-monto-${el.dataset.row}`)

      let partidaEncontrada = partidas.fullInfo.find(
        (partida) => partida.partida === partidaInput.value
      )

      // Verificar si la partida introducida existe

      if (!partidaEncontrada) {
        return false
      }

      return [partidaEncontrada.id, montoInput.value, fieldList.id_ejercicio]
    })

    console.log(mappedPartidas)

    // Verificar si hay algun dato erróneo y cancelar envío
    if (mappedPartidas.some((el) => !el)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Unas de las partidas utilizadas no está registrada',
      })
      return false
    }

    return mappedPartidas
  }

  function enviarInformacion(data) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar esta distribución presupuestaria?',
      successFunction: function () {
        enviarDistribucionPresupuestaria({ arrayDatos: data })
        closeCard()
      },
    })
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

async function cargarSelectEjercicios() {
  d.getElementById('ejercicio')
  let ejercicios = await getEjecicios()

  insertOptions({ input: 'ejercicio', data: ejercicios.mappedData })
}

function actualizarMontoRestante(monto) {
  let montoTotal = Number(monto)
  let montoRestanteElement = d.getElementById('monto-restante')

  let inputsPartidasMontos = d.querySelectorAll('.partida-monto')

  inputsPartidasMontos.forEach((input) => {
    montoTotal -= input.value
  })

  if (montoTotal < 0) {
    montoRestanteElement.innerHTML = `<span class="text-danger">${montoTotal}</span>`
    return montoTotal
  }
  if (montoTotal > 0) {
    montoRestanteElement.innerHTML = `<span class="text-success">${montoTotal}</span>`
    return montoTotal
  }

  montoRestanteElement.innerHTML = `<span class="text-secondary">${montoTotal}</span>`
  return montoTotal
}

function partidaRow(partidaNum) {
  let row = ` <div class='row slide-up-animation' data-row='${partidaNum}'>
      <div class='col'>
        <div class='form-group'>
          <label for='monto' class='form-label'>
            Partida
          </label>
          <input
            class='form-control partida-input'
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
                class='form-control partida-input partida-monto'
                type='number'
                name='partida-monto-${partidaNum}'
                id='partida-monto-${partidaNum}'
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

{
  /* <div class='form-group'>
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
</div> */
}

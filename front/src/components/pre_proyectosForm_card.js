import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

export const pre_proyectosForm_card = ({
  elementToInsert,
  ejercicioFiscal,
}) => {
  let fieldList = { monto: '', fecha: '', 'tipo-credito': '' }
  let fieldListErrors = {
    monto: { value: true, message: 'Monto inválido', type: 'number3' },
    fecha: { value: true, message: 'Fecha inválida', type: 'text' },
    'tipo-credito': { value: true, message: 'Tipo inválido', type: 'text' },
  }

  let fieldListProyecto = { 'proyecto-descripcion': '' }
  let fieldListErrorsProyecto = {
    'proyecto-descripcion': {
      value: true,
      message: 'Descripción inválida',
      type: 'textarea',
    },
  }

  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  let nombreCard = '${nombreCard}'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  let creditosForm = () => {
    return `      <div class='row slide-up-animation' id='card-body-part-1'>
        <div class='col'>
          <div class='form-group'>
            <label class='form-label' for='monto'></label>
            <input
              class='form-select proyecto-input'
              name='monto'
              id='monto'
              placeholder='Monto...'
            />
          </div>
        </div>
        <div class='col'>
          <div class='form-group'>
            <label class='form-label' for='monto'></label>
            <input
              class='form-select proyecto-input'
              name='fecha'
              id='fecha'
              type='date'
            />
          </div>
        </div>

        <div class='form-group'>
          <label for='tipo_credito' class='form-label'>
            Tipo de crédito
          </label>
          <select
            class='form-select proyecto-input'
            name='tipo-credito'
            id='tipo-credito'
          >
            <option value=''>Elegir...</option>
            <option value='FCI'>FCI</option>
            <option value='VB'>Venezuela Bella</option>
          </select>
        </div>

      </div>`
  }

  let proyectoForm = () => {
    return `      <div class='row slide-up-animation' id='card-body-part-2'>
        <div id='header' class='row text-center mb-4'>
          <div class='row'>
            <div class='col'>
              <h6>
                Total a acreditar: <b id='total-sumado'>No asignado</b>
              </h6>
            </div>
            <div class='col'>
              <h6>
                Total total creditado <b id='total-restado'>No asignado</b>
              </h6>
            </div>
          </div>
        </div>

        <div class='row'>
          <div class='form-group'>
          <label class="form-label" for="proyecto-descripcion">Descripción de proyecto</label>
            <textarea
            class="form-control proyecto-input-2"
              name='proyecto-descripcion'
              id='proyecto-descripcion'
            ></textarea>
          </div>

          <h5 class='text-center text-blue-600 mb-2'>Partidas a acreditar</h5>
          <div id='partidas-container'></div>
          <div class='text-center'>
            <button
              type='button'
              class='btn btn-sm bg-brand-color-1 text-white'
              data-add='accion'
            >
              <i class='bx bx-plus'></i> AGREGAR PARTIDA
            </button>
          </div>
        </div>
      </div>`
  }

  let card = `  <div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Información sobre crédito</h5>
          <small class='mt-0 text-muted'>Completa la información para asignar el crédito al proyecto</small>
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
      <div class='card-body' id="card-body">${creditosForm()}</div>
      <div class='card-footer'>
        <div class='card-footer text-center'>
          <button class='btn btn-secondary' id='btn-previus'>
            Atrás
          </button>
          <button class='btn btn-primary' id='btn-next'>
            Siguiente
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let cardBody = d.getElementById(`card-body`)
  //   let formElement = d.getElementById(`${nombreCard}-form`)

  let formFocus = 1
  let numsRows = 0

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

    if (e.target.dataset.add) {
      addRow()
    }

    validateFormFocus(e)
  }

  async function validateInputFunction(e) {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
  }

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')

    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part-1')
    let cardBodyPart2 = d.getElementById('card-body-part-2')
    let cardBodyPart3 = d.getElementById('card-body-part-3')

    if (e.target === btnNext) {
      if (formFocus === 1) {
        let creditoInput = d.querySelectorAll('.proyecto-input')

        creditoInput.forEach((input) => {
          fieldList = validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          })
        })

        if (Object.values(fieldListErrors).some((el) => el.value)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Hay campos inválidos',
          })
          return
        }

        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', proyectoForm())
        }

        if (btnPrevius.hasAttribute('disabled'))
          btnPrevius.removeAttribute('disabled')

        formFocus++
        return
      }

      if (formFocus === 2) {
        let proyectoInputs = d.querySelectorAll('.proyecto-input-2')

        console.log(proyectoInputs)

        proyectoInputs.forEach((input) => {
          fieldListProyecto = validateInput({
            target: input,
            fieldList: fieldListProyecto,
            fieldListErrors: fieldListErrorsProyecto,
            type: fieldListErrorsProyecto[input.name].type,
          })
        })

        if (Object.values(fieldListErrorsProyecto).some((el) => el.value)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Hay campos inválidos',
          })
          return
        }
        // let data = validarInformacion()

        console.log(data)

        enviarInformacion(data)
      }
    }

    if (e.target === btnPrevius) {
      if (formFocus === 3) {
        cardBodyPart2.classList.remove('d-none')
        btnNext.textContent = 'Siguiente'

        if (cardBodyPart3) {
          cardBodyPart3.classList.add('d-none')
        }

        formFocus--
        return
        // confirmNotification({
        //   type: NOTIFICATIONS_TYPES.send,
        //   message: 'Si continua se borrarán los cambios hechos aquí',
        //   successFunction: function () {
        //     cardBodyPart2.remove()

        //     cardBodyPart1.classList.remove('d-block')
        //     cardBodyPart1.classList.add('d-none')
        //     btnNext.textContent = 'Siguiente'
        //     // btnAdd.classList.remove('d-none')

        //     partidasSeleccionadas = []
        //     cardBody.innerHTML += seleccionPartidas()
        //     validarSeleccionPartidasTable()

        //     formFocus--
        //   },
        // })
      }
      if (formFocus === 2) {
        cardBodyPart1.classList.remove('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.add('d-none')
        }

        formFocus--

        btnPrevius.setAttribute('disabled', true)

        return
      }
    }
  }

  async function addRow() {
    let newNumRow = numsRows + 1
    numsRows++

    d.getElementById('partidas-container').insertAdjacentHTML(
      'beforeend',
      partidaRow(newNumRow)
    )

    // AÑADIR ESTADO Y ERRORES A INPUTS

    // fieldListPartidas[`partida-${newNumRow}`] = ''
    // fieldListErrorsPartidas[`partida-${newNumRow}`] = {
    //   value: true,
    //   message: 'Partida inválida',
    //   type: 'partida',
    // }
    fieldListPartidas[`distribucion-monto-${newNumRow}`] = ''
    fieldListErrorsPartidas[`distribucion-monto-${newNumRow}`] = {
      value: true,
      message: 'Monto inválido',
      type: 'number3',
    }

    let options = [`<option value=''>Elegir partida...</option>`]

    ejercicioFiscal.distribucion_partidas.forEach((partida) => {
      let sppa = `
      ${
        partida.sector_informacion ? partida.sector_informacion.sector : '00'
      }.${
        partida.programa_informacion
          ? partida.programa_informacion.programa
          : '00'
      }.${
        partida.proyecto_informacion
          ? partida.proyecto_informacion.proyecto
          : '00'
      }.${partida.id_actividad ? partida.id_actividad : '00'}`

      let opt = `<option value="${partida.id_distribucion}">${sppa}.${partida.partida}</option>`
      options.push(opt)
    })

    // Nombre de ente
    // - ${partida.ente_nombre[0].toUpperCase()}${partida.ente_nombre
    //   .substr(1, partida.ente_nombre.length - 1)
    //   .toLowerCase()}

    let partidasList = d.getElementById(`distribucion-${newNumRow}`)

    partidasList.innerHTML = ''

    partidasList.innerHTML = options.join('')

    $('.chosen-distribucion')
      .chosen()
      .change(function (obj, result) {
        let distribucionMontoActual = d.getElementById(
          `distribucion-monto-actual-${newNumRow}`
        )
        let partida = ejercicioFiscal.distribucion_partidas.find(
          (partida) => Number(partida.id) === Number(result.selected)
        )

        distribucionMontoActual.value = partida
          ? `${separadorLocal(partida.monto)} Bs`
          : 'No seleccionado'
      })

    return
  }

  function actualizarLabel() {
    let totalSumarElement = d.getElementById('total-sumado')
    let totalRestarElement = d.getElementById('total-restado')

    let valorSumar, valorRestar

    if (montos.totalSumar < 0) {
      valorSumar = `<span class="px-2 rounded text-red-600 bg-red-100">${separadorLocal(
        montos.totalSumar
      )}</span>`
    }
    if (montos.totalSumar > 0) {
      valorSumar = `<span class="px-2 rounded text-green-600 bg-green-100">${separadorLocal(
        montos.totalSumar
      )}</span>`
    }
    if (montos.totalSumar === 0) {
      valorSumar = `<span class="class="px-2 rounded text-secondary">No asignado</span>`
    }

    // VALIDAR TOTAL RESTADO

    if (montos.totalRestar > montos.totalSumar) {
      valorRestar = `<span class="px-2 rounded text-red-600 bg-red-100">${separadorLocal(
        montos.totalRestar
      )}</span>`
    }

    if (montos.totalRestar < montos.totalSumar) {
      valorRestar = `<span class="class="px-2 rounded text-secondary">${separadorLocal(
        montos.totalRestar
      )}</span>`
    }

    if (montos.totalRestar === montos.totalSumar) {
      valorRestar = `<span class="px-2 rounded text-green-600 bg-green-100">${separadorLocal(
        montos.totalRestar
      )}</span>`
    }
    if (montos.totalRestar === 0) {
      valorRestar = `<span class="class="px-2 rounded text-secondary">No asignado</span>`
    }
    totalSumarElement.innerHTML = valorSumar
    totalRestarElement.innerHTML = valorRestar
  }
  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

function chosenSelect() {
  let select = ` <div class='form-group'>
          <label for='search-select-${nombreCard}' class='form-label'>
            Seleccione el sector
          </label>
          <select
            class='form-select ${nombreCard}-input chosen-select'
            name='id_sector'
            id='search-select-${nombreCard}'
          >
            <option>Elegir...</option>
          </select>
        </div>`

  let options = [`<option>Elegir...</option>`]
  let data

  data.fullInfo.forEach((sector) => {
    let option = `<option value='${sector.id}'>${sector.sector}.${sector.programa}.${sector.proyecto} - ${sector.nombre}</option>`
    options.push(option)
  })

  selectEjercicio.innerHTML = options.join('')

  $('.chosen-select')
    .chosen()
    .change(function (obj, result) {
      console.log('changed: %o', arguments)
    })
}

function partidaRow(partidaNum) {
  let row = `<div class='row slide-up-animation' data-row="${partidaNum}">
        <div class='col-sm'>
          <div class='form-group'>
            <label for='sector-${partidaNum}' class='form-label'>
              Distribucion
            </label>
            <select
              class='form-control proyecto-partida chosen-distribucion'
              type='text'
              placeholder='Sector...'
              name='distribucion-${partidaNum}'
              id='distribucion-${partidaNum}'
            ></select>
          </div>
        </div>

        <div class='col-sm'>
         <div class='form-group'>
         <label for='distribucion-monto-actual' class='form-label'>Monto actual</label>
          <input
                  class='form-control distribucion-monto-actual
                  type='text'
                  name='distribucion-monto-actual-${partidaNum}'
                  id='distribucion-monto-actual-${partidaNum}'
                  placeholder='Monto actual...'
                  disabled
                />
         </div>
        </div>
  
        <div class='col-sm'>
          <div class='form-group'>
            <label for='distribucion-monto-${partidaNum}' class='form-label'>
          Monto a acreditar
             
            </label>
            <div class='row'>
              <div class='col'>
                <input
                  class='form-control proyecto-monto
                  type='text'
                  name='distribucion-monto-${partidaNum}'
                  id='distribucion-monto-${partidaNum}'
                  placeholder='Monto a asignar...'
                />
              </div>
              <div class='col'>
                <button type="button" class='btn btn-danger' data-delete-row='${partidaNum}'>
                  ELIMINAR
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>`

  return row
}

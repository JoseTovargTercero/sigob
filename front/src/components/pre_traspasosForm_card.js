import {
  confirmNotification,
  hideLoader,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

// NOTAS
// PENDIENTE VALIDAR EL FORM FOCUS DE FORMA CORRECTA PARA NO REHACER TODO AL MOMENTO DE IR ENTRE PASOS

export const pre_traspasosForm_card = ({
  elementToInsert,
  ejercicioFiscal,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  let nombreCard = '${traspasos}'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  const informacionPrincipal = () => {
    return ` <div id="card-body-part-1">
        <form>
          <div class='form-group'>
            <label class='form-label'>Código para traspaso</label>
            <input
              class='form-control'
              type='number'
              name='codigo'
              id='codigo'
            ></input>
          </div>
        </form>
        <div id='partidas-container'></div>
        <button
          type='button'
          class='btn btn-sm bg-brand-color-1 text-white'
          id='add-row'
        >
          <i class='bx bx-plus'></i> AGREGAR PARTIDA
        </button>
      </div>`
  }

  const partidasRestar = () => {
    return `<div id="card-body-part-2">
        <h1>PARTIDAS A RESTAR</h1>
      </div>`
  }

  const resumenPartidas = () => {
    return `<div id='card-body-part-2¿3'>
        <h1>RESUMEN DE PARTIDAS</h1>
      </div>`
  }

  let card = `  <div class='card slide-up-animation' id='${nombreCard}-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Formulario de traspasos</h5>
          <small class='mt-0 text-muted'>
            Siga ls pasos pasos para realizar una solicitud de traspaso
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
      <div class='card-body' id="card-body-principal">${informacionPrincipal()}</div>
      <div class='card-footer text-center'>
        <button class='btn btn-secondary' id='btn-previus'>
          Atrás
        </button>
        <button class='btn btn-primary' id='btn-next'>
          Siguiente
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById(`${nombreCard}-form-card`)
  let formElement = d.getElementById(`${nombreCard}-form`)
  let cardBody = d.getElementById('card-body-principal')

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

    // Añadir partidas
    if (e.target.id === 'add-row') {
      addRow(e.target.dataset.tipo)
    }
    // ELIMINAR PARTIDAS
    if (e.target.dataset.deleteRow) {
      let id = e.target.dataset.deleteRow
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          'Al eliminar esta fila se actualizará el monto restante ¿Desea continuar?',
        successFunction: function () {
          let row = d.querySelector(`[data-row="${id}"]`)

          // ELIMINAR ESTADO Y ERRORES DE INPUTS

          delete fieldListPartidas[`distribucion-monto-${id}`]
          delete fieldListErrorsPartidas[`distribucion-monto-${id}`]

          if (row) numsRows--
          row.remove()
        },
      })
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

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {}

  //   formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')

    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part-1')
    let cardBodyPart2 = d.getElementById('card-body-part-2')
    let cardBodyPart3 = d.getElementById('card-body-part-3')

    if (e.target === btnNext) {
      if (formFocus === 1) {
        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.remove('d-none')
        } else {
          cardBody.innerHTML += partidasRestar()
        }

        console.log('Parte 1')
        formFocus++
        return
      }
      if (formFocus === 2) {
        cardBodyPart2.classList.add('d-none')

        if (cardBodyPart3) {
          cardBodyPart3.classList.remove('d-none')
        } else {
          cardBody.innerHTML += resumenPartidas()
        }

        formFocus++
        return
      }
    }

    if (e.target === btnPrevius) {
      if (formFocus === 3) {
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
        return
      }
      if (formFocus === 2) {
        formFocusPart1()
        formFocus--

        return
      }
    }
  }

  async function addRow(tipo) {
    let newNumRow = numsRows + 1
    numsRows++

    d.getElementById('partidas-container').insertAdjacentHTML(
      'beforeend',
      partidaRow(newNumRow, tipo)
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

    ejercicioFiscal.distribucion_partidas
      .filter((partida) =>
        partidasDisponibles.some((par) => Number(par.id) === Number(partida.id))
      )
      .forEach((el) => {
        let sppa = `${
          el.sector_informacion ? el.sector_informacion.sector : '0'
        }.${el.programa_informacion ? el.programa_informacion.programa : '0'}.${
          el.proyecto_informacion == 0 ? '00' : el.proyecto_informacion.proyecto
        }.${el.id_actividad == 0 ? '00' : el.id_actividad}`

        let opt = `<option value="${el.id}">${sppa}.${el.partida}</option>`
        options.push(opt)
      })

    let partidasList = d.getElementById(`distribucion-${newNumRow}`)
    partidasList.innerHTML = ''

    partidasList.innerHTML = options.join('')

    $('.chosen-distribucion')
      .chosen()
      .change(function (obj, result) {
        console.log('changed: %o', arguments)
      })

    return
  }
}

function partidaRow(partidaNum, tipo) {
  let row = `<div class='row slide-up-animation' ${
    tipo === 'A' ? 'data-row-aumentar' : 'data-row-restar'
  }="${partidaNum}" data-row="${partidaNum}">
        <div class='col-sm'>
          <div class='form-group'>
            <label for='sector-${partidaNum}' class='form-label'>
              Distribucion
            </label>
            <select
              class='form-control partida-partida chosen-distribucion'
              type='text'
              placeholder='Sector...'
              name='distribucion-${partidaNum}'
              id='distribucion-${partidaNum}'
            ></select>
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
                  class='form-control partida-input distribucion-monto'
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

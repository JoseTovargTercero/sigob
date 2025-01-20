import {
  confirmNotification,
  formatearFloat,
  hideLoader,
  insertOptions,
  separadorLocal,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

// NOTAS
// VALIDAR NO ELEGIR LA MISMA PARTIDA 2 VECES EN LA MISMA VISTA Y ENTRE VISTAS
// VALIDAR SI LAS PARTIDAS VIENEN DE DISTRIBUCION PRESUPUESTARIA O DISTRIBUCION A ENTES
// AÑADIR VISTA DE RESUMEN DE TRASPASOS

export const pre_traspasosForm_card = ({
  elementToInsert,
  ejercicioFiscal,
}) => {
  let fieldList = { codigo: '' }
  let fieldListErrors = {
    codigo: {
      value: true,
      message: 'Código inválido',
      type: 'textarea',
    },
  }

  let informacion = {
    codigo: 0,
    añadir: [],
    restar: [],
  }

  console.log(ejercicioFiscal)

  let montos = { totalSumar: 0, totalRestar: 0, acumulado: 0 }

  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  let nombreCard = 'traspasos'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  const informacionPrincipal = () => {
    return ` <div id='card-body-part-1' class="slide-up-animation">
        <form class="mb-2">
          <div class='form-group'>
            <label for="codigo" class='form-label'>Código para traspaso</label>
            <input
              class='form-control'
              type='number'
              name='codigo'
              id='codigo'
            ></input>
          </div>
        </form>
        <h5 class='text-center text-blue-600 mb-4'>Partidas a aumentar</h5>
        <div id='partidas-container-aumentar'></div>
        <div class='text-center'>
          <button
            type='button'
            class='btn btn-sm bg-brand-color-1 text-white'
            id='add-row'
            data-tipo='A'
          >
            <i class='bx bx-plus'></i> AGREGAR PARTIDA
          </button>
        </div>
      </div>`
  }

  const partidasRestar = () => {
    return `<div id='card-body-part-2' class="slide-up-animation">
    <h5 class='text-center text-blue-600 mb-4'>Partidas a restar</h5>
        <div id='partidas-container-restar'></div>
        <div class='text-center'>
          <button
            type='button'
            class='btn btn-sm bg-brand-color-1 text-white'
            id='add-row'
            data-tipo='D'
          >
            <i class='bx bx-plus'></i> AGREGAR PARTIDA
          </button>
        </div>
      </div>`
  }

  const resumenPartidas = () => {
    return `<div id='card-body-part-3' class="slide-up-animation">
        <h1>RESUMEN DE PARTIDAS</h1>
      </div>`
  }

  let card = `    <div class='card slide-up-animation' id='${nombreCard}-form-card'>
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
      <div class='card-body' id='card-body-principal'>
        <div id='header' class='row text-center'>
          <div class='col'>
            <h6>
              Total a traspasar: <b id='total-sumado'>No asignado</b>
            </h6>
          </div>
          <div class='col'>
            <h6>
              Total traspasado <b id='total-restado'>No asignado</b>
            </h6>
          </div>
        </div>
        ${informacionPrincipal()}
      </div>
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

          // ACTUALIZAR MONTOS

          let inputsAumentar =
            d.querySelectorAll('.distribucion-monto-aumentar') || []

          montos.totalSumar = 0
          inputsAumentar.forEach((input) => {
            if (input.value === '' || isNaN(input.value)) {
              input.value = 0
              montos.totalSumar += Number(formatearFloat(input.value))
              input.value = ''
            } else {
              montos.totalSumar += Number(formatearFloat(input.value))
            }
          })

          let inputsRestar =
            d.querySelectorAll('.distribucion-monto-restar') || []

          montos.totalRestar = 0
          inputsRestar.forEach((input) => {
            if (input.value === '' || isNaN(input.value)) {
              input.value = 0
              montos.totalRestar += Number(formatearFloat(input.value))
              input.value = ''
            } else {
              montos.totalRestar += Number(formatearFloat(input.value))
            }
          })

          actualizarLabel()
        },
      })
    }

    validateFormFocus(e)
  }

  async function validateInputFunction(e) {
    if (e.target.classList.contains('distribucion-monto-aumentar')) {
      let inputs = d.querySelectorAll('.distribucion-monto-aumentar')

      montos.totalSumar = 0
      inputs.forEach((input) => {
        if (input.value === '' || isNaN(input.value)) {
          input.value = 0
          montos.totalSumar += Number(formatearFloat(input.value))
          input.value = ''
        } else {
          montos.totalSumar += Number(formatearFloat(input.value))
        }
      })

      console.log(montos)

      actualizarLabel()

      return
    }

    if (e.target.classList.contains('distribucion-monto-restar')) {
      let totalSumarElement = d.getElementById('total-sumado')

      let totalRestarElement = d.getElementById('total-restado')
      let inputs = d.querySelectorAll('.distribucion-monto-restar')

      montos.totalRestar = 0
      inputs.forEach((input) => {
        if (input.value === '' || isNaN(input.value)) {
          input.value = 0
          montos.totalRestar += Number(formatearFloat(input.value))
          input.value = ''
        } else {
          montos.totalRestar += Number(formatearFloat(input.value))
        }
      })

      console.log(montos)

      actualizarLabel()

      return
    }

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

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')

    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part-1')
    let cardBodyPart2 = d.getElementById('card-body-part-2')
    let cardBodyPart3 = d.getElementById('card-body-part-3')

    if (e.target === btnNext) {
      if (formFocus === 1) {
        let result = validarPartidas('A')
        console.log(result)
        if (!result) return

        informacion.añadir = result
        console.log(informacion)

        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', partidasRestar())
        }

        if (btnPrevius.hasAttribute('disabled'))
          btnPrevius.removeAttribute('disabled')

        formFocus++
        return
      }
      if (formFocus === 2) {
        let result = validarPartidas('D')
        console.log(result)
        if (!result) return

        informacion.restar = result
        console.log(informacion)

        cardBodyPart2.classList.add('d-none')

        if (cardBodyPart3) {
          cardBodyPart3.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', resumenPartidas())
        }

        formFocus++

        return
      }

      if (formFocus === 3) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message: '¿Está seguro de realizar esta solicitud de traspaso?',
        })
      }
    }

    if (e.target === btnPrevius) {
      if (formFocus === 3) {
        cardBodyPart2.classList.remove('d-none')

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
        return
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

  function validarPartidas(tipo) {
    let rows
    if (tipo === 'A') {
      rows = d.querySelectorAll('[data-row-aumentar]')
    } else {
      rows = d.querySelectorAll('[data-row-restar]')
    }
    let rowsArray = Array.from(rows)

    let montoRestante = 0

    // VERIFICAR SI SE HAN SELECCIONADO PARTIDAS
    if (rowsArray.length < 1) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se han añadido partidas',
      })
      return false
    }

    let mappedPartidas = rowsArray.map((el) => {
      let partidaInput = el.querySelector(`#distribucion-${el.dataset.row}`)
      let montoInput = el.querySelector(`#distribucion-monto-${el.dataset.row}`)

      let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
        (partida) => Number(partida.id) === Number(partidaInput.value)
      )

      // Verificar si la partida introducida existe

      if (!partidaEncontrada) {
        return false
      }

      return {
        id_distribucion: partidaEncontrada.id,
        monto: formatearFloat(montoInput.value),
      }
    })

    // Verificar si hay algun dato erróneo y cancelar envío
    if (mappedPartidas.some((el) => !el)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Una o más partidas inválidas',
      })
      return false
    }

    return mappedPartidas
  }

  async function addRow(tipo) {
    let newNumRow = numsRows + 1
    numsRows++
    if (tipo === 'A') {
      d.getElementById('partidas-container-aumentar').insertAdjacentHTML(
        'beforeend',
        partidaRow(newNumRow, tipo)
      )
    } else {
      d.getElementById('partidas-container-restar').insertAdjacentHTML(
        'beforeend',
        partidaRow(newNumRow, tipo)
      )
    }

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

    ejercicioFiscal.distribucion_partidas.forEach((el) => {
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
        let distribucionMontoActual = d.getElementById(
          `distribucion-monto-actual-${newNumRow}`
        )
        let partida = ejercicioFiscal.distribucion_partidas.find(
          (partida) => Number(partida.id) === Number(result.selected)
        )

        distribucionMontoActual.value = separadorLocal(partida.monto_actual)
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

        <div class='col-sm'>
         <div class='form-group'>
         <label for='distribucion-monto-actual' class='form-label'>Monto actual</label>
          <input
                  class='form-control distribucion-monto-actual-${
                    tipo === 'A' ? 'aumentar' : 'restar'
                  }'
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
            ${tipo === 'A' ? ' Monto a aumentar' : 'Monto a disminuir'}
             
            </label>
            <div class='row'>
              <div class='col'>
                <input
                  class='form-control partida-input distribucion-monto-${
                    tipo === 'A' ? 'aumentar' : 'restar'
                  }'
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

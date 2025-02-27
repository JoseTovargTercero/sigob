import {
  obtenerDistribucionEntes,
  obtenerEntes,
} from '../api/pre_distribucion.js'
import { getPreAsignacionEntes } from '../api/pre_entes.js'
import { registrarCredito } from '../api/pre_proyectos.js'
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

export const pre_proyectosForm_card = async ({
  elementToInsert = null,
  ejercicioFiscal = null,
  close = false,
  reset,
}) => {
  let fieldList = {
    monto: '',
    fecha: '',
    'tipo-credito': '',
    id_ente: '',
    'descripcion-credito': '',
  }
  let fieldListErrors = {
    id_ente: { value: true, message: 'Ente inválido', type: 'number3' },
    monto: { value: true, message: 'Monto inválido', type: 'number3' },
    fecha: { value: true, message: 'Fecha inválida', type: 'text' },
    'tipo-credito': {
      value: true,
      message: 'Tipo inválido',
      type: 'text',
    },
    'descripcion-credito': {
      value: true,
      message: 'Descripción inválida',
      type: 'textarea',
    },
  }

  let fieldListProyecto = { 'proyecto-descripcion': '', 'tipo-proyecto': '' }
  let fieldListErrorsProyecto = {
    'proyecto-descripcion': {
      value: true,
      message: 'Descripción inválida',
      type: 'textarea',
    },
    'tipo-proyecto': {
      value: true,
      message: 'Descripción inválida',
      type: 'text',
    },
  }

  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  let montos = { totalCredito: 0, totalAcreditado: 0 }

  let nombreCard = 'proyectos'

  const oldCardElement = d.getElementById(`${nombreCard}-form-card`)
  if (oldCardElement || close) {
    closeCard(oldCardElement)
  }

  let entes = await obtenerEntes()
  let distribuciones = []

  const entesOptions = () => {
    let options = [`<option value=''>Elegir...</option>`]
    entes.forEach((ente) => {
      let option = `<option value='${ente.ente.id}'>${ente.ente.nombre}</option>`
      options.push(option)
    })

    return options.join('')
  }

  let creditosForm = () => {
    return ` <div class='row slide-up-animation' id='card-body-part-1'>
        <div class='form-group'>
          <label class='form-label' for='id_ente'></label>
          <select
            class='form-select proyecto-input'
            name='id_ente'
            id='id_ente'
          >
            ${entesOptions()}
          </select>
        </div>
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
            <option value='0'>FCI</option>
            <option value='1'>Venezuela Bella</option>
          </select>
        </div>
        <div class='form-group'>
          <label class='form-label' for='credito-descripcion'>
            Descripción de credito
          </label>
          <textarea
            class='form-control proyecto-input'
            name='descripcion-credito'
            id='descripcion-credito'
          ></textarea>
        </div>
      </div>`
  }

  let proyectoForm = () => {
    return `<div class='row slide-up-animation' id='card-body-part-2'>
        <div class='row'>
          <div class='form-group'>
          <label for='tipo-proyecto' class='form-label'>
            Tipo de proyecto
          </label>
          <select
            class='form-select proyecto-input-2'
            name='tipo-proyecto'
            id='tipo-proyecto'
          >
            <option value=''>Elegir...</option>
            <option value='0'>Transferencia</option>
            <option value='1'>Compra</option>
          </select>
        </div>
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
      <div class='card-body' id="card-body">
      <div id='header' class='row text-center mb-4'>
          <div class='row'>
            <div class='col'>
              <h6>
                Total a acreditar: <b id='total-credito'>No asignado</b>
              </h6>
            </div>
            <div class='col'>
              <h6>
                Total total creditado <b id='total-acreditado'>No asignado</b>
              </h6>
            </div>
          </div>
        </div>
      ${creditosForm()}</div>
      <div class='card-footer'>
        <div class='card-footer text-center'>
          <button class='btn btn-secondary' id='btn-previus' disabled>
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
  $('.chosen-select-ente')
    .chosen()
    .change(function (obj, result) {
      console.log('changed: %o', arguments)
    })
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

          let inputsProyecto = d.querySelectorAll('.proyecto-monto') || []

          montos.totalAcreditado = 0
          inputsProyecto.forEach((input) => {
            if (input.value === '' || isNaN(input.value)) {
              input.value = 0
              montos.totalAcreditado += Number(formatearFloat(input.value))
              input.value = ''
            } else {
              montos.totalAcreditado += Number(formatearFloat(input.value))
            }
          })

          actualizarLabel()
        },
      })
    }

    validateFormFocus(e)
  }

  async function validateInputFunction(e) {
    if (e.target.classList.contains('proyecto-input')) {
      if (e.target.name === 'id_ente') {
        if (d.getElementById('card-body-part-2')) {
          let partidasContainer = d.getElementById('partidas-container')
          partidasContainer.innerHTML = ''

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message:
              'Al cambiar el ente se actualizarán las partidas a acreditar',
          })

          fieldList = validateInput({
            target: e.target,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[e.target.name].type,
          })
          numsRows = 0
          montos.totalAcreditado = 0

          actualizarLabel()
        }
      }
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.id === 'monto') {
      montos.totalAcreditado = 0

      if (e.target.value === '' || isNaN(e.target.value)) {
        e.target.value = 0
        montos.totalCredito = Number(formatearFloat(e.target.value))
        e.target.value = ''
      } else {
        montos.totalCredito = Number(formatearFloat(e.target.value))
      }
      actualizarLabel()
    }

    if (e.target.classList.contains('proyecto-input-2')) {
      fieldListProyecto = validateInput({
        target: e.target,
        fieldList: fieldListProyecto,
        fieldListErrors: fieldListErrorsProyecto,
        type: fieldListErrorsProyecto[e.target.name].type,
      })
    }

    if (e.target.classList.contains('proyecto-monto')) {
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })

      let inputs = d.querySelectorAll('.proyecto-monto')

      montos.totalAcreditado = 0
      inputs.forEach((input) => {
        if (input.value === '' || isNaN(input.value)) {
          input.value = 0
          montos.totalAcreditado += Number(formatearFloat(input.value))
          input.value = ''
        } else {
          montos.totalAcreditado += Number(formatearFloat(input.value))
        }
      })

      actualizarLabel()

      return
    }
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

        if (fieldList.id_ente === '') {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Seleccione un ente',
          })
          return
        }

        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          if (fieldList.id_ente_anterior !== fieldList.id_ente) {
            distribuciones = await obtenerDistribucionEntes(
              ejercicioFiscal.id,
              fieldList.id_ente
            )
            fieldList.id_ente_anterior = fieldList.id_ente
          }

          cardBodyPart2.classList.remove('d-none')
        } else {
          cardBody.insertAdjacentHTML('beforeend', proyectoForm())

          fieldList.id_ente_anterior = fieldList.id_ente

          distribuciones = await obtenerDistribucionEntes(
            ejercicioFiscal.id,
            fieldList.id_ente
          )
        }

        console.log(distribuciones)
        if (btnPrevius.hasAttribute('disabled'))
          btnPrevius.removeAttribute('disabled')

        btnNext.textContent = 'Enviar'
        formFocus++
        return
      }

      if (formFocus === 2) {
        let proyectoInputs = d.querySelectorAll('.proyecto-input-2')

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

        let partidaInputsMonto = d.querySelectorAll('.proyecto-monto')

        partidaInputsMonto.forEach((input) => {
          fieldListPartidas = validateInput({
            target: input,
            fieldList: fieldListPartidas,
            fieldListErrors: fieldListErrorsPartidas,
            type: fieldListErrorsPartidas[input.name].type,
          })
        })

        if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Hay montos de partidas inválidos',
          })
          return
        }

        let partidasIguales = validarInputIguales()

        if (partidasIguales) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message:
              'Está realizando una asignación a una partida 2 o más veces. Valide nuevamente por favor',
          })
          return
        }

        let data = validarInformacion()

        if (!data) return

        if (montos.totalCredito < montos.totalAcreditado) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message:
              'El monto a acreditar supera el monto total del crédito. Si desea seguir modifique el monto a acreditar.',
          })
          return
        }

        if (montos.totalCredito > montos.totalAcreditado) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'No se ha consumido el monto total de crédito.',
          })
          return
        }

        let informacion = {
          id_ente: fieldList.id_ente,
          monto: formatearFloat(fieldList.monto),
          fecha: fieldList.fecha,
          id_ejercicio: ejercicioFiscal.id,
          descripcion_credito: fieldList['descripcion-credito'],
          distribuciones: data,
          tipo_credito: fieldList['tipo-credito'],
          tipo_proyecto: fieldListProyecto['tipo-proyecto'],
          descripcion_proyecto: fieldListProyecto['proyecto-descripcion'],
        }

        enviarInformacion(informacion)
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

        btnNext.textContent = 'Siguiente'
        btnPrevius.setAttribute('disabled', true)

        return
      }
    }
  }

  function validarInformacion(tipo) {
    let rows = d.querySelectorAll('[data-row]')

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

      let partidaEncontrada = distribuciones.distribucion.find(
        (partida) =>
          Number(partida.id_distribucion) === Number(partidaInput.value)
      )

      // Verificar si la partida introducida existe

      if (!partidaEncontrada) {
        return false
      }

      return {
        id_distribucion: partidaEncontrada.id_distribucion,
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

  function validarInputIguales() {
    let inputs = Array.from(d.querySelectorAll('[data-row] .proyecto-partida'))

    const valores = inputs.map((input) => input.value)
    const conteoValores = valores.reduce((conteo, valor) => {
      conteo[valor] = (conteo[valor] || 0) + 1
      return conteo
    }, {})

    for (let valor in conteoValores) {
      if (conteoValores[valor] >= 2) {
        return true
      }
    }
    return false
  }

  async function addRow() {
    if (!fieldList.id_ente) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Seleccione un ente',
      })
      return
    }

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

    let sppa = `
    ${distribuciones.sector_numero || '00'}.${
      distribuciones.programa_numero || '00'
    }.${distribuciones.proyecto_numero || '00'}.${
      distribuciones.actividad_id || '51'
    }`

    distribuciones.distribucion.forEach((partida) => {
      let opt = `<option value="${partida.id_distribucion}">${sppa}.${partida.partida_presupuestaria.partida} - ${partida.partida_presupuestaria.descripcion} </option>`
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
        // let distribucionMontoActual = d.getElementById(
        //   `distribucion-monto-actual-${newNumRow}`
        // )
        // console.log(result.selected)
        // let partida = ejercicioFiscal.distribucion_partidas.find(
        //   (partida) => Number(partida.id) === Number(result.selected)
        // )
        // console.log(partida)
        // distribucionMontoActual.value = partida
        //   ? `${separadorLocal(partida.monto)} Bs`
        //   : 'No seleccionado'
      })

    return
  }

  function actualizarLabel() {
    let totalCredito = d.getElementById('total-credito')
    let totalAcreditado = d.getElementById('total-acreditado')

    let valorCredito, valorAcreditar

    if (montos.totalCredito < 0) {
      valorCredito = `<span class="px-2 rounded text-red-600 bg-red-100">${separadorLocal(
        montos.totalCredito
      )}</span>`
    }
    if (montos.totalCredito > 0) {
      valorCredito = `<span class="px-2 rounded text-green-600 bg-green-100">${separadorLocal(
        montos.totalCredito
      )}</span>`
    }
    if (montos.totalCredito === 0) {
      valorCredito = `<span class="class="px-2 rounded text-secondary">No asignado</span>`
    }

    // VALIDAR TOTAL RESTADO

    if (montos.totalAcreditado > montos.totalCredito) {
      valorAcreditar = `<span class="px-2 rounded text-red-600 bg-red-100">${separadorLocal(
        montos.totalAcreditado
      )}</span>`
    }

    if (montos.totalAcreditado < montos.totalCredito) {
      valorAcreditar = `<span class="class="px-2 rounded text-secondary">${separadorLocal(
        montos.totalAcreditado
      )}</span>`
    }

    if (montos.totalAcreditado === montos.totalCredito) {
      valorAcreditar = `<span class="px-2 rounded text-green-600 bg-green-100">${separadorLocal(
        montos.totalAcreditado
      )}</span>`
    }
    if (montos.totalAcreditado === 0) {
      valorAcreditar = `<span class="class="px-2 rounded text-secondary">No asignado</span>`
    }
    totalCredito.innerHTML = valorCredito
    totalAcreditado.innerHTML = valorAcreditar
  }
  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    console.log(data)

    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea enviar la información?',
      successFunction: async function () {
        let res = await registrarCredito(data)
        if (res.success) {
          closeCard(cardElement)
          reset()
        }
      },
    })
  }

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

  $('.chosen-select-ente')
    .chosen()
    .change(function (obj, result) {
      console.log('changed: %o', arguments)
    })
}

function partidaRow(partidaNum) {
  // input monto actual
  /* <div class='col-sm'>
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
</div> */

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

// QUEDA PENDIENTE ESTRUCTURAR EL ENVÍO DE DATOS
// VALIDAR ANTES DE ENVIAR TODOS LOS INPUTS
// MEJORAR EL DISEÑO
// DIFERENTES MENSAJES DE ERROR AL MOMENTO DE ENVIAR
// REALIZAR PRUEBAS

import { getProgramasData, getSectoresData } from '../api/form_informacion.js'
import { selectTables } from '../api/globalApi.js'
import { getFormPartidas } from '../api/partidas.js'
import {
  enviarDistribucionPresupuestaria,
  getEjecicio,
  getEjecicios,
} from '../api/pre_distribucion.js'
import { getSectores } from '../api/sectores.js'
import { loadDistribucionTable } from '../controllers/form_distribucionTable.js'
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
export const form_distribucion_form_card = async ({
  elementToInset,
  ejercicioFiscal,
  recargarEjercicio,
}) => {
  let montos = {
    total: ejercicioFiscal.situado,
    restante: ejercicioFiscal.restante,
    acumulado: 0,
  }
  let partidas = await getFormPartidas()

  let ejercicio

  let fieldList = {
    id_ejercicio: ejercicioFiscal.id,
    id_sector: '',
    id_programa: '',
    id_proyecto: 0,
  }
  let fieldListErrors = {
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
          <h5 class='mb-0'>Realizar distribución presupuestaria</h5>
          <small class='mt-0 text-muted'>
            Seleccione el sector y las partidas correspondientes
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
                Monto total:
                <b id='monto-total'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>
                Monto total restante dada la asignación por partida
              </small>
            </div>
            <div class='col'>
              <h6 class='mb-0'>
                Monto restante:
                <b id='monto-restante'>Ejercicio fiscal no seleccionado</b>
              </h6>
              <small class='text-muted'>
                Monto total restante dada la asignación por partida
              </small>
            </div>
          </div>

          <div class='row'>
            <div class='col'>
              <div class='form-group'>
                <label for='search-select-sector' class='form-label'>
                  Seleccione el sector
                </label>
                <select
                  class='form-select chosen-sector'
                  name='id_sector'
                  id='search-select-sector'
                >
                  <option>Elegir...</option>
                </select>
              </div>
            </div>
            <div class='col'>
              <div class='form-group'>
                <label for='search-select-programa' class='form-label'>
                  Seleccione el programa
                </label>
                <select
                  class='form-select chosen-programa'
                  name='id_programa'
                  id='search-select-programa'
                >
                  <option>Elegir...</option>
                </select>
              </div>
            </div>
            <div class='col'>
              <div class='form-group'>
                <label for='search-select-programa' class='form-label'>
                  Seleccione el proyecto
                </label>
                <select
                  class='form-select chosen-proyecto'
                  name='id_proyecto'
                  id='search-select-proyecto'>
                  <option>Elegir...</option>
                </select>
              </div>
            </div>

            <div class='col'>
              <div class='form-group'>
                <label for='id_actividad' class='form-label'>
                  Seleccione el la actividad
                </label>
                <input
                  class='form-control distribucion-input'
                  type='number'
                  placeholder='Número para actividad (Defecto "00")'
                  name='id_actividad'
                  id='id_actividad'
                />
              </div>
            </div>
          </div>

          <h5 class='mb-0'>Distribución del presupuesto</h5>
          <small class='text-muted'>
            Añada las partidas para realizar la distribución presupuestaria.
          </small>
          <div id='lista-partidas' class='mt-4'></div>

          <div class='d-flex gap-2 justify-content-center'>
            <button class='btn btn-sm bg-brand-color-1 text-white' id='add-row'>
              Agregar partida +
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

  let numsRows = 0

  console.log(ejercicioFiscal)

  let cardElement = d.getElementById('distribucion-form-card')
  let montoTotalElement = d.getElementById('monto-total')
  let montoRestanteElement = d.getElementById('monto-restante')

  montoTotalElement.textContent = separadorLocal(ejercicioFiscal.situado)
  montoRestanteElement.innerHTML = ejercicioFiscal.restante
    ? `<span class="text-success">${separadorLocal(
        ejercicioFiscal.restante
      )}</span>`
    : `<span class="text-secondary">${separadorLocal(
        ejercicioFiscal.restante
      )}</span>`

  let partidalist = d.getElementById('lista-partidas')
  let formElement = d.getElementById('distribucion-form')

  cargarSelectSectores()

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
      let sectorInput = d.getElementById('search-select-sector')
      let programaInput = d.getElementById('search-select-programa')

      if (sectorInput.value === '' || !sectorInput.value) {
        console.log(sectorInput.value)
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija un sector',
        })
        return
      }

      if (programaInput.value === '' || !programaInput.value) {
        console.log(programaInput.value)
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija un programa',
        })
        return
      }

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

      if (validarInputIguales()) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Está realizando una asignación a una partida 2 o más veces. Valide nuevamente por favor',
        })
        return
      }

      if (montos.restante - montos.acumulado < 0) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Se ha consumido más allá del situado presupuestario. Valide las asignaciones nuevamente',
        })
        return
      }

      if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Hay montos inválidos',
        })
        return
      }

      let inputsMontos = Array.from(d.querySelectorAll('.partida-monto'))
      inputsMontos.forEach((input) => {
        fieldListPartidas = validateInput({
          target: input,
          fieldList: fieldListPartidas,
          fieldListErrors: fieldListErrorsPartidas,
          type: fieldListErrorsPartidas[input.name],
        })
      })

      console.log(fieldListPartidas, fieldListErrorsPartidas)

      console.log(partidasValidadas)

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
          actualizarMontoRestante(montos.total)
        },
      })
    }
  }

  async function validateInputFunction(e) {
    // if (e.target.name === 'id_ejercicio') {
    //   if (!e.target.value) {
    //     montos.total = 0
    //     montoTotalElement.textContent = 'Ejercicio fiscal no seleccionado'
    //     montoRestanteElement.textContent = 'Ejercicio fiscal no seleccionado'
    //     let partidasListContainer = d.getElementById(`lista-partidas`)

    //     let rows = d.querySelectorAll('[data-row]')
    //     if (rows.length > 0)
    //       confirmNotification({
    //         type: NOTIFICATIONS_TYPES.done,
    //         message: 'Se eliminarán las filas de partidas añadidas',
    //       })

    //     fieldListPartidas = {}
    //     fieldListErrorsPartidas = {}
    //     numsRows = 0
    //     partidasListContainer.innerHTML = ''
    //     return
    //   }

    //   ejercicio = await getEjecicio(e.target.value)

    //   montos.total = ejercicio.situado
    //   montos.restante =
    //     ejercicio.distribuido === 0 ? ejercicio.situado : ejercicio.restante
    //   montoTotalElement.textContent = montos.total
    //   actualizarMontoRestante(montos.restante)
    //   cargarPartidas()
    // }
    if (e.target.classList.contains('partida-monto')) {
      actualizarMontoRestante(montos.restante)
    }

    if (e.target.classList.contains('distribucion-input')) {
      if (!e.target.value) return (fieldList[e.target.name] = 0)

      fieldList[e.target.name] = e.target.value
    }
    if (e.target.classList.contains('partida-input')) {
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })

      console.log(fieldListPartidas)
    } else {
      // fieldList = validateInput({
      //   target: e.target,
      //   fieldList,
      //   fieldListErrors,
      //   type: fieldListErrors[e.target.name].type,
      // })
      // console.log(e.target.value, e.target)
    }

    // console.log(fieldListPartidas, fieldListErrorsPartidas)
  }

  // CARGAR LISTA DE PARTIDAS

  // AÑADIR FILA DE PARTIDA

  async function addRow() {
    let newNumRow = numsRows + 1
    numsRows++

    partidalist.insertAdjacentHTML('beforeend', partidaRow(newNumRow))

    // AÑADIR ESTADO Y ERRORES A INPUTS

    // fieldListPartidas[`partida-${newNumRow}`] = ''
    // fieldListErrorsPartidas[`partida-${newNumRow}`] = {
    //   value: true,
    //   message: 'Partida inválida',
    //   type: 'partida',
    // }
    fieldListPartidas[`partida-monto-${newNumRow}`] = ''
    fieldListErrorsPartidas[`partida-monto-${newNumRow}`] = {
      value: true,
      message: 'Monto inválido',
      type: 'number3',
    }

    let options = [`<option value=''>Elegir partida...</option>`]

    partidas.fullInfo.forEach((option) => {
      let opt = `<option value="${option.id}">${option.partida} ${option.descripcion}</option>`
      options.push(opt)
    })

    let partidasList = d.getElementById(`partida-${newNumRow}`)
    partidasList.innerHTML = ''

    partidasList.innerHTML = options.join('')

    $('.chosen-select')
      .chosen()
      .change(function (obj, result) {
        console.log('changed: %o', arguments)
      })

    return
  }

  function actualizarMontoRestante() {
    let montoRestanteElement = d.getElementById('monto-restante')

    let inputsPartidasMontos = d.querySelectorAll('.partida-monto')

    // REINICIAR MONTO ACUMULADO
    montos.acumulado = 0

    inputsPartidasMontos.forEach((input) => {
      montos.acumulado += Number(formatearFloat(input.value))
    })

    if (isNaN(montos.acumulado)) {
      montoRestanteElement.innerHTML = `<span class="text-secondary">¡Revise los montos!</span>`
      return
    }

    let montoRestante = montos.restante - montos.acumulado

    if (montoRestante < 0) {
      montoRestanteElement.innerHTML = `<span class="text-danger">${separadorLocal(
        montoRestante
      )}</span>`
      return montoRestante
    }
    if (montoRestante > 0) {
      montoRestanteElement.innerHTML = `<span class="text-success">${separadorLocal(
        montoRestante
      )}</span>`
      return montoRestante
    }

    montoRestanteElement.innerHTML = `<span class="text-secondary">${separadorLocal(
      montoRestante
    )}</span>`
    return montoRestante
  }

  function validarPartidas() {
    let rows = d.querySelectorAll('[data-row]')
    let rowsArray = Array.from(rows)

    let montoRestante = 0

    // VALIDAR LOS INPUTS DE CADA FILA
    // rows.forEach((el) => {
    //   let partidaInput = el.querySelector(`#partida-${el.dataset.row}`)
    //   let montoInput = el.querySelector(`#partida-monto-${el.dataset.row}`)

    //   validateInput({
    //     target: partidaInput,
    //     type: fieldListErrorsPartidas[partidaInput.name].type,
    //     fieldList: fieldListPartidas,
    //     fieldListErrors: fieldListErrorsPartidas,
    //   })

    //   validateInput({
    //     target: montoInput,
    //     type: fieldListErrorsPartidas[montoInput.name].type,
    //     fieldList: fieldListPartidas,
    //     fieldListErrors: fieldListErrorsPartidas,
    //   })
    // })

    // if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
    //   return toastNotification({
    //     type: NOTIFICATIONS_TYPES.fail,
    //     message:
    //       'La distribución de partidas posee datos erróneos. Elimine o actualice las filas',
    //   })
    // }

    // VERIFICAR SI SE HAN SELECCIONADO PARTIDAS
    if (rowsArray.length < 1) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'No se han añadido partidas',
      })
      return false
    }

    let mappedPartidas = rowsArray.map((el) => {
      let partidaInput = el.querySelector(`#partida-${el.dataset.row}`)
      let montoInput = el.querySelector(`#partida-monto-${el.dataset.row}`)

      let partidaEncontrada = partidas.fullInfo.find(
        (partida) => partida.id === partidaInput.value
      )

      // Verificar si la partida introducida existe

      if (!partidaEncontrada) {
        return false
      }

      return [
        partidaEncontrada.id,
        formatearFloat(montoInput.value),
        fieldList.id_ejercicio,
        fieldList.id_sector,
        fieldList.id_programa,
        fieldList.id_proyecto,
        fieldList.id_actividad,
      ]
    })

    console.log(mappedPartidas)

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
    let inputs = Array.from(d.querySelectorAll('[data-row] .partida-partida'))

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

  async function cargarSelectSectores() {
    let selectSector = d.getElementById('search-select-sector')
    let selectPrograma = d.getElementById('search-select-programa')
    let selectProyecto = d.getElementById('search-select-proyecto')

    let sectores = await selectTables('pl_sectores')
    let proyectos = await selectTables('pl_proyectos')

    let options = [`<option value=''>Elegir sector...</option>`]

    sectores.forEach((sector) => {
      let option = `<option value='${sector.id}'>${sector.sector} - ${sector.denominacion}</option>`
      options.push(option)
    })

    let optionsProyectos = [`<option value='0'>Elegir proyecto...</option>`]

    proyectos.forEach((proyecto) => {
      let option = `<option value='${proyecto.id}'>${proyecto.proyecto_id} - ${proyecto.denominacion}</option>`
      optionsProyectos.push(option)
    })

    selectSector.innerHTML = options.join('')
    selectProyecto.innerHTML = optionsProyectos.join('')

    $('.chosen-sector')
      .chosen()
      .change(function (obj, result) {
        fieldList.id_sector = result.selected
        console.log('changed: %o', result)

        selectTables('pl_programas').then((res) => {
          let optionsPrograma = [`<option value=''>Elegir programa...</option>`]

          res.forEach((programa) => {
            if (programa.sector === fieldList.id_sector) {
              let option = `<option value='${programa.id}'>${programa.programa} - ${programa.denominacion}</option>`
              optionsPrograma.push(option)
            }
          })

          selectPrograma.innerHTML = optionsPrograma.join('')

          $('.chosen-programa').trigger('chosen:updated')
        })
      })

    $('.chosen-programa')
      .chosen()
      .change(function (obj, result) {
        fieldList.id_programa = result.selected
      })

    $('.chosen-proyecto')
      .chosen()
      .change(function (obj, result) {
        fieldList.proyecto = result.selected
      })
  }

  function enviarInformacion(data) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: `¿Desea registrar esta distribución presupuestaria? Monto total: ${separadorLocal(
        montos.acumulado
      )}`,
      successFunction: async function () {
        let res = await enviarDistribucionPresupuestaria({ arrayDatos: data })
        let ejericioActualizado = await getEjecicio(ejercicioFiscal.id)
        if (res.success) {
          loadDistribucionTable(ejericioActualizado.distribucion_partidas)
          recargarEjercicio()
          closeCard()
        }
      },
    })
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

function partidaRow(partidaNum) {
  let row = `<div class='row slide-up-animation' data-row='${partidaNum}'>
      <div class='col'>
        <div class='form-group'>
          <label for='monto' class='form-label'>
            Partida
          </label>
          <select
            class='form-control partida-input partida-partida chosen-select'
            type='text'
            placeholder='Partida...'
            name='partida-${partidaNum}'
            id='partida-${partidaNum}'
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
                class='form-control partida-input partida-monto'
                type='text'
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

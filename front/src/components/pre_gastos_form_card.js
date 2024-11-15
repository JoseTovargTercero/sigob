import { selectTables } from '../api/globalApi.js'
import {
  getTiposGastos,
  registrarGasto,
  registrarTipoGasto,
} from '../api/pre_gastos.js'
import { loadGastosTable } from '../controllers/pre_gastosFuncionamientoTable.js'

import {
  confirmNotification,
  formatearFloat,
  insertOptions,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { pre_gastosTipo_form_card } from './pre_gastoTipo_form_card.js'

const d = document

export const pre_gastos_form_card = async ({
  elementToInsert,
  ejercicioFiscal,
  recargarEjercicio,
}) => {
  const cardElement = d.getElementById('gastos-form-card')
  if (cardElement) cardElement.remove()

  console.log(ejercicioFiscal.id)

  let fieldList = {
    id_tipo: '',
    monto: 0,
    beneficiario: '',
    identificacion: '',
    fecha: '',
    descripcion: '',
    id_ejercicio: ejercicioFiscal.id,
    distribuciones: [],
  }

  let fieldListPartidas = {}
  let fieldListErrors = {
    beneficiario: {
      value: true,
      message: 'Elija un tipo de beneficiario',
      type: 'text',
    },
    identificacion: {
      value: true,
      message: 'Elija un tipo de beneficiario',
      type: 'text',
    },
    monto: {
      value: true,
      message: 'Coloque un monto válido',
      type: 'number3',
    },
    fecha: {
      value: true,
      message: 'Coloque una fecha válida',
      type: 'date',
    },
    descripcion: {
      value: true,
      message: 'Coloque una descripción válida',
      type: 'textarea',
    },
  }

  const cargarTiposGastos = async () => {
    let selectTiposGastos = d.getElementById('search-select-tipo-gasto')
    let data = await getTiposGastos()
    let options = [`<option>Elegir...</option>`]

    data.forEach((gasto) => {
      let option = `<option value='${gasto.id}'>${gasto.nombre}</option>`
      options.push(option)
    })

    selectTiposGastos.innerHTML = options.join('')

    $('#search-select-tipo-gasto').trigger('chosen:updated')
  }

  let card = `   <div class='card slide-up-animation' id='gastos-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Registro de nuevo gasto presupuestario</h5>
          <small class='mt-0 text-muted'>
            Introduzca el tipo de gasto y montó para ser procesado
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
        <form id='gastos-form'>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='search-select-tipo-gasto' class='form-label'>
                  Tipo de gasto
                </label>
                <div class='input-group'>
                  <div class='w-80'>
                    <select
                      class='form-select gasto-input'
                      name='id_tipo'
                      id='search-select-tipo-gasto'
                    ></select>
                  </div>
                  <div class='input-group-prepend'>
                    <button
                      type='button'
                      id='add-tipo-gasto'
                      class='input-group-text btn btn-primary'
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='monto' class='form-label'>
                  Monto
                </label>
                <input
                  class='form-control gasto-input'
                  type='text'
                  name='monto'
                  id='monto'
                  placeholder='00,00 Bs'
                />
              </div>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='beneficiario' class='form-label'>
                  Beneficiario
                </label>
                <input
                  class='form-control gasto-input'
                  type='text'
                  name='beneficiario'
                  id='beneficiario'
                  placeholder='Beneficiario...'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='identificacion' class='form-label'>
                  Identificacion
                </label>
                <input
                  class='form-control gasto-input'
                  type='text'
                  name='identificacion'
                  id='identificacion'
                  placeholder='Identificación del beneficiario...'
                />
              </div>
            </div>
          </div>
          <div class='row'>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='fecha' class='form-label'>
                  Fecha del gasto
                </label>
                <input
                  class='form-control gasto-input'
                  type='date'
                  name='fecha'
                  id='fecha'
                />
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='descripcion' class='form-label'>
                  DESCRIPCIóN
                </label>
                <textarea
                  class='form-control gasto-input'
                  name='descripcion'
                  id='descripcion'
                  placeholder='Se registra un gasto de...'
                  rows='2'
                ></textarea>
              </div>
            </div>
          </div>
          <div class='row'>
          <h6>Distribuciónes del ejercicio fiscal ${ejercicioFiscal.ano}</h6>
            <div class='col-sm' id='distribuciones-container'>
            </div>
          </div>
          <div class='row'>
            <div class='form-group'>
              <button
                type='button'
                class='btn btn-sm bg-brand-color-1 text-white'
                id='add-row'
              >
                <i class='bx bx-plus'></i>Agregar otra partida
              </button>
            </div>
          </div>
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='gastos-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let numsRows = 0

  $('#search-select-tipo-gasto')
    .chosen()
    .change(function (obj, result) {
      let value = result.selected
      fieldList.id_tipo = value
    })

  cargarTiposGastos()

  // cargarBeneficiarios()

  let distribucionesContainer = d.getElementById('distribuciones-container')

  const formElement = d.getElementById('gastos-form')

  const closeCard = () => {
    let cardElement = d.getElementById('gastos-form-card')

    cardElement.remove()
    d.removeEventListener('click', validateClick)
    formElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
      let gastosRegistrarCointaner = d.getElementById(
        'gastos-registrar-container'
      )
      gastosRegistrarCointaner.classList.remove('hide')
    }
    if (e.target.id === 'add-tipo-gasto') {
      closeCard()

      pre_gastosTipo_form_card({ elementToInsert: 'gastos-view' })
    }

    if (e.target.id === 'gastos-guardar') {
      let inputs = d.querySelectorAll('.gastos-input')

      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      console.log(fieldList, fieldListErrors)

      // if (Object.values(fieldListErrors).some((el) => el.value)) {
      //   toastNotification({
      //     type: NOTIFICATIONS_TYPES.fail,
      //     message: 'Hay campos inválidos',
      //   })
      //   return
      // }

      // if (Object.values(fieldList).some((el) => !el)) {
      //   toastNotification({
      //     type: NOTIFICATIONS_TYPES.fail,
      //     message: 'No se ha completado la información necesaria',
      //   })
      //   return
      // }

      let partidasValidadas = validarPartidas()
      console.log(partidasValidadas)
      if (!partidasValidadas) {
        return
      }

      fieldList.distribuciones = partidasValidadas

      if (validarInputIguales()) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message:
            'Está realizando una asignación a una partida 2 o más veces. Valide nuevamente por favor',
        })
        return
      }

      enviarInformacion()
    }

    if (e.target.id === 'add-row') {
      addRow()
    }
  }

  async function addRow() {
    let newNumRow = numsRows + 1
    numsRows++

    distribucionesContainer.insertAdjacentHTML(
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
        console.log('changed: %o', arguments)
      })

    return
  }

  function validarPartidas() {
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

      let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
        (partida) => Number(partida.id) === Number(partidaInput.value)
      )

      console.log(
        ejercicioFiscal.distribucion_partidas,
        partidaEncontrada,
        partidaInput
      )

      // Verificar si la partida introducida existe

      if (!partidaEncontrada) {
        return false
      }

      return { id_distribucion: partidaEncontrada.id, monto: montoInput.value }
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

  function enviarInformacion() {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar este gasto?',
      successFunction: async function () {
        let res = await registrarGasto({ data: fieldList })
        if (res.success) {
          closeCard()
          recargarEjercicio()
        }
      },
    })
  }

  function validateInputFunction(e) {
    if (e.target.classList.contains('gasto-input')) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })

      if (e.target.name === 'monto') {
        fieldList.monto = formatearFloat(e.target.value)
      }

      console.log(e.target, e.target.value)

      console.log(fieldList, fieldListErrors)
    }

    if (e.target.id === 'search-select-tipo-beneficiario') {
      cargarBeneficiarios()
    }
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}

function partidaRow(partidaNum) {
  let row = `   <div class='row slide-up-animation' data-row='${partidaNum}'>
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
                class='form-control partida-input partida-monto'
                type='text'
                name='distribucion-monto-${partidaNum}'
                id='distribucion-monto-${partidaNum}'
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

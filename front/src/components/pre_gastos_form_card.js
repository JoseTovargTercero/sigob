import {
  generarCompromisoPdf,
  registrarCompromiso,
} from '../api/pre_compromisos.js'
import { obtenerDistribucionSecretaria } from '../api/pre_entes.js'
import {
  actualizarGasto,
  getCorrelativoTipoGasto,
  getGasto,
  getTiposGastos,
  registrarGasto,
} from '../api/pre_gastos.js'
import { loadTipoGastosTable } from '../controllers/pre_gastosFuncionamientoTable.js'

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
  id,
}) => {
  const cardElement = d.getElementById('gastos-form-card')
  if (cardElement) cardElement.remove()

  console.log(ejercicioFiscal)

  let fieldList = {
    id_tipo: '',
    monto: 0,
    beneficiario: '',
    identificador: '',
    fecha: '',
    descripcion: '',
    id_ejercicio: ejercicioFiscal.id,
    distribuciones: [],
    codigo: 0,
  }

  if (id) {
    let gasto = await getGasto(id)
    console.log(gasto)

    fieldList = {
      id_tipo: gasto.id_tipo,
      monto: gasto.monto_gasto,
      beneficiario: gasto.beneficiario,
      identificador: gasto.identificador,
      fecha: gasto.fecha,
      descripcion: gasto.descripcion_gasto,
      id_ejercicio: ejercicioFiscal.id,
      distribuciones: gasto.informacion_distribuciones,
    }

    let object = {
      id: 6,
      nombre_tipo_gasto: '123',
      descripcion_gasto: '123',
      monto_gasto: '123',
      fecha: '2025-02-19',
      correlativo: 'C00002-2025',
      numero_compromiso: '',
      status_gasto: 0,
      beneficiario: '123',
      identificador: '123',
      id_compromiso: 6,
      id_ejercicio: 1,
      informacion_distribuciones: [
        {
          id_distribucion: 114,
          monto: 20000,
          partida: '401.05.01.00.0000',
          nombre_partida: null,
          descripcion_partida: 'Aguinaldos al personal empleado',
          sector: '01',
          programa: '04',
        },
      ],
    }
  }

  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  let montos = { acumulado: 0 }

  let fieldListErrors = {
    beneficiario: {
      value: true,
      message: 'Elija un tipo de beneficiario',
      type: 'text',
    },
    identificador: {
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
      type: 'textarea',
    },
    descripcion: {
      value: true,
      message: 'Coloque una descripción válida',
      type: 'textarea',
    },
    codigo: {
      value: true,
      message: 'Coloque un numero de compromiso válido',
      type: 'number',
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

    $('#search-select-tipo-gasto')
      .val(fieldList.id_tipo)
      .trigger('chosen:updated')
  }

  let partidasDisponibles = await obtenerDistribucionSecretaria({
    id_ejercicio: ejercicioFiscal.id,
  })

  console.log(partidasDisponibles)

  let card = `  <div class='card slide-up-animation' id='gastos-form-card'>
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
                  <select
                    class='form-select'
                    name='id_tipo'
                    id='search-select-tipo-gasto'
                  ></select>
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
${
  !id
    ? ` <div class='row d-none slide-up-animation' id='compromiso-input'>
      <div class='form-group' >
        <label for='codigo' class='form-label'>
          Identificar compromiso
        </label>
        <div class='input-group mb-3'>
          <span class='input-group-text mb-auto' id='compromiso-prefijo'>
            No seleccionado
          </span>
          <div class='col'>
            <input
              class='form-control gasto-input'
              type='number'
              name='codigo'
              id='codigo'
              placeholder='Identificación para el compromiso'
            />
          </div>
        </div>
      </div>
    </div>`
    : ''
}
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
                <label for='identificador' class='form-label'>
                  Identificacion
                </label>
                <input
                  class='form-control gasto-input'
                  type='text'
                  name='identificador'
                  id='identificador'
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
                  Descripción
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
            <div class='col-sm' id='distribuciones-container'></div>
          </div>
          <div class='text-center'>
            <button
              type='button'
              class='btn btn-sm bg-brand-color-1 text-white'
              id='add-row'
            >
              <i class='bx bx-plus'></i> AGREGAR PARTIDA
            </button>
          </div>
        </form>
      </div>
      <div class='card-footer text-center'>
        <button class='btn btn-primary' id='gastos-guardar'>
          ${id ? 'Actualizar' : 'Guardar'}
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let numsRows = 0

  let correlativoRecomendado

  let tipoGastoSelect = $('#search-select-tipo-gasto')

  tipoGastoSelect.chosen().change(function (obj, result) {
    let value = result.selected
    fieldList.id_tipo = value

    if (id) return

    let compromisoContainer = d.getElementById('compromiso-input')
    let codigoElement = d.getElementById('codigo')
    let prefijoElement = d.getElementById('compromiso-prefijo')

    if (compromisoContainer.classList.contains('d-none')) {
      compromisoContainer.classList.remove('d-none')
    }

    getCorrelativoTipoGasto(ejercicioFiscal.id, fieldList.id_tipo).then(
      (res) => {
        codigoElement.value = res.numero_compromiso_recomendado
        prefijoElement.textContent = res.prefijo

        correlativoRecomendado = res

        let inputs = d.querySelectorAll('.gasto-input')

        inputs.forEach((input) => {
          fieldList = validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          })
        })
      }
    )
  })

  cargarTiposGastos()

  // cargarBeneficiarios()

  let distribucionesContainer = d.getElementById('distribuciones-container')

  const formElement = d.getElementById('gastos-form')

  if (id) {
    let inputs = d.querySelectorAll('.gasto-input')

    inputs.forEach((input) => {
      input.value = fieldList[input.name]
    })

    inputs.forEach((input) => {
      if (input.name === 'id_tipo') {
      } else {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      }
    })

    if (fieldList.distribuciones.length > 0) {
      fieldList.distribuciones.forEach((distribucion) => {
        addRow(distribucion.id_distribucion)
        let row = d.querySelector(`[data-row="${numsRows}"]`)

        row.querySelector(`#distribucion-monto-${numsRows}`).value =
          distribucion.monto
      })
    }
  }

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
    }
    if (e.target.id === 'add-tipo-gasto') {
      closeCard()

      pre_gastosTipo_form_card({
        elementToInsert: 'gastos-view',
        ejercicioFiscal,
        reset: () => {
          loadTipoGastosTable()
        },
      })
    }

    if (e.target.id === 'gastos-guardar') {
      let inputs = d.querySelectorAll('.gasto-input')

      inputs.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      console.log(fieldList, fieldListErrors)

      if (Object.values(fieldList).some((el) => !el)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No se ha completado la información necesaria',
        })
        return
      }

      if (id) {
        delete fieldList.codigo
        delete fieldListErrors.codigo
      }

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Hay campos inválidos',
        })
        return
      }

      let partidasValidadas = validarPartidas()

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

      let inputsMontos = Array.from(d.querySelectorAll('.distribucion-monto'))
      inputsMontos.forEach((input) => {
        fieldListPartidas = validateInput({
          target: input,
          fieldList: fieldListPartidas,
          fieldListErrors: fieldListErrorsPartidas,
          type: fieldListErrorsPartidas[input.name],
        })
      })

      if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Hay montos inválidos',
        })
        return
      }

      if (montos.acumulado > fieldList.monto) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'La distribución de partidas supera al monto total',
        })
        return
      }
      enviarInformacion()
    }

    if (e.target.id === 'add-row') {
      if (Number(fieldList.monto) < 1) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Antes de añadir partidas añada el monto total usado',
        })
        return
      }

      if (partidasDisponibles.length === 0) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No hay partidas con presupuesto disponible',
        })
        return
      }
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
        },
      })
    }
  }

  async function addRow(defaultOptionValue) {
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
    fieldListErrorsPartidas[`distribucion-monto-${newNumRow}`] = {
      value: true,
      message: 'Monto inválido',
      type: 'number3',
    }

    let options = [`<option value='0'>Elegir partida...</option>`]

    partidasDisponibles.forEach((partida) => {
      let sppa = `
      ${partida.sector_denominacion ? partida.sector_denominacion : '00'}.${
        partida.programa_denominacion ? partida.programa_denominacion : '00'
      }.${
        partida.proyecto_denominacion ? partida.proyecto_denominacion : '00'
      }.${partida.id_actividad ? partida.id_actividad : '00'}`

      let opt = `<option value='${partida.id_distribucion}'>${sppa}.${
        partida.partida
      } - ${partida.ente_nombre[0].toUpperCase()}${partida.ente_nombre
        .substr(1, partida.ente_nombre.length - 1)
        .toLowerCase()}</option>`
      options.push(opt)
    })

    let partidasList = d.getElementById(`distribucion-${newNumRow}`)
    partidasList.innerHTML = ''

    partidasList.innerHTML = options.join('')

    if (defaultOptionValue) {
      console.log(defaultOptionValue)
      let distribucionSelect = $(`#distribucion-${numsRows}`)

      distribucionSelect.chosen().change(function (obj, result) {
        console.log('changed: %o', arguments)
      })

      distribucionSelect.val(defaultOptionValue).trigger('chosen:updated')
    } else {
      $('.chosen-distribucion')
        .chosen()
        .change(function (obj, result) {
          console.log('changed: %o', arguments)
        })
    }
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

      let partidaEncontrada = partidasDisponibles.find(
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
    console.log(fieldList)

    let informacion = {
      ...fieldList,
      monto: formatearFloat(fieldList.monto),
    }

    if (id) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea actualizar este gasto?',
        successFunction: async function () {
          let res = await actualizarGasto({ data: { id, ...informacion } })
          if (res.success) {
            closeCard()
            recargarEjercicio()
          }
        },
      })
    } else {
      let compromisoGenerado = `${correlativoRecomendado.prefijo}-${fieldList.codigo}`

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Desea registrar este gasto?',
        successFunction: async function () {
          let res = await registrarGasto({
            data: { ...informacion, codigo: compromisoGenerado },
          })
          if (res.success) {
            generarCompromisoPdf(
              res.compromiso.id_compromiso,
              res.compromiso.correlativo
            )

            closeCard()
            recargarEjercicio()
          }
        },
      })
    }
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
    }

    if (e.target.id === 'search-select-tipo-beneficiario') {
      cargarBeneficiarios()
    }

    if (e.target.classList.contains('distribucion-monto')) {
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })

      let inputsPartidasMontos = d.querySelectorAll('.distribucion-monto')

      // REINICIAR MONTO ACUMULADO
      montos.acumulado = 0

      inputsPartidasMontos.forEach((input) => {
        montos.acumulado += Number(formatearFloat(input.value))
      })
    }
  }

  formElement.addEventListener('input', validateInputFunction)
  d.addEventListener('click', validateClick)
}

function partidaRow(partidaNum) {
  let row = `<div class='row slide-up-animation' data-row='${partidaNum}'>
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

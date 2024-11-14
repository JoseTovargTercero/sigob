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

  let fieldList = {
    id_tipo: '',
    monto: 0,
    tipo_beneficiario: '',
    id_beneficiario: '',
    fecha: '',
    descripcion: '',
    id_ejercicio: ejercicioFiscal.id,
    id_distribucion: '',
  }
  let fieldListErrors = {
    tipo_beneficiario: {
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

  const cargarBeneficiarios = async () => {
    let selectBeneficiarios = d.getElementById('search-select-beneficiario')
    let data
    let options = [`<option>Elegir...</option>`]
    if (fieldList.tipo_beneficiario === '0') {
      data = await selectTables('entes_dependencias')
      data.forEach((ente) => {
        let option = `<option value='${ente.id}'>${ente.actividad} - ${ente.ente_nombre}</option>`
        options.push(option)
      })
    } else {
      data = await selectTables('empleados')

      data.forEach((empleado) => {
        let option = `<option value='${empleado.id}'>${empleado.nombres} - ${empleado.cedula}</option>`
        options.push(option)
      })
    }

    selectBeneficiarios.innerHTML = options.join('')

    $('#search-select-beneficiario').trigger('chosen:updated')
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

  const cargarDistribuciones = () => {
    let selectDistribucion = d.getElementById('search-select-distribucion')
    let data = ejercicioFiscal.distribucion_partidas

    let options = [`<option>Elegir...</option>`]

    data.forEach((el) => {
      let sector_programa_proyecto = `${
        el.sector_informacion ? el.sector_informacion.sector : '0'
      }.${el.programa_informacion ? el.programa_informacion.programa : '0'}.${
        el.proyecto_informacion == 0 ? '00' : el.proyecto_informacion.proyecto
      }.${el.id_actividad == 0 ? '00' : el.id_actividad}`

      let option = `<option value='${el.id}'>${sector_programa_proyecto} - ${el.partida}</option>`
      options.push(option)
    })

    selectDistribucion.innerHTML = options.join('')

    $('#search-select-distribucion').trigger('chosen:updated')
  }

  let card = `<div class='card slide-up-animation' id='gastos-form-card'>
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
                <label for='search-select-distribucion' class='form-label'>
                  Distribuciónes del ejercicio fiscal ${ejercicioFiscal.ano}
                </label>
                <select
                  class='form-select'
                  name='tipo_beneficiario'
                  id='search-select-distribucion'
                >
                  <option value='' selected>
                    Elegir...
                  </option>
                </select>
              </div>
            </div>
          </div>
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
                <label for='search-select-tipo-beneficiario' class='form-label'>
                  Tipo de beneficiario
                </label>
                <select
                  class='form-select gasto-input'
                  name='tipo_beneficiario'
                  id='search-select-tipo-beneficiario'
                >
                  <option value='' selected>
                    Elegir...
                  </option>
                  <option value='0'>Ente</option>
                  <option value='1'>Empleado</option>
                </select>
              </div>
            </div>
            <div class='col-sm'>
              <div class='form-group'>
                <label for='search-select-beneficiario' class='form-label'>
                  Tipo de beneficiario
                </label>
                <select
                  class='form-select'
                  name='beneficiario'
                  id='search-select-beneficiario'
                >
                  <option value='' selected>
                    Seleccione un tipo de beneficiario
                  </option>
                </select>
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
        </form>
      </div>
      <div class='card-footer'>
        <button class='btn btn-primary' id='gastos-guardar'>
          Guardar
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  $('#search-select-beneficiario')
    .chosen()
    .change(function (obj, result) {
      let value = result.selected
      fieldList.id_beneficiario = value
    })

  $('#search-select-tipo-gasto')
    .chosen()
    .change(function (obj, result) {
      let value = result.selected
      fieldList.id_tipo = value
    })

  $('#search-select-distribucion')
    .chosen()
    .change(function (obj, result) {
      let value = result.selected
      fieldList.id_distribucion = value
    })

  cargarTiposGastos()
  cargarDistribuciones()

  // cargarBeneficiarios()

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

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Hay campos inválidos',
        })
        return
      }

      if (Object.values(fieldList).some((el) => !el)) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No se ha completado la información necesaria',
        })
        return
      }

      enviarInformacion()
    }
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

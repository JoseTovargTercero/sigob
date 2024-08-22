import { getDependencyData, getJobData } from '../api/empleados.js'
import { revertirCambios } from '../api/movimientos.js'
import { eliminarPeticionNomina } from '../api/peticionesNomina.js'
import { loadRequestTable } from '../controllers/peticionesTable.js'
import {
  confirmNotification,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document

export const nomCorregirCard = ({
  elementToInsertId,
  peticionInfo,
  peticionCorrecciones,
}) => {
  let element = d.getElementById(elementToInsertId)

  let fieldList = {
    nombres: '',
    nacionalidad: '',
    cedula: 0,
    status: '',
    instruccion_academica: '',
    cod_cargo: '',
    fecha_ingreso: '',
    otros_años: 0,
    hijos: 0,
    beca: 0,
    discapacidades: '',
    banco: '',
    cuenta_bancaria: '',
    // tipo_cuenta: 0,
    id_dependencia: '',
    tipo_nomina: 0,
    cod_empleado: '441151',
    correcion: 0,
    observacion: '',
  }

  let fieldListErrors = {
    nombres: {
      value: true,
      message: 'Introducir un campo válido',
      type: 'text',
    },
    nacionalidad: {
      value: true,
      message: 'Introducir un campo válido',
      type: 'text',
    },
    cedula: {
      value: true,
      message: 'Introduzca cédula válida',
      type: 'cedula',
    },
    status: {
      value: false,
      message: 'Elija una opción',
      type: 'text',
    },
    instruccion_academica: {
      value: true,
      message: 'Elija una opción',
      type: 'text',
    },
    cod_cargo: {
      value: true,
      message: 'Elija un cargo',
      type: 'number',
    },
    fecha_ingreso: {
      value: true,
      message: 'Fecha inválida o mayor',
      type: 'date',
    },
    otros_años: {
      value: true,
      message: 'Introducir cantidad o "0"',
      type: 'number2',
    },
    hijos: {
      value: true,
      message: 'Introducir cantidad o "0"',
      type: 'number2',
    },
    beca: {
      value: true,
      message: 'Introducir cantidad o "0"',
      type: 'number2',
    },
    banco: {
      value: true,
      message: 'Elija un banco',
      type: 'text',
    },
    cuenta_bancaria: {
      value: true,
      message: 'Introducir N° de cuenta válido',
      type: 'cuenta_bancaria',
    },
    discapacidades: {
      value: true,
      message: 'Elija una opción',
      type: 'number2',
    },
    // tipo_cuenta: {
    //   value: true,
    //   message: 'Elegir tipo de cuenta',
    //   type: 'number2',
    // },
    id_dependencia: {
      value: true,
      message: 'Elegir una dependencia',
      type: 'number',
    },
    tipo_nomina: {
      value: null,
      message: 'Introducir un campo válido',
      type: 'number2',
    },
    observacion: {
      value: null,
      message: 'Introducir un campo válido',
      type: 'text',
    },
  }
  let correcionesManuales = []
  let movimientosRevertidos = []

  let correccionesList = peticionCorrecciones
    ? peticionCorrecciones.map((correccion) => {
        let { movimiento_descripcion, tabla, campo, descripcion, id } =
          correccion
        return ` <li class='list-group-item mb-2 slide-up-animation' data-correccion-item="${id}">
          <p>${movimiento_descripcion}</p>
          <p>${tabla}</p>
          <p>${campo}</p>
          <p>${descripcion}</p>
          <button class='btn btn-danger btn-sm' data-revertir="${id}">Revertir</button>
          <button class='btn btn-success btn-sm mr-2' data-corregir="${id}">Corregir manual</button>
        </li>`
      })
    : [`<li class='list-group-item'>Sin correcciones</li>`]

  let card = `<div class='modal-window' id='peticion-rechazada-card'>
      <div class='card modal-box slide-up-animation'>
        <div class='modal-box-header card-header'>
          <h5>Corrección general de nómina:</h5>
                <button
            id='btn-close-peticion-rechazada-card'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </div>
        <div class='card-body modal-box-content'>
          <div class='row'>
            <div class='col'>
              <h5>Correcciones a realizar:</h5>
              <ul class='list-group overflow-hidden' id="correcciones-list-container">
              ${correccionesList.join('')}
              </ul>
            </div>
            <div class='col d-none' id="corregir-input-container">
         
            </div>
          </div>
        </div>
        <div class='card-footer'>
          <h5>Motivo de rechazo:</h5>
         <p> ${peticionInfo.correccion}</p>
          <div>
           <button class='btn btn-secondary' data-reset="true">Reiniciar</button>
            <button class='btn btn-secondary' data-finish="true">Finalizar</button>
          </div>
        </div>
      </div>
    </div>`

  element.insertAdjacentHTML('beforeend', card)
  let corregirInputContainer = d.getElementById('corregir-input-container')

  // let btnClose = d.getElementById('btn-close-movimiento-card')
  // let btnConfirm = d.getElementById('btn-confirm')

  const closeModalCard = () => {
    let cardElement = d.getElementById('peticion-rechazada-card')
    cardElement.remove()
    d.removeEventListener('click', validateClick)
    return false
  }

  function filterCorreccion(id) {
    return peticionCorrecciones.find(
      (peticionCorreccion) => peticionCorreccion.id === Number(id)
    )
  }

  function insertOptions() {
    return options
      .map((option) => {
        return `<option value=${option.id}>${option.name}</option>`
      })
      .join()
  }

  async function createInput(type) {
    if (type === 'nombres')
      return `<input class="form-control" type="text" name="nombres" id="nombres" placeholder="NOMBRE COMPLETO">`

    if (type === 'cedula')
      return `<input class="form-control" type="text" name="cedula" id="cedula" placeholder="CEDULA..." maxlength="9">`

    if (type === 'nacionalidad')
      return `<select name="nacionalidad" class="form-select" id="nacionalidad">
                      <option selected="" value="">NACIONALIDAD</option>
                      <option value="V">V</option>
                      <option value="E">E</option>
                    </select>`

    if (type === 'status')
      return `<select name="status" id="status" class="form-select">
                      <option value="" selected="">ELEGIR...</option>
                      <option value="A">ACTIVO</option>
                      <option value="R">RETIRADO</option>
                      <option value="S">SUSPENDIDO</option>
                      <option value="C">COMISIÓN DE SERVICIO</option>
                    </select>`

    if (type === 'instruccion_academica') {
      let instruccion_academica = await getProfessionData()
      let options = insertOptions(instruccion_academica)

      return `<select class="form-select" name="instruccion_academica">${options}</select>`
    }

    if (type === 'cod_cargo') {
      let cod_cargo = await getJobData()
      let options = insertOptions(cod_cargo)

      return `<select class="form-select" name="cod_cargo">${options}</select>`
    }

    if (type === 'fecha_ingreso')
      return `<input class="form-control" type="date" name="fecha_ingreso" placeholder="Fecha de ingreso" id="fecha_ingreso">`

    if (type === 'otros_anios')
      return `<input class="employee-input form-control " type="number" name="hijos" placeholder="CANTIDAD DE HIJOS...">`

    if (type === 'hijos')
      return `<input class="employee-input form-control " type="number" name="hijos" placeholder="CANTIDAD DE HIJOS...">`

    if (type === 'becas')
      return `<input class="<input class="employee-input form-control " type="number" name="beca" placeholder="CANTIDAD DE BECAS...">`

    if (type === 'dispacacidades')
      return `<select name="discapacidades" class="form-select employee-select" id="discapacidades">
                      <option value="" selected="">ELEGIR...</option>
                      <option value="1">SÍ POSEE</option>
                      <option value="0">NO POSEE</option>
                    </select>`

    if (type === 'id_ependencia') {
      let cod_cargo = await getDependencyData()
      let options = insertOptions(cod_cargo)

      return `<select class="form-select" name="cod_cargo">${options}</select>`
    }
  }

  async function corregirInputFormHtml(peticionCorreccion) {
    let {
      movimiento_descripcion,
      valor_anterior,
      valor_nuevo,
      campo,
      descripcion,
      id,
    } = peticionCorreccion
    return `<form class="form slide-up-animation" id="corregir-input-form" autocomplete="off">
    <h5>Detalles de corrección</h5>
    <p class="mb-2"><b>Movimiento realizado: </b>${movimiento_descripcion}</p>
    <p class="mb-2"><b>Valor anterior: </b>${valor_anterior}</p>
    <p class="mb-2"><b>Valor nuevo: </b>${valor_nuevo}</p>
    <p class="mb-4"><b>Motivo de corrección: </b>${descripcion}</p>

    <h6 class="mb-2">* Coloque el nuevo valor al campo indicado según la correción por parte de registro y control:</h6>

    <div class="form-group" >
    <label class="form-label">${campo.toUpperCase()}</label>
    ${await createInput(campo)}
    
    </div>
     <div class="form-group">
     <button class='btn btn-danger btn-sm mr-2' data-cancel="${id}">Cancelar</button>
      <button class='btn btn-primary btn-sm' data-add="${id}">Añadir</button>
    </div>
     </form>
    
     `
  }

  // const validarInput = (e) => {
  //   console.log(fieldList)
  //   fieldList = validateInput({
  //     target: movimientoCardForm.correccion,
  //     fieldList,
  //     fieldListErrors,
  //     type: fieldListErrors[movimientoCardForm.correccion.name].type,
  //   })
  // }

  const validateClick = async (e) => {
    if (e.target.id === 'btn-close-peticion-rechazada-card') {
      closeModalCard(e)
    }

    if (e.target.dataset.revertir) {
      d.querySelectorAll('[data-correccion-item]').forEach((element) => {
        if (element.classList.contains('d-none'))
          element.classList.remove('d-none')
      })
      let corregirInputFormElement = d.getElementById('corregir-input-form')
      if (corregirInputFormElement) corregirInputFormElement.remove()

      // OCULTAR COLUMNA DE FORMULARIO
      corregirInputContainer.classList.add('d-none')

      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message:
          '¿Desea revertir esa acción? (Los cambios se aplicaran al finalizar)',
        successFunction: function () {
          // AÑADIR A MOVIMIENTOS A REVERTIR
          movimientosRevertidos.push(Number(e.target.dataset.revertir))

          let correcionListItem = d.querySelector(
            `[data-correccion-item="${e.target.dataset.revertir}"]`
          )
          correcionListItem.classList.add('hide')

          // AÑADIR ITEM LIST DE SIN CORRECIONES PENDIENTES
          if (
            correccionesList.length ===
            movimientosRevertidos.length + correcionesManuales.length
          ) {
            d.getElementById('correcciones-list-container').insertAdjacentHTML(
              'beforebegin',
              `<li id="correciones-pendientes-item" class='list-group-item'>Sin correcciones pendientes</li>`
            )
          }
        },
      })
    }

    if (e.target.dataset.corregir) {
      // MOSTRAR TODAS LAS CORRECIONES AL CAMBIAR DE FOCUS
      d.querySelectorAll('[data-correccion-item]').forEach((element) => {
        if (element.classList.contains('d-none'))
          element.classList.remove('d-none')
      })

      // OCULTAR LA CORRECCIÓN A CORREGIR
      let correcionListItem = d.querySelector(
        `[data-correccion-item="${e.target.dataset.corregir}"]`
      )
      correcionListItem.classList.add('d-none')

      // ELIMINAR EL FORMULARIO DE LA CORRECIÓN
      let corregirInputFormElement = d.getElementById('corregir-input-form')
      if (corregirInputFormElement) corregirInputFormElement.remove()

      // CARGAR INFORMACIÓN
      let peticionCorreccion = filterCorreccion(e.target.dataset.corregir)
      let corregirInputForm = await corregirInputFormHtml(peticionCorreccion)

      corregirInputContainer.classList.remove('d-none')

      corregirInputContainer.insertAdjacentHTML('beforeend', corregirInputForm)

      // ACTUALIZAR FIELDLIST CON EL INPUT GENERADO
      console.log(peticionCorreccion)
    }

    // CANCELAR CORRECIÓN
    if (e.target.dataset.cancel) {
      let corregirInputFormElement = d.getElementById('corregir-input-form')
      if (corregirInputFormElement) corregirInputFormElement.remove()

      d.querySelectorAll('[data-correccion-item]').forEach((element) => {
        element.classList.remove('d-none')
      })

      // OCULTAR COLUMNA DE FORMULARIO
      corregirInputContainer.classList.add('d-none')
    }

    // AÑADIR CORRECIONES A LA LISTA
    if (e.target.dataset.add) {
      let correcionListItem = d.querySelector(
        `[data-correccion-item="${e.target.dataset.add}"]`
      )
      correcionListItem.classList.add('hide')
      let corregirInputFormElement = d.getElementById('corregir-input-form')

      // AÑADIR A LISTA DE CORRECIONES A ENVIAR

      let peticionCorreccion = filterCorreccion(e.target.dataset.add)
      console.log(corregirInputFormElement[peticionCorreccion.campo])
      validateInput({
        target: corregirInputFormElement[peticionCorreccion.campo],
        fieldList,
        fieldListErrors,
        type: fieldListErrors[
          corregirInputFormElement[peticionCorreccion.campo].name
        ],
      })

      if (fieldListErrors[peticionCorreccion.campo].value) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete el campo antes de añadir',
        })
        return
      }

      correcionesManuales.push({
        id_correccion: peticionCorreccion.id,
        id_empleado: peticionCorreccion.id_empleado,
        tabla: peticionCorreccion.tabla,
        campo: peticionCorreccion.campo,
        nuevo_valor: corregirInputFormElement[peticionCorreccion.campo].value,
      })

      corregirInputFormElement.remove()

      // AÑADIR ITEM LIST DE SIN CORRECIONES PENDIENTES
      if (
        correccionesList.length ===
        movimientosRevertidos.length + correcionesManuales.length
      ) {
        d.getElementById('correcciones-list-container').insertAdjacentHTML(
          'beforebegin',
          `<li id="correciones-pendientes-item" class='list-group-item'>Sin correcciones pendientes</li>`
        )
      }

      // OCULTAR COLUMNA DE FORMULARIO
      corregirInputContainer.classList.add('d-none')
    }

    if (e.target.dataset.reset) {
      console.log(fieldList)
      // REINICIAR LISTAS
      movimientosRevertidos = []
      correcionesManuales = []

      let sinCorrecionesPendientesElement = d.getElementById(
        'correciones-pendientes-item'
      )
      if (sinCorrecionesPendientesElement)
        sinCorrecionesPendientesElement.remove()

      d.querySelectorAll('[data-correccion-item]').forEach((element) => {
        if (element.classList.contains('d-none'))
          element.classList.remove('d-none')

        if (element.classList.contains('hide')) element.classList.remove('hide')
      })

      // OCULTAR COLUMNA DE FORMULARIO
      corregirInputContainer.classList.add('d-none')
    }

    if (e.target.dataset.finish) {
      console.log(movimientosRevertidos, correcionesManuales)
      console.log(fieldList)
      let correcionesLength = peticionCorrecciones
        ? peticionCorrecciones.length
        : 0

      if (correcionesLength === 0) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message:
            'Se realizaran los cambios dada las correciones realizadas y se eliminará la petición actual ¿Desea continuar?',
          successFunction: async function () {
            let res2 = await eliminarPeticionNomina({
              id_peticion: peticionInfo.id,
              correlativo: peticionInfo.correlativo,
            })
            closeModalCard()
            await loadRequestTable()
            console.log(res2)
          },
        })
        return
      }
      if (
        correcionesLength >
        movimientosRevertidos.length + correcionesManuales.length
      ) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Debe completar todas las correciones antes de finalizar',
        })
        return
      }
      if (
        correcionesLength ===
        movimientosRevertidos.length + correcionesManuales.length
      ) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message:
            'Al finalizar las correciones se eliminará la petición actual. Deberá volver a realizar la petición ¿Desea continuar?',
          successFunction: async function () {
            let res = await revertirCambios({
              revertir: movimientosRevertidos,
              manual: correcionesManuales,
            })
            let res2 = await eliminarPeticionNomina({
              id_peticion: peticionInfo.id,
              correlativo: peticionInfo.correlativo,
            })
            closeModalCard()
            await loadRequestTable()
          },
        })
      }
    }
  }

  d.addEventListener('click', validateClick)

  // movimientoCardForm.addEventListener('submit', (e) => e.preventDefault())
  // movimientoCardForm.addEventListener('input', validarInput)
}

import { getCategorias } from '../api/categorias.js'
import { getDependencias } from '../api/dependencias.js'
import {
  getBankData,
  getEmployeeByCedula,
  getEmployeeData,
  getJobData,
  getProfessionData,
  sendEmployeeData,
  updateRequestEmployeeData,
} from '../api/empleados.js'
import { consultarPartida, getPartidas } from '../api/partidas.js'
import { nom_categoria_form_card } from '../components/nom_categoria_form_card.js'
import { nomCorrectionAlert } from '../components/nom_correcion_alert.js'
import { nom_dependencia_form_card } from '../components/nom_dependencia_form_card.js'

import {
  closeModal,
  confirmNotification,
  insertOptions,
  openModal,
  toastNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { ALERT_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  confirmDeleteEmployee,
  loadEmployeeTable,
  validateEmployeeTable,
} from '../controllers/empleadosTable.js'

const d = document

export const nom_empleados_form_card = ({ elementToInset, id }) => {
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
    id_categoria: '',
    id_partida: '',

    tipo_nomina: 0,
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
    id_categoria: {
      value: true,
      message: 'Elegir una unidad',
      type: 'number',
    },
    id_partida: {
      value: null,
      message: 'Elegir una partida',
      type: 'text',
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

  let clases = {
    employeeInputClass: 'employee-input',
    employeeSelectClass: 'employee-select',
    btnId: 'btn-employee-save',
    selectSearchInput: 'select-search-input',
    selectSearch: ['cargo'],
    btnAddId: 'add-dependency',
  }

  let employeeId

  let partidas

  const oldCardElement = d.getElementById('modal-employee-form')
  if (oldCardElement) oldCardElement.remove()

  let card = ` <div class='modal-window' id='modal-employee-form'>
      <div class='card modal-box'>
        <div class='card-header modal-box-header'>
          <div>
            <h5 class='mb-0'>Nuevo empleado</h5>
            <small class='text-muted mt-0'>
              Registre la información del empleado
            </small>
          </div>
        </div>
        <div class='card-body modal-box-content'>
          <form
            class='row employee-form'
            id='employee-form'
            autocomplete='off'
          >
            <div class='row mb-4'>
              <picture>
                <img
                  id='empleado-foto'
                  src='../../front/src/assets/img/default.jpg'
                  class='img-thumbnail'
                  alt='...'
                  style='height: 100px;'
                />
                <figcaption>Foto personal cargada</figcaption>
              </picture>
            </div>
            <div class='form-group'>
              <div class='row'>
                <div class='col-sm-4'>
                  <div class='mb-3'>
                    <label for='empleado-foto-input' class='form-label'>
                      Foto personal
                    </label>
                    <input
                      class='form-control'
                      type='file'
                      id='empleado-foto-input'
                    />
                  </div>
                </div>

                <div class='col-sm'>
                  <label class='form-label' for='nombres'>
                    NOMBRE COMPLETO
                  </label>
                  <input
                    class='form-control employee-input'
                    type='text'
                    name='nombres'
                    id='nombres'
                    placeholder='NOMBRE COMPLETO'
                  />
                </div>
              </div>
            </div>

            <div class=' form-group'>
              <div class='row'>
                <div class='col-sm'>
                  <label class='form-label' for='nacionalidad'>
                    NACIONALIDAD
                  </label>
                  <select
                    name='nacionalidad'
                    class='form-select employee-select'
                    id='nacionalidad'
                  >
                    <option selected value=''>
                      NACIONALIDAD
                    </option>
                    <option value='V'>V</option>
                    <option value='E'>E</option>
                  </select>
                </div>
                <div class='col-sm'>
                  <label class='form-label' for='cedula'>
                    CÉDULA
                  </label>
                  <input
                    class='employee-input form-control'
                    type='text'
                    name='cedula'
                    id='cedula'
                    placeholder='CEDULA...'
                    maxlength='9'
                  />
                </div>
                <div class='col-sm'>
                  <label class='form-label' for='status'>
                    ESTATUS DEL TRABAJADOR
                  </label>
                  <select
                    name='status'
                    id='status'
                    class='form-select employee-select'
                  >
                    <option value='' selected>
                      ELEGIR...
                    </option>
                    <option value='A'>ACTIVO</option>
                    <option value='R'>RETIRADO</option>
                    <option value='S'>SUSPENDIDO</option>
                    <option value='C'>COMISIÓN DE SERVICIO</option>
                  </select>
                </div>
              </div>
            </div>

            <div class='form-group'>
              <div class='row'>
                <div class='col-sm'>
                  <label class='form-label' for='instruccion_academica'>
                    INSTRUCCIÓN ACADÉMICA
                  </label>
                  <select
                    class='form-select employee-select'
                    name='instruccion_academica'
                    id='search-select-instruccion_academica'
                  >
                    <option value='' selected>
                      ELEGIR...
                    </option>
                  </select>
                </div>

                <div class='col-sm'>
                  <label class='form-label' for='cargo'>
                    CARGO AL QUE OPTA
                  </label>
                  <select
                    class='form-select employee-select'
                    name='cod_cargo'
                    id='search-select-cargo'
                  >
                    <option value='' selected>
                      ELEGIR...
                    </option>
                  </select>
                </div>
                <div class='col-sm'>
                  <label class='form-label' for='fecha_ingreso'>
                    FECHA DE INGRESO
                  </label>
                  <input
                    class='employee-input form-control'
                    type='date'
                    name='fecha_ingreso'
                    placeholder='Fecha de ingreso'
                    id='fecha_ingreso'
                  />
                </div>
              </div>
            </div>

            <div class='form-group'>
              <div class='row'>
                <div class='col-sm'>
                  <label class='form-label' for='otros_años'>
                    OTROS AÑOS LABORALES
                  </label>
                  <input
                    class='employee-input form-control'
                    type='number'
                    name='otros_años'
                    placeholder='Cantidad de años'
                    id='otros_años'
                  />
                </div>
                <div class='col-sm'>
                  <label class='form-label' for=''>
                    HIJOS
                  </label>
                  <input
                    class='employee-input form-control '
                    type='number'
                    name='hijos'
                    placeholder='CANTIDAD DE HIJOS...'
                  />
                </div>
                <div class='col-sm'>
                  <label class='form-label' for=''>
                    Becas
                  </label>
                  <input
                    class='employee-input form-control '
                    type='number'
                    name='beca'
                    placeholder='CANTIDAD DE BECAS...'
                  />
                </div>
              </div>
            </div>

            <div class='form-group'>
              <div class='row'>
                <div class='col-sm-2'>
                  <label class='form-label' for='discapacidades'>
                    DISCAPACIDAD
                  </label>
                  <select
                    name='discapacidades'
                    class='form-select employee-select'
                    id='discapacidades'
                  >
                    <option value='' selected>
                      ELEGIR...
                    </option>
                    <option value='1'>SÍ POSEE</option>
                    <option value='0'>NO POSEE</option>
                  </select>
                </div>

                <div class='col-sm-3'>
                  <label class='form-label' for='banco'>
                    BANCO
                  </label>
                  <select
                    name='banco'
                    class='form-select employee-select'
                    id='search-select-bancos'
                  >
                    <option value='' selected>
                      ELEGIR...
                    </option>
                  </select>
                </div>
                <div class='col-sm'>
                  <label class='form-label' for='cuenta_bancaria'>
                    N° DE CUENTA
                  </label>
                  <input
                    class='employee-input form-control'
                    type='text'
                    name='cuenta_bancaria'
                    placeholder='0000 0000 00 0000'
                    id='cuenta_bancaria'
                    maxlength='20'
                  />
                </div>
              </div>
            </div>

            <div class='form-group'>
              <div class='row '>
                <div class='col-sm '>
                  <label class='form-label' for='id_dependencia'>
                    UNIDAD
                  </label>
                  <div class='input-group'>
                    <div class='w-80'>
                      <select
                        class='form-select employee-select'
                        name='id_dependencia'
                        id='search-select-dependencias'
                      ></select>
                    </div>
                    <div class='input-group-prepend'>
                      <button
                        type='button'
                        id='add-dependency'
                        class='input-group-text btn btn-primary'
                      >
                        +
                      </button>
                    </div>
                  </div>
                </div>
                <div class='col-sm '>
                  <label class='form-label' for='id_categoria'>
                    CATEGORIA
                  </label>
                  <div class='input-group'>
                    <div class='w-80'>
                      <select
                        class='form-select employee-select'
                        name='id_categoria'
                        id='search-select-categorias'
                      ></select>
                    </div>
                    <div class='input-group-prepend'>
                      <button
                        type='button'
                        id='add-category'
                        class='input-group-text btn btn-primary'
                      >
                        +
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-sm">
                <label class="form-label" for="id_partida">PARTIDA</label>
                <select name="id_partida" class="form-select employee-select chosen-select" id="search-select-partidas">
                  <option value="" selected>ELEGIR...</option>

                </select>
              </div>
              </div>
            </div>

            <div class='form-group'>
              <div class='row'>
                <div class='form-group'>
                  <button class='btn btn-secondary' id='actualizar-opciones'>
                    ACTUALIZAR OPCIONES
                  </button>
                </div>
              </div>
            </div>

            

            <div class='form-group'>
              <div class='row'>
                <div class='form-group'>
                  <label for='observacion'>OBSERVACIONES</label>
                  <textarea
                    class='form-control employee-input'
                    name='observacion'
                    placeholder='Observación sobre el empleado...'
                    id='observacion'
                    style='height: 50px'
                  ></textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class='modal-box-footer card-footer d-flex align-items-center justify-content-center gap-2 py-0'>
          <button class='btn btn-primary ' id='btn-employee-save'>
            GUARDAR
          </button>
          <button class='btn btn-danger ' id='btn-employee-form-close'>
            CERRAR
          </button>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardElement = d.getElementById('modal-employee-form')
  let formElement = d.getElementById('employee-form')

  if (!formElement) return

  //   const btnAddElement = d.getElementById(btnAddId)
  //   const btnDependencySave = d.getElementById('dependency-save-btn')

  //   const dependenciaFormElement = d.getElementById('employee-dependencia-form')

  //   const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)

  const employeeInputElement = d.querySelectorAll(`.employee-input`)
  const employeeSelectElement = d.querySelectorAll(`.employee-select`)

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  async function loadEmployeeData() {
    let cargos = await getJobData()
    let profesiones = await getProfessionData()
    let dependencias = await getDependencias()
    let categorias = await getCategorias()
    partidas = await getPartidas()

    let bancos = await getBankData()
    insertOptions({ input: 'cargo', data: cargos })
    insertOptions({ input: 'instruccion_academica', data: profesiones })
    insertOptions({ input: 'dependencias', data: dependencias.mappedData })
    insertOptions({ input: 'categorias', data: categorias.mappedData })
    insertOptions({ input: 'bancos', data: bancos })
    ;(() => {
      let partidasSelect = d.getElementById('search-select-partidas')
      partidasSelect.innerHTML = ''

      let options = [`<option value=''>Elegir partida...</option>`]

      partidas.fullInfo.forEach((option) => {
        let opt = `<option value="${option.id}">${option.partida} - ${option.descripcion}</option>`
        options.push(opt)
      })

      partidasSelect.innerHTML = options.join('')

      $('#search-select-partidas')
        .chosen()
        .change(function (obj, result) {
          fieldList.id_partida = result.selected
          console.log('changed: %o', result)
        })

      return
    })()

    // CÓDIGO PARA OBTENER EMPLEADO EN CASO DE EDITAR
    if (id) {
      // Obtener datos de empleado dada su ID
      let employeeData = await getEmployeeData(id)
      console.log(employeeData)

      // SI EL EMPLEADO TIENE EL VERIFICADO EN 2, COLOCAR CORRECIÓN EN FORMULARCIÓN DE EDICIÓN

      if (employeeData.verificado === 2) {
        let correcionElement = d.getElementById('employee-correcion')
        if (correcionElement) correcionElement.remove()
        formElement.insertAdjacentHTML(
          'beforebegin',
          nomCorrectionAlert({
            message: employeeData.correcion,
            type: ALERT_TYPES.warning,
          })
        )
      }

      // Vacíar campo de dependencia no necesario
      delete employeeData.dependencia

      employeeData.id = employeeData.id_empleado
      if (employeeData.foto) {
        let preview = document.getElementById('empleado-foto')
        preview.src = `../../img/empleados/${employeeData.cedula}.jpg`
      }
      employeeId = employeeData.id_empleado

      console.log(employeeId)

      fieldList = employeeData

      employeeSelectElement.forEach((select) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN SELECT
        if (employeeData[select.name] !== undefined)
          select.value = employeeData[select.name]

        if (select.name === 'id_partida') {
          $('#search-select-partidas').val(employeeData[select.name])

          // Actualizar el select de Chosen
          $('#search-select-partidas').trigger('chosen:updated')
        }

        validateInput({
          target: select,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[select.name].type,
        })
      })

      employeeInputElement.forEach((input) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN INPUT
        if (input.name === 'cedula') {
          input.setAttribute('disabled', 'true')
        }

        if (employeeData[input.name] !== undefined)
          input.value = employeeData[input.name]

        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      // mostrarCodigoDependencia()

      // console.log(fieldList, fieldListErrors)
    } else {
      delete fieldList.id
      delete fieldList.employeeId
      d.getElementById('cedula').removeAttribute('disabled')
    }
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === 'btn-employee-form-close') {
      closeCard()
    }

    if (e.target.id === 'add-dependency') {
      nom_dependencia_form_card({
        elementToInsert: 'modal-employee-form',
        reloadSelect: loadDependencias,
      })
    }

    if (e.target.id === 'add-category') {
      nom_categoria_form_card({
        elementToInsert: 'modal-employee-form',
        reloadSelect: loadCategorias,
      })
    }

    if (e.target.id === 'actualizar-opciones') {
      loadCategorias()
      loadDependencias()
      const numeroFinal = 0
      let contador = 5
      let intervalo
      e.target.innerText = `ACTUALIZAR OPCIONES EN ${contador}`
      e.target.setAttribute('disabled', true)

      if (contador >= numeroFinal) {
        intervalo = setInterval(function () {
          contador--
          e.target.innerText = `ACTUALIZAR OPCIONES EN ${contador}`
          if (contador === numeroFinal) {
            clearInterval(intervalo)
            e.target.removeAttribute('disabled')
            e.target.innerText = 'ACTUALIZAR OPCIONES'
          }
        }, 1000) // Intervalo de 1 segundo (1000 milisegundos)
      }
    }

    // ENVIAR DATOS

    if (e.target.id === 'btn-employee-save') {
      // VALIDAR EI BECAS CURSADAS ES MAYOR A HIJOS

      employeeSelectElement.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      employeeInputElement.forEach((input) => {
        if (fieldListErrors[input.name])
          fieldList = validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          })
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete todo el formulario antes de avanzar',
        })
      }

      console.log(fieldList, fieldListErrors)

      if (fieldList.beca > fieldList.hijos) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Becas cursadas no puede ser mayor a la cantidad de hijos.',
        })
        return
      }
      delete fieldList.correcion

      // EDITAR EMPLEADO

      if (id)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: function () {
            sendEmployeeInformationRequest({ data: fieldList }).then((res) => {
              loadEmployeeTable()
              closeCard()
            })
          },
          message: '¿Desea actualizar la información de este empleado?',
        })

      // REGISTRAR EMPLEADO
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: function () {
          sendEmployeeData({ data: fieldList }).then((res) => {
            loadEmployeeTable()
            closeCard()
          })
        },

        message: '¿Desea registrar este empleado?',
      })
    }
  }

  async function validateInputFunction(e) {
    if (e.target.id === 'empleado-foto-input') {
      previewImage(e)
    }
    if (e.target.classList.contains('employee-input')) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains('employee-select')) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
      if (e.target.name === 'id_dependencia') {
        // mostrarCodigoDependencia()
      }
    }
  }
  async function validateFocusOutFunction(e) {
    if (e.target.name === 'cedula') {
      getEmployeeByCedula({ cedula: e.target.value }).then((res) => {
        if (!res.status) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: res.mensaje,
          })
          // Resetear input si el status es falso
          e.target.value = ''
          fieldList = validateInput({
            target: e.target,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[e.target.name].type,
          })

          formElement.otros_años.value = ''
        } else {
          // SI EL USUARIO EXISTE, SUMAR LOS OTROS AÑOS
          if (!res.otros_anios) {
            formElement.otros_años.value = ''
            return
          }

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message:
              'Existe registro de este empleado. Se actualizará el campo otros años laborales',
          })
          formElement.otros_años.value =
            Number(formElement.otros_años.value) + res.otros_anios
        }
      })
    }

    if (e.target.name === 'id_partida') {
    }
    if (e.target.classList.contains('employee-input')) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains('employee-select')) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  }

  async function loadDependencias() {
    getDependencias().then((res) => {
      // dependenciasLaborales = res.fullInfo
      insertOptions({ input: 'dependencias', data: res.mappedData })
    })

    //  mostrarCodigoDependencia()
  }

  async function loadCategorias() {
    getCategorias().then((res) => {
      // dependenciasLaborales = res.fullInfo
      insertOptions({ input: 'categorias', data: res.mappedData })
    })
  }

  function previewImage(event) {
    let input = event.target

    if (input.files === 0) return

    let file = input.files[0]

    console.log(file)

    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Tipo de archivo no admitido',
      })

      event.target.value = ''
      return
    }

    if (file.size > 2 * 1024 * 1024) {
      // Validar el tamaño del archivo
      // Mostrar un mensaje de error si el archivo es demasiado grande
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Archivo demasiado grande',
      })
      return
    }

    let reader = new FileReader()
    reader.onload = function (e) {
      console.log(e)

      let dataURL = reader.result
      let preview = document.getElementById('empleado-foto')
      preview.style.display = 'none'

      // Guardar estado de la url
      fieldList.foto = e.target.result
      if (!id) {
        fieldList.tipo_foto = file.type.replace('image/', '')
      }

      preview.src = dataURL
      preview.style.display = 'block' // Mostrar la imagen una vez cargada
    }
    reader.readAsDataURL(file)
  }

  async function sendEmployeeInformationRequest({ data }) {
    let employeeDataRequest = await getEmployeeData(employeeId),
      employeeData = employeeDataRequest

    console.log(employeeData)
    let updateData = []

    Object.entries(data).forEach((el) => {
      let propiedad = el[0]
      let valorAnterior =
        typeof employeeData[propiedad] !== 'string'
          ? String(employeeData[propiedad])
          : employeeData[propiedad]

      let valorNuevo = el[1] !== 'string' ? String(el[1]) : el[1]

      if (!valorNuevo) return

      if (propiedad === 'id' || propiedad === 'id_empleado') return

      if (valorNuevo !== valorAnterior) {
        console.log(
          propiedad,
          valorNuevo,
          valorAnterior,
          'Se actualiza: ',
          valorNuevo !== valorAnterior
        )
        updateData.push([
          Number(employeeId),
          propiedad,
          valorNuevo,
          valorAnterior,
        ])
      }
    })
    console.log(updateData)
    if (updateData.length === 0) {
      toast_s('error', 'No hay cambios para este empleado')
      return
    }

    let result = await updateRequestEmployeeData({ data: updateData })
  }

  function enviarInformacion(data) {}

  formElement.addEventListener('submit', (e) => e.preventDefault())

  loadEmployeeData()

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('focusout', validateFocusOutFunction)
  cardElement.addEventListener('click', validateClick)
}

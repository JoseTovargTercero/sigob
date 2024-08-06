import {
  getBankData,
  getDependencyData,
  getEmployeeData,
  getJobData,
  getProfessionData,
  sendDependencyData,
  sendEmployeeData,
  updateEmployeeData,
  updateRequestEmployeeData,
} from '../api/empleados.js'
import { nomCorrectionAlert } from '../components/nom_correcion_alert.js'
import { employeeCard } from '../components/nom_empleado_card.js'
import {
  closeModal,
  confirmNotification,
  openModal,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { ALERT_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { validateEmployeeTable } from './empleadosTable.js'

const d = document
const w = window

let employeeId

let dependenciasLaborales

function validateEmployeeForm({
  formElement,
  employeeInputClass,
  employeeSelectClass,
  btnId,
  btnAddId,
  fieldList = {},
  fieldListErrors = {},
  fieldListDependencias = {},
  fieldListErrorsDependencias = {},
  selectSearchInput,
  selectSearch,
}) {
  const btnElement = d.getElementById(btnId)
  const btnAddElement = d.getElementById(btnAddId)
  const btnDependencySave = d.getElementById('dependency-save-btn')

  const dependenciaFormElement = d.getElementById('employee-dependencia-form')

  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)

  const employeeInputElement = d.querySelectorAll(`.${employeeInputClass}`)
  const employeeSelectElement = d.querySelectorAll(`.${employeeSelectClass}`)

  let employeeInputElementCopy = [...employeeInputElement]
  let employeeSelectElementCopy = [...employeeSelectElement]

  const loadEmployeeData = async (id = false) => {
    let cargos = await getJobData()
    let profesiones = await getProfessionData()
    let dependencias = await getDependencyData()

    dependenciasLaborales = dependencias.fullInfo
    let bancos = await getBankData()
    insertOptions({ input: 'cargo', data: cargos })
    insertOptions({ input: 'instruccion_academica', data: profesiones })
    insertOptions({ input: 'dependencias', data: dependencias.mappedData })
    insertOptions({ input: 'bancos', data: bancos })

    // CÓDIGO PARA OBTENER EMPLEADO EN CASO DE EDITAR
    if (id) {
      // Obtener datos de empleado dada su ID
      let employeeData = await getEmployeeData(id)

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
      employeeId = employeeData.id_empleado

      console.log(employeeId)

      fieldList = employeeData

      employeeSelectElementCopy.forEach((select) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN SELECT
        if (employeeData[select.name] !== undefined)
          select.value = employeeData[select.name]

        validateInput({
          target: select,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[select.name].type,
        })
      })

      employeeInputElementCopy.forEach((input) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN INPUT
        console.log(input)
        if (
          employeeData[input.name] !== undefined &&
          input.name !== 'dependencia'
        )
          input.value = employeeData[input.name]

        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      mostrarCodigoDependencia()

      // console.log(fieldList, fieldListErrors)
    } else {
      validateInput({
        type: 'reset',
      })
      employeeSelectElementCopy.forEach((select) => {
        select.value = ''
      })

      employeeInputElementCopy.forEach((input) => {
        input.value = ''
      })

      employeeId = undefined
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

  dependenciaFormElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
      if (e.target.name === 'id_dependencia') {
        mostrarCodigoDependencia()
      }
    }
  })

  dependenciaFormElement.addEventListener('input', (e) => {
    console.log(e.target.value)
    fieldListDependencias = validateInput({
      target: e.target,
      fieldList: fieldListDependencias,
      fieldListErrors: fieldListErrorsDependencias,
      type: fieldListErrorsDependencias[e.target.name].type,
    })

    console.log(fieldListDependencias)
  })

  formElement.addEventListener('focusout', (e) => {
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  // selectSearchInputElement.forEach((input) => {
  //   const parentElement = input.parentNode
  //   const parentChildElement = parentElement.childNodes

  //   console.log(parentElement)
  //   parentElement.addEventListener('click', (e) => {
  //     activateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })

  //   parentElement.addEventListener('focusout', (e) => {
  //     desactivateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })
  // })

  d.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-edit')) {
      loadEmployeeData(e.target.dataset.id)
      openModal({ modalId: 'modal-employee-form' })
    }

    if (e.target.id === 'btn-employee-form-open') {
      loadEmployeeData()
      openModal({ modalId: 'modal-employee-form' })
    }
    if (e.target.id === 'btn-employee-form-close') {
      closeModal({ modalId: 'modal-employee-form' })
      formElement.reset()
    }

    if (e.target.id === 'add-dependency') {
      openModal({ modalId: 'modal-dependency' })
    }

    if (e.target.id === 'btn-close-dependency') {
      closeModal({ modalId: 'modal-dependency' })
      dependenciaFormElement.reset()
    }

    if (e.target === btnDependencySave) {
      console.log(
        dependenciaFormElement.cod_dependencia.value,
        dependenciaFormElement.dependencia.value
      )
      let newDependency = {
        dependencia: dependenciaFormElement.dependencia.value,
        cod_dependencia: dependenciaFormElement.cod_dependencia.value,
      }
      if (!fieldListDependencias.dependencia)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No se puede enviar una dependencia vacía',
        })

      if (Object.values(fieldListDependencias).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Error al registrar dependencia',
        })
      }

      validateNewDependency({ newDependency })
      dependenciaFormElement.reset()
      fieldListDependencias.dependencia = ''
      fieldListDependencias.cod_dependencia = ''
    }

    // ENVIAR DATOS

    if (e.target === btnElement) {
      employeeSelectElementCopy.forEach((input) => {
        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        })
      })

      employeeInputElementCopy.forEach((input) => {
        if (fieldListErrors[input.name])
          validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          })
      })

      console.log(fieldList, fieldListErrors)

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete todo el formulario antes de avanzar',
        })
      }
      delete fieldList.correcion

      // EDITAR EMPLEADO

      if (employeeId)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: sendEmployeeInformationRequest,
          successFunctionParams: { data: fieldList },
          othersFunctions: [
            function () {
              closeModal({ modalId: 'modal-employee-form' })
            },
            function () {
              validateEmployeeTable()
            },
          ],
          message: 'Los datos del empleado serán modificados.',
        })

      // REGISTRAR EMPLEADO
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: sendEmployeeData,
        successFunctionParams: { data: fieldList },
        othersFunctions: [
          loadEmployeeData,
          function () {
            closeModal({ modalId: 'modal-employee-form' })
          },
          function () {
            validateEmployeeTable()
          },
        ],
        message: 'Se enviará este empleado para su revisión',
      })
    }
  })
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
  if (updateData.length === 0) {
    toast_s('error', 'No hay cambios para este empleado')
    return
  }

  let result = await updateRequestEmployeeData({ data: updateData })
}

function insertOptions({ input, data }) {
  const selectElement = d.getElementById(`search-select-${input}`)
  selectElement.innerHTML = `<option value="">Elegir...</option>`
  const fragment = d.createDocumentFragment()
  data.forEach((el) => {
    const option = d.createElement('option')
    option.setAttribute('value', el.id)
    option.textContent = el.name
    fragment.appendChild(option)
  })

  selectElement.appendChild(fragment)
}

async function validateNewDependency({ newDependency }) {
  let isSend = await sendDependencyData({ newDependency })
  if (isSend) {
    getDependencyData().then((res) => {
      insertOptions({ input: 'dependencias', data: res.mappedData })
      dependenciasLaborales = res.fullInfo
    })
  }
  closeModal({ modalId: 'modal-dependency' })
}

function mostrarCodigoDependencia() {
  console.log(dependenciasLaborales)
  const id_dependencia = d.getElementById('search-select-dependencias').value
  const cod_dependenciaInput = d.getElementById('cod_dependencia')

  const dependenciaSeleccionada = dependenciasLaborales.find(
    (dep) => dep.id_dependencia == id_dependencia
  )
  console.log(dependenciaSeleccionada)
  if (dependenciaSeleccionada) {
    cod_dependenciaInput.value = dependenciaSeleccionada.cod_dependencia
  } else {
    cod_dependenciaInput.value = '' // Limpiar el input si no hay ninguna dependencia seleccionada
  }
}

const activateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.remove('hide')
}

const desactivateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.add('hide')
}

export { validateEmployeeForm }

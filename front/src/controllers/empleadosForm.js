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
import {
  closeModal,
  confirmNotification,
  openModal,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { ALERT_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

let employeeId

console.log(location)
function validateEmployeeForm({
  formElement,
  employeeInputClass,
  employeeSelectClass,
  btnId,
  btnAddId,
  fieldList = {},
  fieldListErrors = {},
  selectSearchInput,
  selectSearch,
}) {
  const btnElement = d.getElementById(btnId)
  const btnAddElement = d.getElementById(btnAddId)
  const btnDependencySave = d.getElementById('dependency-save-btn')
  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`)

  const employeeInputElement = d.querySelectorAll(`.${employeeInputClass}`)
  const employeeSelectElement = d.querySelectorAll(`.${employeeSelectClass}`)

  let employeeInputElementCopy = [...employeeInputElement]
  let employeeSelectElementCopy = [...employeeSelectElement]

  const loadEmployeeData = async (id) => {
    let cargos = await getJobData()
    let profesiones = await getProfessionData()
    let dependencias = await getDependencyData()
    let bancos = await getBankData()
    insertOptions({ input: 'cargo', data: cargos })
    insertOptions({ input: 'instruccion_academica', data: profesiones })
    insertOptions({ input: 'dependencias', data: dependencias })
    insertOptions({ input: 'bancos', data: bancos })

    // CÓDIGO PARA OBTENER EMPLEADO EN CASO DE EDITAR
    if (id) {
      let employeeData = await getEmployeeData(id)

      console.log(employeeData)

      employeeSelectElementCopy.forEach((select) => {
        select.value = employeeData[0][select.name]
      })

      employeeInputElementCopy.forEach((input) => {
        input.value = employeeData[0][input.name]
      })

      employeeData[0].id = employeeData[0].id_empleado
      employeeId = employeeData[0].id_empleado

      fieldList = employeeData[0]
      console.log(fieldList, fieldListErrors)

      if (employeeData[0].verificado === 2) {
        formElement.insertAdjacentHTML(
          'beforebegin',
          nomCorrectionAlert({
            message: employeeData[0].correcion,
            type: ALERT_TYPES.warning,
          })
        )
      }
    } else {
      employeeSelectElementCopy.forEach((select) => {
        select.value = ''
      })

      employeeInputElementCopy.forEach((input) => {
        input.value = ''
      })
    }
  }

  formElement.addEventListener('submit', (e) => e.preventDefault())

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
    }
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
      openModal({ modalId: 'modal-employee-form' })
    }
    if (e.target.id === 'btn-employee-form-close') {
      closeModal({ modalId: 'modal-employee-form' })
      loadEmployeeData(false)
    }

    if (e.target === btnAddElement) {
      validateModal({
        e: e,
        btnId: btnAddElement.id,
        modalId: 'modal-dependency',
      })
    }

    if (e.target === btnDependencySave) {
      let newDependency = { dependencia: fieldList.dependencia }
      console.log(fieldList)
      if (!fieldListErrors.dependencia.value) {
        validateNewDependency({ newDependency })
      }
    }

    if (e.target === btnElement) {
      if (fieldList.dependencia.length <= 0) delete fieldListErrors.dependencia

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

      if (employeeId)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: sendEmployeeInformationRequest,
          successFunctionParams: { data: fieldList },
          message: 'Complete todo el formulario antes de avanzar',
        })
      sendEmployeeInformationRequest({ data: fieldList })

      // ENVÍO DE INFORMACIÓN
      sendEmployeeData({ data: fieldList })
    }
  })
}

async function sendEmployeeInformationRequest({ data }) {
  let employeeDataRequest = await getEmployeeData(employeeId),
    employeeData = employeeDataRequest[0]

  console.log(employeeData)
  let updateData = []

  Object.entries(data).forEach((el) => {
    let propiedad = el[0]
    let valorNuevo = el[1]
    let valorAnterior = employeeData[propiedad]

    if (propiedad === 'id' || propiedad === 'id_empleado') return

    if (valorNuevo !== valorAnterior) {
      console.log(
        propiedad,
        valorNuevo,
        valorAnterior,
        'Se actualiza: ',
        valorNuevo !== valorAnterior
      )
      updateData.push([Number(employeeId), propiedad, valorNuevo])
    }
  })

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
      insertOptions({ input: 'dependencias', data: res })
    })
  }
  closeModal({ modalId: 'modal-dependency' })
}

const activateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.remove('hide')
}

const desactivateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.add('hide')
}

export { validateEmployeeForm }

import { sendTabulatorData } from '../api/tabulator.js'
import {
  confirmNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { regularExpressions } from '../helpers/regExp.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

function validateTabulatorForm({
  formId,
  secondaryFormId,
  tabulatorInputClass,
  matrixId,
  matrixRowClass,
  matrixCellClass,
  matrixInputsClass,
  btnId,
  btnSaveId,
  fieldList = {},
}) {
  const formElement = d.getElementById(formId)
  const formElementSecondary = d.getElementById(secondaryFormId)
  const tabulatorInputsElement = d.querySelectorAll(`.${tabulatorInputClass}`)
  const matrixElement = d.getElementById(matrixId)
  const btnElement = d.getElementById(btnId)
  const btnSaveElement = d.getElementById(btnSaveId)

  formElement.addEventListener('submit', (e) => {
    e.preventDefault()
  })
  formElementSecondary.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains(tabulatorInputClass)) {
      fieldList = validateInput({
        e: e,
        fieldList: fieldList,
        type: fieldList.errors[e.target.name].type,
      })
    }
  })

  formElement.addEventListener('change', (e) => {
    if (e.target.classList.contains(tabulatorInputClass))
      fieldList = validateInput({
        e: e,
        fieldList: fieldList,
        type: fieldList.errors[e.target.name].type,
      })
  })

  formElementSecondary.addEventListener('input', (e) => {
    if (e.target.classList.contains(matrixInputsClass))
      validateInput({ e: e, type: 'matrixCell' })
  })

  d.addEventListener('click', (e) => {
    // PRIMER FORMULARIO
    if (e.target === btnElement) {
      // Si hay errores en el primer formulario, no continuar
      if (Object.values(fieldList.errors).some((el) => el.value)) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete los datos requeridos antes de avanzar',
        })
        return
      }

      validateModal(e, 'tabulator-btn', 'modal-window')

      return generateMatrix({
        fieldList,
        matrixElement,
        matrixRowClass,
        matrixCellClass,
        matrixInputsClass,
      })
    }

    // SEGUNDO FORMULARIO - Enviar datos

    if (e.target === btnSaveElement) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: generateMatrixData,
        successFunctionParams: { fieldList, matrixInputsClass },
      })
    }
  })

  // Funciones
}

function generateCellContent({ row, col, matrixInputsClass }) {
  if (col === 0 && row === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">INICIO</span>`
  if (col === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">GRADO ${row}</span>`
  if (row === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">PASO${col}</span>`
  let inputText = `G${row} - P${col}`
  return `<input
  class="${matrixInputsClass} form-control form-control-sm"
  type="number"
  step="0.01"
  min="0.00"
  placeholder="${inputText}"
  name="g${row}p${col}"
  data-grado="${row}"
  data-paso="${col}"
/>`
}

function generateMatrix({
  fieldList,
  matrixElement,
  matrixCellClass,
  matrixRowClass,
  matrixInputsClass,
}) {
  // Borrar contenido de la matriz
  matrixElement.innerHTML = ''

  const cellsFragment = d.createDocumentFragment()

  let rows = fieldList.grados
  let columns = fieldList.pasos

  matrixElement.style.display = 'grid'
  matrixElement.style.gridTemplateRows = `repeat(${rows + 1}, 1fr)`

  for (let i = 0; i <= rows; i++) {
    const matrixRow = d.createElement('div')
    matrixRow.classList.add(matrixRowClass)

    for (let j = 0; j <= columns; j++) {
      const matrixCell = d.createElement('div')
      matrixCell.classList.add(matrixCellClass)

      matrixCell.innerHTML = generateCellContent({
        row: i,
        col: j,
        matrixInputsClass,
      })
      matrixRow.appendChild(matrixCell)
    }
    cellsFragment.appendChild(matrixRow)
  }

  matrixElement.appendChild(cellsFragment)
}

function generateMatrixData({ fieldList, matrixInputsClass }) {
  const inputsElements = d.querySelectorAll(`.${matrixInputsClass}`)
  if (
    [...inputsElements].some(
      (el) =>
        el.value <= 0 ||
        el.value === '' ||
        !regularExpressions.FLOAT.test(el.value)
    )
  ) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Complete el tabulario correctamente',
      // successFunction: location.reload(),
    })
    return
  }

  const tabulatorData = [...inputsElements].map((el) => {
    let grado = `G${el.dataset.grado}`
    let paso = `P${el.dataset.paso}`
    let value = Number(el.value)

    return [grado, paso, value]
  })

  fieldList.tabulador = tabulatorData
  delete fieldList.errors

  // Enviar datos
  sendTabulatorData({ tabulatorData: fieldList })
}

export { validateTabulatorForm }

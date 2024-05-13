import { sendTabulatorData } from '../api/tabulator.js'
import {
  confirmNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { isFloat } from '../helpers/regExp.js'
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
      fieldList = validateInput(e, fieldList)
      console.log(fieldList)
    }
  })
  formElementSecondary.addEventListener('input', (e) => {
    if (e.target.classList.contains(matrixInputsClass)) validateCellValue(e)
  })

  formElement.addEventListener('change', (e) => {
    if (e.target.classList.contains(tabulatorInputClass))
      fieldList = validateInput(e, fieldList)
  })

  d.addEventListener('click', (e) => {
    if (e.target === btnElement) {
      // Si hay errores en el primer formulario, no continuar
      if (Object.values(fieldList.errors).some((el) => el.value)) {
        console.log('hola')
        confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete los datos requeridos antes de avanzar',
        })
        return
      }

      validateModal(e, 'tabulator-btn', 'modal-window')

      generateMatrix({
        fieldList,
        matrixElement,
        matrixRowClass,
        matrixCellClass,
        matrixInputsClass,
      })
    }
  })
  btnSaveElement.addEventListener('click', (e) => {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      successFunction: generateMatrixData,
      successFunctionParams: { fieldList, matrixInputsClass },
    })
  })

  // Funciones
}

function validateCellValue(e) {
  console.log(isFloat(e.target.value))
  if (!e.target.checkValidity()) {
    e.target.classList.add('input-error')
  } else {
    e.target.classList.remove('input-error')
  }
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
  required
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

  const tabulatorData = [...inputsElements].map((el) => {
    console.log(el.checkValidity())
    let grado = `G${el.dataset.grado}`
    let paso = `P${el.dataset.paso}`
    let value = Number(el.value)

    return [grado, paso, value]
  })
  if ([...inputsElements].some((el) => !el.checkValidity())) {
    console.log([...inputsElements].some((el) => !el.checkValidity()))
    confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Complete el tabulario correctamente',
      // successFunction: location.reload(),
    })
    return
  }
  fieldList.tabulador = tabulatorData
  delete fieldList.errors

  // Enviar datos
  sendTabulatorData({ tabulatorData: fieldList })
}

export { validateTabulatorForm }

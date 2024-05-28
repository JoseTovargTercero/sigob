import {
  getTabulatorData,
  sendTabulatorData,
  updateTabulatorData,
} from '../api/tabulator.js'
import {
  confirmNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { regularExpressions } from '../helpers/regExp.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

const urlParameters = new URLSearchParams(w.location.search)
const id = urlParameters.get('id')

async function validateTabulatorForm({
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
  fieldListErrors = {},
}) {
  const formElement = d.getElementById(formId)
  const formElementSecondary = d.getElementById(secondaryFormId)
  const matrixElement = d.getElementById(matrixId)
  const btnElement = d.getElementById(btnId)
  const btnSaveElement = d.getElementById(btnSaveId)

  const tabulatorInputElement = d.querySelectorAll(`.${tabulatorInputClass}`)
  const tabulatorInputElementCopy = [...tabulatorInputElement]

  if (id) {
    let tabulatorData = await getTabulatorData(id)

    console.log(tabulatorData)

    tabulatorInputElementCopy.forEach((input) => {
      input.value = tabulatorData[0][input.name]
    })

    fieldList = tabulatorData[0]
    fieldList.tabulador = tabulatorData.map((data) => {
      let tdata = data.tabulador
      return [tdata.grado, tdata.paso, tdata.monto]
    })
    console.log(fieldList, fieldListErrors)
  }

  formElement.addEventListener('submit', (e) => {
    e.preventDefault()
  })
  formElementSecondary.addEventListener('submit', (e) => e.preventDefault())

  // VALIDAR INPUTS

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains(tabulatorInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  })

  formElement.addEventListener('change', (e) => {
    if (e.target.classList.contains(tabulatorInputClass))
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
  })

  formElementSecondary.addEventListener('input', (e) => {
    if (e.target.classList.contains(matrixInputsClass))
      validateInput({ target: e.target, type: 'matrixCell' })
  })

  formElementSecondary.addEventListener('keydown', (e) => {
    moveBetweenInputs({ e, matrixCellClass, rows: fieldList.grados })
  })

  // VALIDAR ACCIONES DEL FORMULARIO

  d.addEventListener('click', (e) => {
    // PRIMER FORMULARIO
    if (e.target === btnElement) {
      // Si hay errores en el primer formulario, no continuar

      tabulatorInputElementCopy.forEach((input) => {
        validateInput({
          fieldList,
          fieldListErrors,
          target: input,
          type: fieldListErrors[input.name].type,
        })
      })

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Complete los datos requeridos antes de avanzar',
        })
        return
      }

      validateModal({
        e: e,
        btnId: 'tabulator-btn',
        modalId: 'modal-secondary-form-tabulator',
      })

      generateMatrix({
        fieldList,
        matrixElement,
        matrixRowClass,
        matrixCellClass,
        matrixInputsClass,
        tabulador: fieldList.tabulador,
      })
      if (id)
        fillCellContent({
          matrixValues: fieldList.tabulador,
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
  let tabulador = fieldList.tabulador || []

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

function generateCellContent({ row, col, matrixInputsClass }) {
  if (col === 0 && row === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">INICIO</span>`
  if (col === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">GRADO ${row}</span>`
  if (row === 0)
    return `<span class="tabulator-matrix-cell tabulator-matrix-span">PASO${col}</span>`

  let inputText = `G${row} - P${col}`

  return `<input
  class="${matrixInputsClass} form-control form-input form-control-sm"
  type="number"
  step="0.01"
  min="0.00"
  placeholder="${inputText}"
  name="g${row}p${col}"
  data-grado="${row}"
  data-paso="${col}"
/>`
}

function fillCellContent({ matrixValues, matrixInputsClass }) {
  const matrixInputsElement = d.querySelectorAll(`.${matrixInputsClass}`)
  const matrixInputsElementCopy = [...matrixInputsElement]

  matrixInputsElementCopy.forEach((input, i) => {
    if (
      input.dataset.grado === matrixValues[i][0].charAt(1) &&
      input.dataset.paso === matrixValues[i][1].charAt(1)
    ) {
      input.value = matrixValues[i][2]
    }
  })
}

function generateMatrixData({ fieldList, matrixInputsClass }) {
  const matrixInputsElement = d.querySelectorAll(`.${matrixInputsClass}`)
  const matrixInputsElementCopy = [...matrixInputsElement]
  if (
    matrixInputsElementCopy.some(
      (el) =>
        el.value <= 0 ||
        el.value === '' ||
        !regularExpressions.FLOAT.test(el.value)
    )
  ) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Complete el tabulario correctamente',
    })

    matrixInputsElementCopy.forEach((input) => {
      validateInput({ target: input, type: 'matrixCell' })
    })

    return
  }

  const tabulatorData = matrixInputsElementCopy.map((el) => {
    let grado = `G${el.dataset.grado}`
    let paso = `P${el.dataset.paso}`
    let value = Number(el.value)

    return [grado, paso, value]
  })

  fieldList.tabulador = tabulatorData

  if (id) return updateTabulatorData({ tabulatorData: fieldList })

  // Enviar datos
  sendTabulatorData({ tabulatorData: fieldList })
}

function moveBetweenInputs({ e, matrixCellClass, rows }) {
  const matrix = document.getElementsByClassName('tabulator-matrix-cell-input')
  let currentIndex = Array.prototype.indexOf.call(
    matrix,
    document.activeElement
  )

  if (e.ctrlKey && e.keyCode >= 37 && e.keyCode <= 40) {
    e.preventDefault()
    switch (e.keyCode) {
      case 37: // Flecha izquierda
        currentIndex = (currentIndex - 1 + matrix.length) % matrix.length
        break
      case 38: // Flecha arriba
        currentIndex = (currentIndex - rows + matrix.length) % matrix.length
        break
      case 39: // Flecha derecha
        currentIndex = (currentIndex + 1) % matrix.length
        break
      case 40: // Flecha abajo
        // Código para la flecha abajo: Ajusta la lógica según la estructura de tu matriz

        currentIndex = (currentIndex + rows) % matrix.length
        break
    }

    matrix[currentIndex].focus()
  }
}

export { validateTabulatorForm }

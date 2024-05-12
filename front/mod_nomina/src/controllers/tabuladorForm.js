import { sendTabulatorData } from '../api/tabulator.js'
import { validateInput } from '../helpers/helpers.js'

const d = document
const w = window

function validateTabulatorForm({
  formId,
  tabulatorInputClass,
  matrixId,
  matrixRowClass,
  matrixCellClass,
  matrixInputsClass,
  btnId,
  btnSaveId,
  btnCloseId,
  modalClass,
  fieldList = {},
}) {
  const formElement = d.getElementById(formId)
  const matrixElement = d.getElementById(matrixId)
  const btnElement = d.getElementById(btnId)
  const btnSaveElement = d.getElementById(btnSaveId)

  formElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener('input', (e) => {
    if (e.target.classList.contains(tabulatorInputClass))
      fieldList = validateInput(fieldList, e)
  })
  formElement.addEventListener('change', (e) => {
    if (e.target.classList.contains(tabulatorInputClass))
      fieldList = validateInput(fieldList, e)
  })

  btnElement.addEventListener('click', () => {
    generateMatrix({
      fieldList,
      matrixElement,
      matrixRowClass,
      matrixCellClass,
      matrixInputsClass,
    })
  })
  btnSaveElement.addEventListener('click', (e) => {
    generateMatrixData({ fieldList, matrixInputsClass })
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
  type="text"
  placeholder="${inputText}"
  name=""
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
  // matrixElement.style.gridTemplateColumns = `repeat(${columns + 1}, 1fr)`
  matrixElement.style.gridTemplateRows = `repeat(${rows + 1}, 1fr)`

  for (let i = 0; i <= rows; i++) {
    const matrixRow = d.createElement('div')
    matrixRow.classList.add(matrixRowClass)

    // cellsFragment.appendChild(matrixRow)

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
  console.log(matrixInputsClass)
  const inputsElements = d.querySelectorAll(`.${matrixInputsClass}`)
  console.log(inputsElements)

  const tabulatorData = [...inputsElements].map((el) => {
    let grado = `G${el.dataset.grado}`
    let paso = `P${el.dataset.paso}`
    let value = Number(el.value)
    return [grado, paso, value]
  })
  fieldList.tabulador = tabulatorData

  // Enviar datos
  console.log(fieldList)
  sendTabulatorData({ tabulatorData: fieldList })
}

export { validateTabulatorForm }

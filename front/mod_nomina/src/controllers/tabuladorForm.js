import { sendTabulatorData } from '../api/tabulator.js'
import { validateInput } from '../helpers/helpers.js'

const d = document
const w = window

function validateTabulatorForm({
  formId,
  tabulatorInputClass,
  matrixId,
  matrixInputsClass,
  btnId,
  btnSaveId,
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
    // if (e.target.classList.contains(tabulatorInputClass))
    //   fieldList = validateInput(fieldList, e)
  })

  btnElement.addEventListener('click', () => {
    generateMatrix({ fieldList, matrixElement })
  })
  btnSaveElement.addEventListener('click', () => {
    generateMatrixData({ fieldList, matrixInputsClass })
  })

  // Funciones
}

function generateCellContent({ row, col }) {
  if (col === 0 && row === 0) return ''
  if (col === 0) return `GRADO ${row}`
  if (row === 0) return `PASO ${col}`
  let inputText = `G${row} - P${col}`
  return `<input
  class="tabulator-matrix-cell"
  type="text"
  placeholder="... ${inputText}"
  name=""
  data-grado="${row}"
  data-paso="${col}"
/>`
}

function generateMatrix({ fieldList, matrixElement }) {
  // Borrar contenido de la matriz
  matrixElement.innerHTML = ''

  const cellsFragment = d.createDocumentFragment()
  const tableElement = d.createElement('table')

  let rows = fieldList.grados
  let colums = fieldList.pasos

  //   Generar celdas de la matriz
  for (let i = 0; i <= rows; i++) {
    // Crear Filas
    const tr = d.createElement('tr')
    cellsFragment.appendChild(tr)

    // Crear número de columna (pasos)
    for (let j = 0; j <= colums; j++) {
      //   Crear culumnas
      const td = d.createElement('td')

      td.innerHTML = generateCellContent({ row: i, col: j })
      cellsFragment.appendChild(td)
    }
  }

  tableElement.appendChild(cellsFragment)
  matrixElement.appendChild(tableElement)
}

function generateMatrixData({ fieldList, matrixInputsClass }) {
  const inputsElements = d.querySelectorAll(`.${matrixInputsClass}`)

  const tabulatorData = [...inputsElements].map((el) => {
    let grado = `G${el.dataset.grado}`
    let paso = `P${el.dataset.paso}`
    let value = Number(el.value)
    return [grado, paso, value]
  })
  fieldList.tabulador = tabulatorData

  // Enviar datos
  sendTabulatorData({ tabulatorData: fieldList })
}

export { validateTabulatorForm }

import { getTabulatorData } from '../api/tabulator.js'

const d = document

export async function tabulatorCard({ id, elementToInsert }) {
  const container = document.getElementById(elementToInsert)
  let tabulatorData = await getTabulatorData(id)
  let fieldList = tabulatorData[0]
  fieldList.tabulador = tabulatorData.map((data) => {
    let tdata = data.tabulador
    return [tdata.grado, tdata.paso, tdata.monto]
  })
  let matrix = generateMatrix({
    fieldList: tabulatorData[0],
    matrixCellClass: 'tabulator-matrix-cell',
    matrixRowClass: 'tabulator-matrix-row',
    matrixInputsClass: 'tabulator-matrix-cell-input',
  })

  let tabulatorCardElement = `
    <div class='modal-window slide-up-animation' id='modal-tabulator'>
      <div class='modal-box card w-90 overflow-auto'>
        <div class='row'>
          <div class='modal-box-header'>
            <h2 class='card-title'>${fieldList.nombre}</h2>
            <button
              id='btn-close-tabulator-card'
              type='button'
              class='btn btn-danger'
              aria-label='Close'
            >
              &times;
            </button>
          </div>
        </div>
        <div class="modal-box-content" id="tabulator-matrix">
        </div>
    </div>
  `

  container.insertAdjacentHTML('beforeend', tabulatorCardElement)

  d.getElementById('tabulator-matrix').appendChild(matrix)

  fillCellContent({
    matrixInputsClass: `tabulator-matrix-cell-input`,
    matrixValues: fieldList.tabulador,
  })
  return
}

function generateMatrix({
  fieldList,
  matrixCellClass,
  matrixRowClass,
  matrixInputsClass,
}) {
  // Borrar contenido de la matriz
  const matrixElement = d.createElement('div')
  matrixElement.innerHTML = ''

  const cellsFragment = d.createDocumentFragment()

  let rows = fieldList.grados
  let columns = fieldList.pasos
  let tabulador = fieldList.tabulador || []

  matrixElement.style.display = 'grid'
  matrixElement.style.gridTemplateRows = `repeat(${Number(rows) + 1}, 1fr)`
  matrixElement.classList.add('tabulator-matrix')

  for (let i = 0; i <= rows; i++) {
    const matrixRow = d.createElement('div')
    matrixRow.classList.add(matrixRowClass)

    for (let j = 0; j <= columns; j++) {
      const matrixCell = d.createElement('div')
      matrixCell.classList.add(matrixCellClass)
      if (fieldList.tabulador.length === 0) {
        matrixCell.innerHTML = generateCellContent({
          row: i,
          col: j,
          matrixInputsClass,
        })
      } else {
        matrixCell.innerHTML = generateCellContent({
          row: i,
          col: j,
          matrixInputsClass,
          monto: fieldList.tabulador,
        })
      }

      matrixRow.appendChild(matrixCell)
    }
    cellsFragment.appendChild(matrixRow)
  }

  matrixElement.appendChild(cellsFragment)

  return matrixElement
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
    disabled
  />`
}

function fillCellContent({ matrixValues, matrixInputsClass }) {
  const matrixInputsElement = d.querySelectorAll(`.${matrixInputsClass}`)
  const matrixInputsElementCopy = [...matrixInputsElement]
  // console.log(matrixInputsElementCopy)

  matrixInputsElementCopy.forEach((input, i) => {
    // console.log('e2')
    if (
      input.dataset.grado === matrixValues[i][0].charAt(1) &&
      input.dataset.paso === matrixValues[i][1].charAt(1)
    ) {
      // console.log('e')
      input.value = matrixValues[i][2]
    }
  })
}

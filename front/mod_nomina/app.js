import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateModal } from './src/helpers/helpers.js'
const d = document

d.addEventListener('DOMContentLoaded', (e) => {
  validateTabulatorForm({
    formId: 'tabulator-primary-form',
    tabulatorInputClass: 'tabulator-input',
    matrixId: 'tabulator-matrix',
    matrixRowClass: 'tabulator-matrix-row',
    matrixCellClass: 'tabulator-matrix-cell',
    matrixInputsClass: 'tabulator-matrix-cell-input',
    btnId: 'tabulator-btn',
    btnSaveId: 'tabulator-save-btn',
    btnCloseId: 'btn-close',
    modalClass: 'modal-window',
    fieldList: {
      nombre: '',
      pasos: 0,
      grados: 0,
      aniosPasos: 0,
      tabulador: [],
    },
  })
})

d.addEventListener('click', (e) => {
  validateModal(e, {
    btnOpenId: 'tabulator-btn',
    btnCloseId: 'btn-close',
    modalClass: 'modal-window',
  })
})

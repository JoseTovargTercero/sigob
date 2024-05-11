import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
const d = document

d.addEventListener('DOMContentLoaded', (e) => {
  validateTabulatorForm({
    formId: 'tabulator-primary-form',
    tabulatorInputClass: 'tabulator-input',
    matrixId: 'tabulator-matrix',
    matrixInputsClass: 'tabulator-matrix-cell',
    btnId: 'tabulator-btn',
    btnSaveId: 'tabulator-save-btn',
    fieldList: {
      nombre: '',
      pasos: 0,
      grados: 0,
      aniosPasos: 0,
      tabulador: [],
    },
  })
})

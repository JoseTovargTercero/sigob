import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateModal } from './src/helpers/helpers.js'
const d = document

d.addEventListener('DOMContentLoaded', (e) => {
  validateTabulatorForm({
    formId: 'tabulator-primary-form',
    secondaryFormId: 'tabulator-secundary-form',
    tabulatorInputClass: 'tabulator-input',
    matrixId: 'tabulator-matrix',
    matrixRowClass: 'tabulator-matrix-row',
    matrixCellClass: 'tabulator-matrix-cell',
    matrixInputsClass: 'tabulator-matrix-cell-input',
    btnId: 'tabulator-btn',
    btnSaveId: 'tabulator-save-btn',
    fieldList: {
      nombre: '',
      pasos: 0,
      grados: 0,
      aniosPasos: 0,
      tabulador: [],
      errors: {
        nombre: {
          value: true,
          message: 'Introducir un nombre válido',
          type: 'text',
        },
        pasos: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
        grados: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
        aniosPasos: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
      },
    },
  })
})

d.addEventListener('click', (e) => {
  validateModal(e, 'btn-close', 'modal-window')
})

import { validateEmployeeForm } from './src/controllers/employeeForm.js'
import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateModal } from './src/helpers/helpers.js'
const d = document

d.addEventListener('DOMContentLoaded', (e) => {
  const tabulatorForm = d.getElementById('tabulator-primary-form')
  const employeeForm = d.getElementById('employee-form')

  if (tabulatorForm) {
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
  }

  if (employeeForm) {
    validateEmployeeForm({
      formElement: employeeForm,
      employeeInputClass: 'employee-input',
      btnId: 'tabulator-btn',
      selectSerach: 'select-search',
      fieldList: {
        nombre: '',
        nacionalidad: '',
        identificacion: 0,
        status: '',
        instruccion_academica: '',
        cod_cargo: '',
        fecha_ingreso: '',
        otros_anios: '',
        hijos: 0,
        discapacidad: '',
        banco: '',
        cuenta: 0,
        becas: '',
        errors: {
          nombre: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          nacionalidad: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          identificacion: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'number',
          },
          status: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          instruccion_academica: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          cod_cargo: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'number',
          },
          fecha_ingreso: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          discapacidad: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          banco: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          cuenta: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
        },
      },
    })
  }
})

d.addEventListener('click', (e) => {
  validateModal(e, 'btn-close', 'modal-window')
})

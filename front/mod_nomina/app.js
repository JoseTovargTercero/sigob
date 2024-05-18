import { validateEmployeeForm } from './src/controllers/empleadosForm.js'
import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateModal } from './src/helpers/helpers.js'
import {
  confirmDeleteEmployee,
  loadTable,
} from './src/controllers/empleadosTable.js'
const d = document

const tabulatorForm = d.getElementById('tabulator-primary-form')
const employeeForm = d.getElementById('employee-form')

const employeeTableElement = d.getElementById('employee-table')
d.addEventListener('DOMContentLoaded', (e) => {
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
      selectSearchInput: 'select-search-input',
      selectSearch: ['cargo'],
      btnAddId: 'add-dependency',
      fieldList: {
        nombre: '',
        nacionalidad: '',
        cedula: 0,
        status: 'ACTIVO',
        instruccion_academica: '',
        cod_cargo: '',
        fecha_ingreso: '',
        otros_años: '',
        hijos: 0,
        discapacidades: '',
        banco: '',
        cuenta_bancaria: 0,
        tipo_cuenta: '',
        id_dependencia: '',
        tipo_nomina: 'a',
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
          cedula: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'number',
          },
          status: {
            value: false,
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
            message: 'Fecha inválida o mayor',
            type: 'date',
          },
          otros_años: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'date',
          },
          hijos: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'number',
          },
          discapacidades: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          banco: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          cuenta_bancaria: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'number',
          },
          tipo_cuenta: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          id_dependencia: {
            value: true,
            message: 'Introducir un campo válido',
            type: 'text',
          },
          dependencia: {
            value: false,
            message: 'Introducir un campo válido',
            type: 'text',
          },
        },
      },
    })
  }

  if (employeeTableElement) {
    loadTable()
  }
})

d.addEventListener('click', (e) => {
  if (e.target.id === 'btn-close')
    validateModal({
      e: e,
      btnId: e.target.id,
      modalId: 'modal-secondary-form-tabulator',
    })

  if (e.target.id === 'btn-close-dependency')
    validateModal({ e: e, btnId: e.target.id, modalId: 'modal-dependency' })

  if (employeeTableElement) {
    if (e.target.classList.contains('btn-delete')) {
      confirmDeleteEmployee({ e: e, id: e.target.dataset.empleadoId })
    }
  }
})

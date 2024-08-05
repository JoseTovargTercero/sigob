import { validateDependenciaForm } from './src/controllers/dependenciasForm.js'
import { loadDependenciaTable } from './src/controllers/dependenciasTable.js'
import { validateEmployeeForm } from './src/controllers/empleadosForm.js'
import { validateEmployeeTable } from './src/controllers/empleadosTable.js'
import { validateRequestForm } from './src/controllers/peticionesNominaForm.js'
// import { validatePayNomForm } from './src/controllers/pagarNominaForm.js'
// import { validateEmployeePayForm } from './src/controllers/peticionesNominaForm.js'
// import { validateRequestForm } from './src/controllers/peticionesNominaForm2.js'
import { validateRequestNomForm } from './src/controllers/peticionesNominaReview.js'
import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateModal } from './src/helpers/helpers.js'
const d = document

// const requestForm2 = d.getElementById('request-form2')
const payNomForm = d.getElementById('pay-nom-form')

const employeeTableElement = d.getElementById('employee-table')
const tabulatorTableElement = d.getElementById('tabulator-table')
d.addEventListener('DOMContentLoaded', (e) => {
  const tabulatorForm = d.getElementById('tabulator-primary-form')
  const employeeForm = d.getElementById('employee-form')
  const requestNomForm = d.getElementById('request-nom-form')
  const requestForm = d.getElementById('request-form')
  const dependenciaTable = d.getElementById('dependencia-table')

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
        nombre: 'aaaaaaaaaa',
        pasos: 0,
        grados: 0,
        aniosPasos: 0,
        tabulador: [],
      },
      fieldListErrors: {
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
    })
  }

  if (employeeForm) {
    validateEmployeeTable()
    validateEmployeeForm({
      formElement: employeeForm,
      employeeInputClass: 'employee-input',
      employeeSelectClass: 'employee-select',
      btnId: 'btn-employee-save',
      selectSearchInput: 'select-search-input',
      selectSearch: ['cargo'],
      btnAddId: 'add-dependency',
      fieldList: {
        nombres: '',
        nacionalidad: '',
        cedula: 0,
        status: '',
        instruccion_academica: '',
        cod_cargo: '',
        fecha_ingreso: '',
        otros_años: 0,
        hijos: 0,
        beca: 0,
        discapacidades: '',
        banco: '',
        cuenta_bancaria: '',
        // tipo_cuenta: 0,
        id_dependencia: '',
        dependencia: '',
        tipo_nomina: 0,
        cod_empleado: '441151',
        correcion: 0,
        observacion: '',
      },
      fieldListErrors: {
        nombres: {
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
          message: 'Introduzca cédula válida',
          type: 'cedula',
        },
        status: {
          value: false,
          message: 'Elija una opción',
          type: 'text',
        },
        instruccion_academica: {
          value: true,
          message: 'Elija una opción',
          type: 'text',
        },
        cod_cargo: {
          value: true,
          message: 'Elija un cargo',
          type: 'number',
        },
        fecha_ingreso: {
          value: true,
          message: 'Fecha inválida o mayor',
          type: 'date',
        },
        otros_años: {
          value: true,
          message: 'Introducir cantidad o "0"',
          type: 'number2',
        },
        hijos: {
          value: true,
          message: 'Introducir cantidad o "0"',
          type: 'number2',
        },
        beca: {
          value: true,
          message: 'Introducir cantidad o "0"',
          type: 'number2',
        },
        banco: {
          value: true,
          message: 'Elija un banco',
          type: 'text',
        },
        cuenta_bancaria: {
          value: true,
          message: 'Introducir N° de cuenta válido',
          type: 'cuenta_bancaria',
        },
        discapacidades: {
          value: true,
          message: 'Elija una opción',
          type: 'number2',
        },
        // tipo_cuenta: {
        //   value: true,
        //   message: 'Elegir tipo de cuenta',
        //   type: 'number2',
        // },
        id_dependencia: {
          value: true,
          message: 'Elejir una dependencia',
          type: 'number',
        },
        dependencia: {
          value: null,
          message: 'No puede estar vacío',
          type: 'text',
        },
        tipo_nomina: {
          value: null,
          message: 'Introducir un campo válido',
          type: 'number2',
        },
        observacion: {
          value: null,
          message: 'Introducir un campo válido',
          type: 'text',
        },
      },
    })
    return
  }

  if (dependenciaTable) {
    loadDependenciaTable()
    validateDependenciaForm({
      formId: 'dependencia-form',
      formContainerId: 'dependencia-form-container',
      btnNewId: 'dependencia-nueva',
      btnSaveId: 'dependencia-guardar',
      fieldList: {
        dependencia: '',
        cod_dependencia: '',
      },
      fieldListErrors: {
        dependencia: {
          value: true,
          message: 'Campo inválido',
          type: 'text',
        },
        cod_dependencia: {
          value: true,
          message: 'Campo inválido',
          type: 'number',
        },
      },
    })
  }

  // if (requestForm) {
  //   validateEmployeePayForm({
  //     selectIdNomina: 'nomina',
  //     selectIdGrupo: 'grupo',
  //     selectIdFrecuencia: 'frecuencia',
  //     requestSelectContainerId: 'request-employee-container',
  //     showRequestGroupBtnId: 'show-request-group',
  //     formId: 'request-form',
  //   })
  // }

  if (requestForm) {
    validateRequestForm({
      btnNewRequestId: 'btn-new-request',
      requestTableId: 'request-table',
      requestFormId: 'request-form',
      requeFormInformationId: 'request-form-information',
      newRequestFormId: 'form-request-id',
      selectGrupoId: 'grupo',
      selectNominaId: 'nomina',
      selectFrecuenciaId: 'frecuencia',
      requestEmployeeListId: 'request-employee-list',
      btnNextId: 'btn-next',
      btnPreviusId: 'btn-previus',
    })
  }

  if (requestNomForm) {
    validateRequestNomForm({
      selectId: 'select-nomina',
      consultBtnId: 'consultar-nomina',
      formId: 'request-nom-form',
    })
  }

  // if (payNomForm) {
  //   validatePayNomForm({
  //     selectId: 'select-correlativo',
  //     consultBtnId: 'consultar-correlativo',
  //     formId: 'pay-nom-form',
  //   })
  // }

  // if (employeeTableElement) {
  //   loadTable()
  // }

  // if (tabulatorTableElement) {
  //   loadTabulatorTable()
  // }
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
})

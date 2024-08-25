import { getEmployeeData, updateEmployeeStatus } from '../api/empleados.js'
import { getPeticionMovimientos } from '../api/movimientos.js'
import {
  calculoNomina,
  descargarNominaTxt,
  enviarCalculoNomina,
  getComparacionNomina,
  getComparacionNomina2,
  getNominas,
  getPeticionesNomina,
  getPeticionNomina,
  getSemanasDelAnio,
} from '../api/peticionesNomina.js'
import { nomCorregirCard } from '../components/nom_corregir_card.js'
import {
  loadEmployeeList,
  nom_empleados_list_card,
} from '../components/nom_empleados_list_card.js'
import { nomReportCard } from '../components/nom_report_card.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { nom_comparation_employee } from '../components/regcon_comparation_employee.js'
import {
  closeModal,
  confirmNotification,
  validateInput,
} from '../helpers/helpers.js'
import { FRECUENCY_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { createTable, employeePayTableHTML } from './peticionesNominaTable.js'
import { loadRequestTable } from './peticionesTable.js'

const d = document

let fieldList = {
  nomina: '',
  grupo: '',
  frecuencia: '',
  identificador: '',
  tipo: '',
}

let fieldListErrors = {
  grupo: {
    value: true,
    message: 'Seleccione un grupo de nómina',
    type: 'number',
  },
  nomina: {
    value: true,
    message: 'Seleccione una nómina',
    type: 'text',
  },
  identificador: {
    value: true,
    message: 'Seleccione frecuencia a pagar',
    type: 'text',
  },
}

let employeeNewStatus = []

let nominas

let formFocus = 1

let calculoInformacion

export async function validateRequestForm({
  btnNewRequestId,
  requestFormId,
  requestTableId,
  newRequestFormId,
  selectNominaId,
  selectGrupoId,
  selectFrecuenciaId,
  btnNextId,
  btnPreviusId,
}) {
  loadRequestTable()
  let requestForm = d.getElementById(requestFormId)
  let requestTableRevision = d.getElementById(
    'request-table-revision-container'
  )
  let requestTableConfirmado = d.getElementById(
    'request-table-confirmado-container'
  )
  let requestTableOptions = d.getElementById('request-table-options')

  let newRequestForm = d.getElementById(newRequestFormId)
  let requestFormInformation = d.getElementById('request-form-information')
  let requestFormInformationBody = d.getElementById(
    'request-form-information-body'
  )

  let selectNomina = d.getElementById(selectNominaId)
  let selectGrupo = d.getElementById(selectGrupoId)
  let selectFrecuencia = d.getElementById(selectFrecuenciaId)

  let selectMes = d.getElementById('mes')
  let date = new Date()
  let meses = await getSemanasDelAnio()

  selectMes.value = date.getMonth() + 1
  selectMes.options[selectMes.selectedIndex].scrollIntoView()

  let btnNext = d.getElementById(btnNextId)
  let btnPrevius = d.getElementById(btnPreviusId)
  let requestStepPart1 = d.getElementById('request-step-1')
  let requestStepPart2 = d.getElementById('request-step-2')
  let requestStepPart3 = d.getElementById('request-step-3')

  requestForm.addEventListener('submit', (e) => e.preventDefault())

  d.addEventListener('change', async (e) => {
    if (e.target.dataset.employeeid) {
      let id = e.target.dataset.employeeid
      let defaultValue = e.target.dataset.defaultvalue
      let cedula = e.target.dataset.cedula
      let nombres = e.target.dataset.nombres

      let oldValueIndex = employeeNewStatus.findIndex((el) => el.id === id)
      if (e.target.value === defaultValue) {
        employeeNewStatus = employeeNewStatus.filter((el) => el.id !== id)
        console.log(employeeNewStatus)
        d.getElementById('btn-confirm-list').setAttribute('disabled', 'true')
        d.getElementById('btn-confirm-list').textContent = 'Sin modificaciones'
        return
      }

      if (employeeNewStatus.some((el) => el.id === id)) {
        employeeNewStatus.splice(oldValueIndex, 1, {
          id,
          value: e.target.value,
          cedula,
          nombres,
          defaultValue,
        })
      } else {
        employeeNewStatus.push({
          id,
          value: e.target.value,
          cedula,
          nombres,
          defaultValue,
        })
      }

      console.log(employeeNewStatus.length)

      if (employeeNewStatus.length > 0) {
        d.getElementById('btn-confirm-list').removeAttribute('disabled')
        d.getElementById('btn-confirm-list').textContent = 'Confirmar'
      }
      console.log(employeeNewStatus)
    }

    if (e.target === selectGrupo) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })

      nominas = await getNominas(e.target.value)

      selectNomina.innerHTML = ''

      if (nominas.length > 0)
        nominas.forEach((nomina) => {
          let option = `<option value="${nomina.nombre}">${
            nomina.nombre || 'Grupo de nómina vacío'
          }</option>`

          selectNomina.insertAdjacentHTML('beforeend', option)
        })
      else
        selectNomina.insertAdjacentHTML(
          'beforeend',
          `<option value="">Grupo de nómina vacío</option>`
        )
    }

    if (e.target === selectNomina) {
      if (!e.target.value) return

      selectMes.parentElement.classList.add('hide')
      let nominaFind = nominas.find(
        (nomina) => nomina.nombre === e.target.value
      )
      fieldList.nomina = e.target.value

      console.log(nominas)
      fieldList.frecuencia = nominaFind.frecuencia
      fieldList.tipo = nominaFind.tipo
      fieldList.concepto_valor_max = nominaFind.concepto_valor_max

      console.log(fieldList)

      selectFrecuencia.innerHTML = ''

      let identificadorOpciones = ''

      switch (fieldList.frecuencia) {
        case '1':
          selectMes.parentElement.classList.remove('hide')
          selectMes.value = date.getMonth() + 1

          meses[date.getMonth() + 1].forEach((identificadorNomina, index) => {
            identificadorOpciones += `<option value='s${identificadorNomina}'>Semana ${identificadorNomina}</option>`
          })
          break
        case '2':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina, index) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Quincena ${
                index + 1
              }</option>`
            }
          )
          break
        case '3':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break
        case '4':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break
        case '5':
          console.log('hola')
          for (let i = 1; i <= fieldList.concepto_valor_max; i++) {
            identificadorOpciones += `<option value='p${i}'>Periodo ${i}</option>`
          }
          break

        default:
          break
      }

      selectFrecuencia.insertAdjacentHTML('beforeend', identificadorOpciones)
    }

    if (e.target === selectMes) {
      let identificadorOpciones = ''
      selectFrecuencia.innerHTML = ''

      meses[e.target.value].forEach((identificadorNomina, index) => {
        identificadorOpciones += `<option value='s${identificadorNomina}'>Semana ${identificadorNomina}</option>`
      })

      selectFrecuencia.insertAdjacentHTML('beforeend', identificadorOpciones)
    }

    if (e.target === selectFrecuencia) {
      if (!fieldList.nomina)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija una nomina.',
        })

      if (!fieldList.grupo)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija un grupo de nomina.',
        })

      // Mostrar contenedor de información

      fieldList.identificador = e.target.value

      let result = await confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: async function () {
          console.log('CONFIRMADOOO')

          requestFormInformation.classList.remove('hide')

          let employeePayTableCard = d.getElementById(
            'request-employee-table-card'
          )
          let movimientosTable = d.getElementById('movimientos-table')
          let employeeComparationCard = d.getElementById(
            'nom-comparation-employee'
          )
          let card = d.getElementById('employee-new-status-card')
          let requestComparationContainer = d.getElementById(
            'request-comparation-container'
          )
          if (employeePayTableCard) employeePayTableCard.remove()
          if (movimientosTable) movimientosTable.remove()
          if (employeeComparationCard) employeeComparationCard.remove()
          if (card) card.remove()
          if (requestComparationContainer) requestComparationContainer.remove()

          calculoInformacion = await calculoNomina({
            nombre: fieldList.nomina,
            frecuencia: fieldList.frecuencia,
            identificador: fieldList.identificador,
            tipo: fieldList.tipo,
            concepto_valor_max: fieldList.concepto_valor_max,
          })
          console.log(calculoInformacion)

          let nominaMapped = { ...calculoInformacion }

          nominaMapped.informacion_empleados =
            nominaMapped.informacion_empleados.map((el) => {
              delete el.aportes
              delete el.deducciones
              delete el.asignaciones

              return el
            })

          console.log(nominaMapped)
          let columns = Object.keys(nominaMapped.informacion_empleados[0])

          // Insertar tabla en formulario

          employeePayTableHTML({
            nominaData: nominaMapped,
            columns,
            elementToInsert: 'request-form-information-body',
          })
          // requestFormInformationBody.insertAdjacentHTML('beforeend')
          // createTable({ nominaData: nominaMapped, columns })

          toast_s('success', 'Se ha realizado el cálculo')

          d.getElementById('btn-next').click()
        },
        message: `Está seguro de realizar el cálculo de la nomina ${fieldList.nomina.toLocaleUpperCase()} con frecuencia ${validarIdentificador(
          fieldList.identificador.toLowerCase()
        )}`,
      })

      if (!result) {
        selectFrecuencia.value = ''
        fieldList.identificador = ''
      }
    }
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'btn-show-request') {
      e.preventDefault()
      let peticiones = await getPeticionesNomina()
      let peticion = peticiones.find(
        (el) => el.correlativo === e.target.dataset.correlativo
      )

      fieldList.frecuencia = peticion.frecuencia
      let reportCard = d.getElementById('modal-report')
      if (reportCard) reportCard.remove()

      nomReportCard({ data: peticion, elementToInsert: 'request-form' })
    }

    if (e.target.dataset.revisar) {
      console.log('hola')
      let movimientosPeticion = await getPeticionMovimientos({
        id_peticion: Number(e.target.dataset.revisar),
      })
      let peticion = await getPeticionNomina(e.target.dataset.revisar)

      console.log(movimientosPeticion)
      console.log(peticion)
      nomCorregirCard({
        elementToInsertId: 'request-form',
        peticionInfo: peticion,
        peticionCorrecciones: movimientosPeticion,
      })
    }

    if (e.target.dataset.close === 'btn-close-report') {
      let reportCard = d.getElementById('modal-report')
      reportCard.remove()
    }

    if (e.target.dataset.correlativotxt) {
      console.log(e.target.dataset)
      descargarNominaTxt({
        identificador: e.target.dataset.identificador,
        correlativo: e.target.dataset.correlativotxt,
      }).then((res) => {
        loadRequestTable()
        d.getElementById('modal-report').remove()
      })
    }

    if (e.target.id === btnNewRequestId) {
      if (e.target.classList.contains('active')) {
        e.target.classList.remove('active')

        e.target.textContent = 'Nueva petición'

        requestTableRevision.classList.add('d-block')
        requestTableConfirmado.classList.add('d-none')
        requestTableRevision.classList.remove('d-none')
        requestTableConfirmado.classList.remove('d-block')
        requestTableOptions.classList.remove('hide')

        newRequestForm.classList.add('hide')
        requestFormInformation.classList.add('hide')

        // location.reload()
        resetForm()
      } else {
        e.target.classList.add('active')

        e.target.textContent = 'Cancelar petición'

        requestTableRevision.classList.add('d-none')
        requestTableConfirmado.classList.add('d-none')
        requestTableRevision.classList.remove('d-block')
        requestTableConfirmado.classList.remove('d-block')
        requestTableOptions.classList.add('hide')

        newRequestForm.classList.remove('hide')
        // requestFormInformation.classList.remove('hide')
      }
    }

    if (e.target.id === 'btn-close-employee-list-card') {
      closeModal({ modalId: 'modal-employee-list' })
    }

    if (e.target.id === 'btn-confirm-list') {
      confirmNotification({
        message:
          'Esto guardará los cambios permanentes de los empleados seleccionados y se re-calculará la nomina nuevamente ¿Desea seguir?',
        type: NOTIFICATIONS_TYPES.send,
        successFunction: async () => {
          let res = await updateEmployeeStatus({ data: employeeNewStatus })

          if (res.status === 'success') {
            toast_s('success', 'Se han actualizado los empleados')

            let card = d.getElementById('employee-new-status-card')

            if (card) {
              card.remove()
              toast_s('success', 'Se ha actualizado la tabla')
            } else {
              toast_s('success', 'Tabla con nuevos status añadida')
            }

            requestFormInformationBody.insertAdjacentHTML(
              'afterbegin',
              empleadosModificados(employeeNewStatus)
            )
            closeModal({ modalId: 'modal-employee-list' })
          } else {
            toast_s(
              'error',
              'Error al status de empleado. No se realizará el cálculo'
            )
            closeModal({ modalId: 'modal-employee-list' })
            return
          }

          // REALIZAR CÁLCULO LUEGO DE ACTUALIZAR EMPLEADO
          console.log('CONFIRMADOOO')

          let employeePayTableCard = d.getElementById(
            'request-employee-table-card'
          )
          if (employeePayTableCard) employeePayTableCard.remove()

          calculoInformacion = await calculoNomina({
            nombre: fieldList.nomina,
            identificador: fieldList.identificador,
            frecuencia: fieldList.frecuencia,
            tipo: fieldList.tipo,
            concepto_valor_max: fieldList.concepto_valor_max,
          })

          let nominaMapped = { ...calculoInformacion }

          nominaMapped.informacion_empleados =
            nominaMapped.informacion_empleados.map((el) => {
              delete el.aportes
              delete el.deducciones
              delete el.asignaciones

              return el
            })

          console.log(nominaMapped)
          let columns = Object.keys(nominaMapped.informacion_empleados[0])

          // Insertar tabla en formulario
          employeePayTableHTML({
            nominaData: nominaMapped,
            columns,
            elementToInsert: 'request-form-information-body',
          })

          toast_s('success', 'Se ha realizado el cálculo')
        },
      })

      // confirmNotification({
      //   type: NOTIFICATIONS_TYPES.send,
      //   successFunction: async function () {
      //     let res = await updateEmployeeStatus({ data: employeeNewStatus })

      //     if (res.status === 'success') {
      //       toast_s('success', 'Se han actualizado los empleados')
      //     }

      //     requestFormInformationBody.insertAdjacentHTML(
      //       'afterbegin',
      //       empleadosModificados(employeeNewStatus)
      //     )
      //   },
      // })
    }

    if (e.target.id === 'btn-send-request') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: '¿Deseas cancelar esta petición?',
        successFunction: enviarCalculoNomina,
        successFunctionParams: calculoInformacion,
        othersFunctions: [
          function () {
            location.reload()
          },
        ],
      })
    }
    if (e.target.id === 'btn-next') {
      scroll(0, 0)

      if (formFocus === 1) {
        if (Object.values(fieldList).some((el) => !el)) {
          toast_s(
            'error',
            'Advertencia: Seleccione todos los campos para realizar el cálculo'
          )
          return
        }

        btnPrevius.textContent = 'Cancelar petición'
        requestStepPart1.classList.add('hide')
        requestStepPart2.classList.remove('hide')

        // Deshabilitar boton
        btnPrevius.removeAttribute('disabled')
        formFocus++

        validateNavPill()
        return
      }

      if (formFocus === 2) {
        // INSERTAR DATOS DE ASIGNACIONES, APORTES, DEDUCCIONES

        // ¡¡¡¡¡¡¡MODIFICAR PARA QUE SE REALICE LA PETICIÓN CON EL NOMBRE E IDENTIFICADOR
        let data = {}
        data.registro_anterior = await getComparacionNomina2({
          nombre_nomina: calculoInformacion.nombre_nomina,

          confirmBtn: false,
        })

        data.registro_actual = {
          asignaciones: calculoInformacion.suma_asignaciones,
          deducciones: calculoInformacion.suma_deducciones,
          aportes: calculoInformacion.suma_aportes,
          empleados: calculoInformacion.empleados,
          identificador: calculoInformacion.identificador,
          status: 'En proceso',
          total_pagar: calculoInformacion.total_pagar,
          nombre_nomina: calculoInformacion.nombre_nomina,
        }

        let requestComparationContainer = d.getElementById(
          'request-comparation-container'
        )
        if (requestComparationContainer) requestComparationContainer.remove()

        createComparationContainer({
          data,
          elementToInsert: 'request-form-information-body',
        })

        let tablaDiferencia = await nom_comparation_employee({
          anterior: data.registro_anterior.empleados,
          actual: data.registro_actual.empleados,
          obtenerEmpleado: getEmployeeData,
          elementToInsert: 'request-form-information-body',
        })

        requestStepPart2.classList.add('hide')
        requestStepPart3.classList.remove('hide')

        btnPrevius.textContent = 'Anterior'
        //  Habilitar
        btnNext.setAttribute('disabled', '')

        formFocus++

        validateNavPill()
        return
      }
    }

    if (e.target.id === 'btn-previus') {
      scroll(0, 0)
      if (formFocus === 3) {
        e.target.textContent = 'Cancelar petición'

        requestStepPart2.classList.remove('hide')
        requestStepPart3.classList.add('hide')

        //  Habilitar
        btnNext.removeAttribute('disabled')
        btnPrevius.textContent = 'Cancelar peticion'
        formFocus--
        validateNavPill()
        return
      }

      if (formFocus === 2) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message:
            'Hacer esto borrará el calculo y se tendrá que realizar de nuevo ¿Desea continuar?',
          successFunction: function () {
            // Resetear formulario

            btnPrevius.textContent = 'Anterior'
            btnPrevius.setAttribute('disabled', '')

            requestStepPart2.classList.add('hide')
            requestStepPart1.classList.remove('hide')

            resetForm()
            // Deshabilitar
            btnPrevius.setAttribute('disabled', '')

            validateNavPill()
          },
        })

        return
      }
    }

    if (e.target.id === 'show-employee-list') {
      let modalEmployeeList = d.getElementById('modal-employee-list')
      if (modalEmployeeList) modalEmployeeList.remove()
      newRequestForm.insertAdjacentHTML('afterbegin', nom_empleados_list_card())

      console.log(calculoInformacion.informacion_empleados)

      loadEmployeeList({
        listaEmpleados: calculoInformacion.informacion_empleados,
      })
    }
    // console.log(formFocus)
  })

  function resetForm() {
    // Resetear fo rmulario
    newRequestForm.reset()

    let option = `<option value="">Grupo de nómina vacío</option>`

    selectNomina.innerHTML = ''
    selectNomina.insertAdjacentHTML('beforeend', option)

    calculoInformacion = ''
    nominas = ''
    employeeNewStatus = []
    // Volocar la vista en la primera parte del formulario
    formFocus = 1
    requestStepPart1.classList.remove('hide')
    requestStepPart2.classList.add('hide')
    requestStepPart3.classList.add('hide')

    validateNavPill()
    // Ocultar información adicional
    requestFormInformation.classList.add('hide')
    // Eliminar cards de información
    let employeePayTableCard = d.getElementById('request-employee-table-card')
    let card = d.getElementById('employee-new-status-card')
    let requestComparationContainer = d.getElementById(
      'request-comparation-container'
    )
    if (employeePayTableCard) employeePayTableCard.remove()
    if (card) card.remove()
    if (requestComparationContainer) requestComparationContainer.remove()

    // Resetear lista de valores
    for (let key in fieldList) {
      fieldList[key] = ''
    }
  }
}

function validateNavPill() {
  d.querySelectorAll(`[data-part]`).forEach((navPill) => {
    navPill.classList.remove('active')
  })
  d.querySelectorAll(`[data-part="part${formFocus}"]`)[0].classList.add(
    'active'
  )
}

export function validarIdentificador(identificador) {
  if (identificador.startsWith('s'))
    return `Semana ${identificador.slice(1, identificador.length)}`
  if (identificador.startsWith('q'))
    return `Quincena ${identificador.charAt(1)}`
  if (identificador.startsWith('p')) return `Periodo ${identificador.charAt(1)}`
  if (identificador === 'fecha_unica') return `Mensual`

  return identificador
}

function empleadosModificados(listaEmpleados) {
  let tr = ''

  listaEmpleados.forEach((el) => {
    tr += `
      <tr>
        <td>${el.cedula}</td>
        <td>${el.nombres}</td>
        <td>${el.defaultValue}</td>
        <td>${el.value}</td>
      </tr>
    `
  })
  return ` <div
      class='card d-flex flex-row overflow-auto'
      id='employee-new-status-card'
    >
      <div class='card-header d-flex flex-column align-items-center justify-content-center'>
        <h5 class='mb-0'>Información de empleados</h5>
        <small class='text-muted m-0'>
         Últimos cambios de los empleados
        </small>
      
      </div>
      <div
        class='card-body'
        style='
      max-height: 400px;
      overflow: auto;
  '
      >
        <table
          class='table'
          style='width: 100%; min-width: 18rem; max-height: 400px'
          id='employee-change-list'
        >
          <thead>
            <tr>
              <th class='table-warning'>
                <i>Cedula</i>
              </th>
              <th class=''>Nombre</th>
              <th class=''>Anterior</th>
              <th class=''>Nuevo</th>
            </tr>
          </thead>
          <tbody>${tr}</tbody>
        </table>
      </div>
    </div>`
}

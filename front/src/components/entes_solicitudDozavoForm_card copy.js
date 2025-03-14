import {
  getEntesAsignacion,
  getEntesAsignaciones,
  registrarEnteSolicitudDozavo,
} from '../api/entes_solicitudesDozavos.js'
import { getDistribucionEntes } from '../api/form_entes.js'
import {
  getPreAsignacionEnte,
  getPreAsignacionEntes,
} from '../api/pre_entes.js'
import { registrarSolicitudDozavo } from '../api/pre_solicitudesDozavos.js'
import { loadSolicitudEntesTable } from '../controllers/entes_solicitudTable.js'
import { loadSolicitudesDozavosTable } from '../controllers/pre_solicitudesDozavosTable.js'
import {
  confirmNotification,
  formatearFloat,
  hideLoader,
  insertOptions,
  separadorLocal,
  tableLanguage,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES, meses } from '../helpers/types.js'

const d = document

// OTRO COMPONENTE

const mesesOptions = () => {
  let mesActual = new Date().getMonth()

  let mesesOptionsElement = meses.map((mes, index) => {
    if (index)
      if (mesActual === index)
        return `<option value='${index}' selected>${mes}</option>`
    return `<option value='${index}'>${mes}</option>`
  })

  let options = [`<option value=''>Elegir...</option>`, ...mesesOptionsElement]

  return options.join('')
}

export const entes_solicitudGenerar_card2 = async ({
  elementToInsert,
  enteId,
  close = false,
  ejercicioId,
  reset,
  asignacionEnte,
}) => {
  let fieldList = { mes: '', descripcion: '' }
  let fieldListErrors = {
    mes: {
      value: true,
      message: 'Elija un mes válido',
      type: 'text',
    },
    descripcion: {
      value: true,
      message: 'Escriba descripción de la solicitud',
      type: 'text',
    },
  }

  let fieldListDozavos = {}
  let fieldListErrorsDozavos = {}

  const oldCardElement = d.getElementById('solicitud-distribucion-form-card')
  if (oldCardElement) {
    closeCard(oldCardElement)
  }

  if (close) return

  let dependencias = asignacionEnte.dependencias

  let actividadesEnte = asignacionEnte.actividades_entes

  let distribucionesSeleccionadas = []
  let dozavoMontos = []

  const listaDependencias = () => {
    // let dozavoMontoTotal = 0
    let montosActividadDistribuido = {}
    let montosActividadDozavo = {}
    actividadesEnte.forEach((distribucion) => {
      montosActividadDozavo[distribucion.actividad] = 0
      montosActividadDistribuido[distribucion.actividad] = 0
      distribucion.distribucion_partidas.forEach((partida) => {
        // let doceavaParte = Number(partida.monto) / 12

        // dozavoMontoTotal += doceavaParte
        // montosActividadDozavo[distribucion.actividad] += doceavaParte
        montosActividadDistribuido[distribucion.actividad] += partida.monto
      })
    })

    // Guardar total de dozavo
    // fieldList.dozavoMontoTotal = dozavoMontoTotal
    //   <p class="mb-0">
    //   <b>Dozavo:</b>
    //   <span class='px-2 rounded text-secondary'>
    //     ${separadorLocal(
    //       montosActividadDozavo[dependencia.actividad]
    //     )}
    //     Bs
    //   </span>
    // </p>

    let dependenciasElement =
      dependencias.length > 0
        ? dependencias
            .filter((dependencia) =>
              actividadesEnte.some(
                (distribucionActividad) =>
                  distribucionActividad.actividad === dependencia.actividad
              )
            )
            .sort((a, b) => a.actividad - b.actividad)
            .map((dependencia) => {
              return `<li class='list-group-item'>
                  <p class='mb-2'>${dependencia.ente_nombre}</p>

                  <p class='mb-0'>
                    <b>Actividad: </b>
                    <span class='px-2 rounded text-secondary'>
                      ${dependencia.actividad}
                    </span>
                  </p>
                  <p class="mb-0">
                    <b>Distribuido: </b>
                    <span class='px-2 rounded text-secondary'>
                      ${separadorLocal(
                        montosActividadDistribuido[dependencia.actividad]
                      )}
                      Bs
                    </span>
                  </p>
                
                </li>`
            })
            .join('')
        : ''

    return `
          <ul class='list-group mb-4'>${dependenciasElement}</ul>        
      `
  }

  let cardBodyPartView1 = () => {
    let fila = []

    actividadesEnte.forEach((distribucion) => {
      distribucion.distribucion_partidas.forEach((partida) => {
        let dozavo = partida.monto / 12
        let codigo = `${
          partida.sector_informacion ? partida.sector_informacion.sector : '0'
        }.${
          partida.programa_informacion
            ? partida.programa_informacion.programa
            : '0'
        }.${
          // partida.proyecto_informacion == 0
          //   ? '00'
          //   : partida.proyecto_informacion.proyecto
          '00'
        }.${distribucion.actividad == 0 ? '00' : distribucion.actividad}`

        fila.push(`   
          <tr>     
        <td><input type="checkbox" class="dozavo-checkbox" id="${
          partida.id_distribucion
        }"></td>
        <td>${codigo}</td>
        <td>${partida.partida_informacion.partida}</td>
        <td>${separadorLocal(partida.monto)} Bs</td>
        
        </tr>`)
      })
    })

    let card1 = ` <div class='card-body slide-up-animation' id='card-body-part-1'>
        <h4 class='mb-3'>Seleccione las partidas a crear el dozavo</h4>
        <table class='table table-striped table-sm' id="distribucion-ente-table">
          <thead class=''>
            
              <th>Seleccionar</th>
              <th>S/P/P/A</th>
              <th>PARTIDA</th>
              <th>MONTO PARTIDA</th>
            
          </thead>
          <tbody>${fila.join('')}</tbody>
        </table>
      </div>`

    return card1
  }

  let cardBodyPartView2 = () => {
    let fila = []

    distribucionesSeleccionadas.forEach((partida) => {
      let codigo = `${
        partida.sector_informacion ? partida.sector_informacion.sector : '0'
      }.${
        partida.programa_informacion
          ? partida.programa_informacion.programa
          : '0'
      }.${
        // partida.proyecto_informacion == 0
        //   ? '00'
        //   : partida.proyecto_informacion.proyecto
        '00'
      }.${partida.actividad == 0 ? '00' : partida.actividad}`

      fila.push(
        ` <tr>
          <td>${codigo}</td>
          <td>${partida.partida_informacion.partida}</td>
          <td>${separadorLocal(partida.monto)} Bs</td>
          <td>
            <input
              class='form-control dozavo-monto'
              name='distribucion-monto-${partida.id_distribucion}'
              id='distribucion-monto-${partida.id_distribucion}'
              data-distribucionid='${partida.id_distribucion}'
              placeholder='monto a solicitar'
            />
          </td>
        </tr>`
      )
    })

    let card2 = ` <div class='card-body slide-up-animation' id='card-body-part-2'>
        <h4 class='mb-3'>Asigne el monto a las partidas seleccionadas</h4>
        <table class='table table-striped' id="distribucion-ente-table2">
          <thead class=''>
            

              <th>S/P/P/A</th>
              <th>PARTIDA</th>
              <th>MONTO PARTIDA</th>
              <th>DOZAVO A SOLICITAR</th>
            
          </thead>
          <tbody>${fila.join('')}</tbody>
        </table>
      </div>`

    return card2
  }

  let cardBodyPartView3 = () => {
    let fila = []

    dozavoMontos.forEach((dozavo) => {
      let partida = distribucionesSeleccionadas.find(
        (p) => Number(p.id_distribucion) === Number(dozavo.id)
      )

      let codigo = `${
        partida.sector_informacion ? partida.sector_informacion.sector : '0'
      }.${
        partida.programa_informacion
          ? partida.programa_informacion.programa
          : '0'
      }.${
        // partida.proyecto_informacion == 0
        //   ? '00'
        //   : partida.proyecto_informacion.proyecto
        '00'
      }.${partida.actividad == 0 ? '00' : partida.actividad}`

      let montoFinal = partida.monto - dozavo.monto

      fila.push(
        `   <tr>
          <td>${codigo}.${partida.partida_informacion.partida}</td>
          <td>${separadorLocal(partida.monto)} Bs</td>
          <td>${separadorLocal(dozavo.monto)} Bs</td>
          <td>${separadorLocal(montoFinal)} Bs</td>
        </tr>`
      )
    })

    let card3 = ` <div class='card-body slide-up-animation' id='card-body-part-3'>

    <form id='solicitud-distribucion-form'>
              
              <h4>Información para generar solicitud</h4>
              <h5 mt-3>
                Monto total de dozavo:
                <span class='px-2 rounded text-green-600 bg-green-100'>
                  ${separadorLocal(fieldList.dozavoMontoTotal)} Bs
                </span>
              </h5>
              <div class='form-group'>
                <label for='mes' class='form-label'>
                  Mes de solicitud (mes actual por defecto)
                </label>
                <select
                  class='form-control solicitud-input chosen-select'
                  type='text'
                  name='mes'
                  id='mes'
                >
                  ${mesesOptions()}
                </select>
              </div>

              <div class='form-group'>
                <label for='mes' class='form-label'>
                  Descripción
                </label>
                <textarea
                  class='form-control solicitud-input'
                  name='descripcion'
                  id='descripcion'
                  rows='2'
                ></textarea>
              </div>
            </form>
    
        <h4 class='mb-3'>Verifique el monto solicitado de las partidas</h4>
        <table class='table table-sm table-striped' distribucion-ente-table3>
          <thead class=''>
            

              <th>S/P/P/A - Partida</th>
              <th>DISPONIBLE</th>
              <th>SOLICITADO</th>
              <th>RESTANTE</th>
            
          </thead>
          <tbody>${fila.join('')}</tbody>
        </table>
      </div>`

    return card3
  }

  let card = `    <div class='card slide-up-animation' id='solicitud-distribucion-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Generar solicitud de dozavo</h5>
          <small class='mt-0 text-muted'>
            Valide la información para generar la solicitud del dozavo
          </small>
        </div>
        <button
          data-close='btn-close'
          type='button'
          class='btn btn-danger'
          aria-label='Close'
        >
          &times;
        </button>
      </div>
      <div class="card-body row" class="row">
     <div class='text-center my-auto col-sm-4'>
            <h5>Actividades:</h5>${listaDependencias()}
          </div>

          <div class='col-sm'>
          <h5 class="text-center">Información para generar solicitud:</h5>
            <div id="card-body""> 
              ${cardBodyPartView1()}
            </div>
          </div>
              

     
      </div>
      
      <div class='card-footer d-flex align-items-center justify-content-center gap-2 py-2'>
       
          <button class='btn btn-secondary' id='btn-previus'>
          Atrás
        </button>
        <button class='btn btn-primary' id='btn-next'>
          Siguiente
        </button>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  validarEntesTabla()

  let cardElement = d.getElementById('solicitud-distribucion-form-card')
  let cardBody = d.getElementById('card-body')

  let formFocus = 1
  // let formElement = d.getElementById('solicitud-distribucion-form')

  function closeCard(card) {
    // validateEditButtons()
    card.remove()
    card.removeEventListener('click', validateClick)
    card.removeEventListener('input', validateInputFunction)

    return false
  }

  async function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard(cardElement)
    }

    if (e.target.id === 'solicitud-cancelar') {
      closeCard(cardElement)
    }

    if (e.target.id === 'solicitud-generar') {
      let dozavoMontoTotal = 0

      let partidasDozavos = []

      // actividadesEnte.forEach((distribucion) => {
      //   distribucion.distribucion_partidas.forEach((partida) => {
      //     let monto = partida.monto / 12
      //     partidasDozavos.push({
      //       id: Number(partida.id_distribucion),
      //       monto: Number(monto.toFixed(2)),
      //     })
      //   })
      // })

      // let dozavoPartidas = asignacionEnte.distribucion_partidas.map(
      //   (distribucion) => {
      //     dozavoMontoTotal += Number(distribucion.monto)

      //     let dozavaParte = Number(distribucion.monto) / 12

      //     return {
      //       id_distribucion: Number(distribucion.id_distribucion),
      //       monto: dozavaParte.toFixed(2),
      //     }
      //   }
      // )
    }

    validateFormFocus(e)
  }

  async function validateInputFunction(e) {
    if (e.target.classList.contains('dozavo-checkbox')) {
      // console.log(e.target.id)

      if (e.target.checked) {
        actividadesEnte.forEach((distribucion) => {
          // console.log(distribucion)

          let partida = distribucion.distribucion_partidas.find(
            (partida) => partida.id_distribucion === e.target.id
          )

          partida.actividad = distribucion.actividad

          distribucionesSeleccionadas.push(partida)
        })
      } else {
        const index = distribucionesSeleccionadas.findIndex(
          (partida) => partida.id_distribucion === e.target.id
        )
        if (index !== -1) {
          distribucionesSeleccionadas.splice(index, 1)
        }
      }

      // console.log(distribucionesSeleccionadas)

      return
    }

    if (e.target.classList.contains('dozavo-monto')) {
      fieldListDozavos = validateInput({
        target: e.target,
        fieldList: fieldListDozavos,
        fieldListErrors: fieldListErrorsDozavos,
        type: fieldListErrorsDozavos[e.target.name].type,
      })
    } else {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })
    }
  }

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')

    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part-1')
    let cardBodyPart2 = d.getElementById('card-body-part-2')
    let cardBodyPart3 = d.getElementById('card-body-part-3')

    if (e.target === btnNext) {
      if (formFocus === 1) {
        // let planInputs = d.querySelectorAll('.plan-input')

        // planInputs.forEach((input) => {
        //   fieldList = validateInput({
        //     target: input,
        //     fieldList,
        //     fieldListErrors,
        //     type: fieldListErrors[input.name].type,
        //   })
        // })

        // if (Object.values(fieldListErrors).some((el) => el.value)) {
        //   toastNotification({
        //     type: NOTIFICATIONS_TYPES.fail,
        //     message: 'Hay campos inválidos',
        //   })
        //   return
        // }

        if (distribucionesSeleccionadas.length < 1) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'No se han seleccionado distribuciones a solicitar dozavo',
          })
          return
        }

        cardBodyPart1.classList.add('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.outerHTML = cardBodyPartView2()
        } else {
          cardBody.insertAdjacentHTML('beforeend', cardBodyPartView2())
        }

        validarEntesTabla2()

        let dozavoMontosInputs = d.querySelectorAll('.dozavo-monto')
        // console.log(dozavoMontosInputs)

        dozavoMontosInputs.forEach((input) => {
          fieldListDozavos = {
            ...fieldListDozavos,
            [input.name]: 0,
          }
          fieldListErrorsDozavos = {
            ...fieldListErrorsDozavos,
            [input.name]: {
              value: true,
              message: 'Monto inváldo',
              type: 'number3',
            },
          }
        })

        if (btnPrevius.hasAttribute('disabled'))
          btnPrevius.removeAttribute('disabled')

        formFocus++
        return
      }
      if (formFocus === 2) {
        // let planInputsOptions = d.querySelectorAll('.plan-input-option')

        // planInputsOptions.forEach((input) => {
        //   fieldListOptions = validateInput({
        //     target: input,
        //     fieldListOptions,
        //     fieldListErrorsOptions,
        //     type: fieldListErrorsOptions[input.name].type,
        //   })
        // })

        // if (Object.values(fieldListErrorsOptions).some((el) => el.value)) {
        //   toastNotification({
        //     type: NOTIFICATIONS_TYPES.fail,
        //     message: 'Hay campos inválidos',
        //   })
        //   return
        // }

        let dozavoMontosInputs = Array.from(d.querySelectorAll('.dozavo-monto'))

        let sumaTotal = 0

        // Mapear y validar inputs de montos de partidas
        dozavoMontos = dozavoMontosInputs.map((input) => {
          fieldListDozavos = validateInput({
            target: input,
            fieldList: fieldListDozavos,
            fieldListErrors: fieldListErrorsDozavos,
            type: fieldListErrorsDozavos[input.name].type,
          })

          let monto = formatearFloat(input.value)

          sumaTotal += monto

          return {
            id: input.dataset.distribucionid,
            monto,
          }
        })

        fieldList.dozavoMontoTotal = sumaTotal

        console.log(dozavoMontos)

        // VALIDAR ERRORES EN LOS INPUTS

        if (
          Object.values(fieldListErrorsDozavos).some((input) => input.value)
        ) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Montos inválidos',
          })
          return
        }

        let montosInvalidos = distribucionesSeleccionadas.every(
          (distribucion) => {
            let dozavoMonto = formatearFloat(
              fieldListDozavos[
                `distribucion-monto-${distribucion.id_distribucion}`
              ]
            )
            return dozavoMonto <= distribucion.monto
          }
        )

        if (!montosInvalidos) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Hay distribuciones que superan el monto disponible',
          })
          return
        }

        cardBodyPart2.classList.add('d-none')

        if (cardBodyPart3) {
          cardBodyPart3.outerHTML = cardBodyPartView3()
        } else {
          cardBody.insertAdjacentHTML('beforeend', cardBodyPartView3())
        }

        validarEntesTabla3()

        btnNext.textContent = 'Enviar solicitud'

        formFocus++
        return
      }

      if (formFocus === 3) {
        // let data = validarInformacion()

        let inputs = d.querySelectorAll('.solicitud-input')

        inputs.forEach((input) => {
          fieldList = validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          })
        })

        if (Object.values(fieldListErrors).some((el) => el.value)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Hay campos inválidos',
          })
          return
        }

        if (Object.values(fieldList).some((el) => !el)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'No se ha completado la información necesaria',
          })
          return
        }

        let dozavoInformacion = {
          id_ente: asignacionEnte.id_ente,
          descripcion: fieldList.descripcion,
          monto: fieldList.dozavoMontoTotal,
          partidas: dozavoMontos,
          id_ejercicio: ejercicioId,
          tipo: 'D',
          mes: fieldList.mes,
        }

        enviarInformacion(dozavoInformacion)
      }
    }

    if (e.target === btnPrevius) {
      if (formFocus === 3) {
        cardBodyPart2.classList.remove('d-none')
        btnNext.textContent = 'Siguiente'

        if (cardBodyPart3) {
          cardBodyPart3.classList.add('d-none')
        }

        formFocus--
        return
        // confirmNotification({
        //   type: NOTIFICATIONS_TYPES.send,
        //   message: 'Si continua se borrarán los cambios hechos aquí',
        //   successFunction: function () {
        //     cardBodyPart2.remove()

        //     cardBodyPart1.classList.remove('d-block')
        //     cardBodyPart1.classList.add('d-none')
        //     btnNext.textContent = 'Siguiente'
        //     // btnAdd.classList.remove('d-none')

        //     partidasSeleccionadas = []
        //     cardBody.innerHTML += seleccionPartidas()
        //     validarSeleccionPartidasTable()

        //     formFocus--
        //   },
        // })
      }
      if (formFocus === 2) {
        cardBodyPart1.classList.remove('d-none')

        if (cardBodyPart2) {
          cardBodyPart2.classList.add('d-none')
        }

        formFocus--

        btnPrevius.setAttribute('disabled', true)

        return
      }
    }
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: `Se enviará la solicitud de dozavo`,
      successFunction: async function () {
        let res = await registrarEnteSolicitudDozavo(data)
        if (res.success) {
          closeCard(cardElement)
          loadSolicitudEntesTable({ id_ejercicio: ejercicioId })
          reset()
        }
      },
    })
  }

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)

  function validarEntesTabla() {
    let entesTable = new DataTable('#distribucion-ente-table', {
      scrollY: 250,

      language: tableLanguage,
      layout: {
        topStart: function () {
          let toolbar = document.createElement('div')
          toolbar.innerHTML = `
                            `
          return toolbar
        },
        topEnd: { search: { placeholder: 'Buscar...' } },
        bottomStart: 'info',
        bottomEnd: 'paging',
      },
    })
  }
}

function validarEntesTabla2() {
  let entesTable = new DataTable('#distribucion-ente-table2', {
    scrollY: 250,

    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
                          `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

function validarEntesTabla3() {
  let entesTable = new DataTable('#distribucion-ente-table3', {
    scrollY: 250,

    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
                
                          `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

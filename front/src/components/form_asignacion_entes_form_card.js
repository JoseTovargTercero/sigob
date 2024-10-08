// CONTINUAR MAQUETADO
// AJUSTAR INFORMACIÓN DE LA DISTRIBUCIÓN PRESUPUESTARIA
// AJUSTAR INFORMACIÓN DEL PLAN OPERATIVO DE ENTES
// COLOCAR EL MONTO RESTANTE EN UN HEADER EN LA CARD
// AÑADIR UNA TABLA PARA SELECCIONAR PARTIDAS QUE SE QUIERAN ASIGNAR PARA POSTERIOR ASIGNARLES SU MONTO

import { getPartidas } from '../api/partidas.js'
import { getEjecicio, getEjecicios } from '../api/pre_distribucion.js'
import {
  confirmNotification,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
const d = document

const tableLanguage = {
  decimal: '',
  emptyTable: 'No hay datos disponibles en la tabla',
  info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
  infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
  infoFiltered: '(filtrado de _MAX_ entradas totales)',
  infoPostFix: '',
  thousands: ',',
  lengthMenu: 'Mostrar _MENU_',
  loadingRecords: 'Cargando...',
  processing: '',
  search: 'Buscar:',
  zeroRecords: 'No se encontraron registros coincidentes',
  paginate: {
    first: 'Primera',
    last: 'Última',
    next: 'Siguiente',
    previous: 'Anterior',
  },
  aria: {
    orderable: 'Ordenar por esta columna',
    orderableReverse: 'Orden inverso de esta columna',
  },
}

export const form_asignacion_entes_form_card = async ({
  elementToInset,
  plan,
  ejercicioFiscal,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  // PARA VALIDAR INPUTS DE PARTIDAS
  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  // PARA GUARDAR PARTIDAS SELECCIONADAS
  let partidasSeleccionadas = []

  // CONTROLAR FOCUS DEL FORMUALRIO
  let formFocus = 1

  // OBTENER DATOS PARA TRABAJAR EN EL FORMULARIO

  let partidas = await getPartidas()

  console.log(ejercicioFiscal)
  console.log(partidas)

  let montos = { total: 0, restante: 0, acumulado: 0 }

  montos.total = ejercicioFiscal.situado
  montos.restante = ejercicioFiscal.restante
  montos.total_solicitado = plan.monto

  const oldCardElement = d.getElementById('asignacion-entes-form-card')
  if (oldCardElement) oldCardElement.remove()

  let informacionparadistribucionpresupuestaria = `  <div class='col'>
      <h5 class=''>Información de la distribución presupuestaria anual</h5>
      <h5 class=''>
        <b>Año actual:</b>
        <span>${ejercicioFiscal ? ejercicioFiscal.ano : 'No definido'}</span>
      </h5>
      <h5 class=''>
        <b>Situado actual:</b>
        <span>
          ${
            ejercicioFiscal
              ? separarMiles(ejercicioFiscal.situado)
              : 'No definido'
          }
        </span>
      </h5>
      <ul class='list-group'></ul>
    </div>`

  // PARTE 1

  const distribucionPartidasEnteList = (checkbox) => {
    let liItems = plan.partidas.map((partida) => {
      let partidaEncontrada = partidas.fullInfo.find(
        (par) => par.id == partida.id
      )
      if (checkbox) {
        return ` <tr class=''>
        <td><input type="checkbox" class="form-check-input input-check" value="${
          partidaEncontrada.id
        }" name="ente-partida-${partidaEncontrada.id}"/></td>
        <td>${partidaEncontrada.partida}</td>
        <td>${partidaEncontrada.nombre}</td>
        <td>${partidaEncontrada.descripcion}</td>
        <td> ${separarMiles(partida.monto)} Bs.</td>
      </tr>`
      } else {
        return ` <tr class=''>
        <td>${partidaEncontrada.partida}</td>
        <td>${partidaEncontrada.nombre}</td>
        <td>${partidaEncontrada.descripcion}</td>
        <td> ${separarMiles(partida.monto)} Bs.</td>
      </tr>`
      }
    })

    return liItems.join('')
  }

  const planEnte = () => {
    return ` <div id="card-body-part1" class="slide-up-animation">
        <h4>PLAN OPERATIVO DE ENTE</h4>
        <h5>Nombre: ${plan.ente_nombre}</h5>
        <h5>
          Tipo: ${plan.tipo_ente === 'J' ? 'juridico' : 'Descentralizado'}
        </h5>
        <h5>Monto total solicitado: ${separarMiles(plan.monto)}</h5>

        <table
          id='asignacion-part1-table'
          class='table table-striped table-sm'
          style='width:100%'
        >
          <thead class='w-100'>
          <th>PARTIDA</th>
            <th>NOMBRE</th>
            <th>DESCRIPCION</th>
            <th>MONTO</th>
          </thead>
          
          <tbody>${distribucionPartidasEnteList()}</tbody>
        </table>
      </div>
      `
  }

  // PARTE 2

  const distribucionPartidasList = () => {
    // if (!ejercicio)
    //   return ` <li class='list-group-item list-group-item-danger'>
    //       <h6>No se pudo obtener las partidas del ejercicio fiscal</h6>
    //     </li>`

    // if (ejercicio.partidas < 1) {
    //   return ` <li class='list-group-item list-group-item-danger'>
    //       <h6>No hay partidas distribuidas en el ejercicio fiscal</h6>
    //     </li>`
    // } else {
    // }
    let liItems = ejercicioFiscal.partidas.map((partida) => {
      let partidaEncontrada = partidas.fullInfo.find(
        (par) => par.id == partida.id
      )

      return ` <tr>
          <td>
            <input type='checkbox' value="${
              partida.id
            }" class="form-check-input input-check" name='partida-ejercicio-${
        partida.id
      }' id='partida-ejetcicio-${partida.id}' />
          </td>
          <td>${partidaEncontrada.partida}</td>
          <td>${partidaEncontrada.nombre}</td>
          <td>${partidaEncontrada.descripcion}</td>
          <td> ${separarMiles(partida.monto_inicial)} Bs.</td>
        </tr>`
    })

    return liItems.join('')
  }

  const formularioNuevaPartida = () => {
    let options = partidas.fullInfo
      .map((option) => {
        return `<option value="${option.partida}">${option.descripcion}</option>`
      })
      .join('')
    return `  <div class='row mt-4 d-none slide-up-animation' id="form-nueva-partida">  
          <label for='partida-nueva'>Nueva partida a añadir</label>
          <div class='input-group'>
            <div class='w-80'>
              <input
                class='form-control'
                type='text'
                name='partida-nueva'
                id='partida-nueva-input'
                list='partidas-list'
                placeholder='Seleccione partida a añadir'
              />
            </div>
            <div class='input-group-prepend'>
              <button class='btn btn-primary' id="btn-add-partida">Añadir partida</button>
            </div>
          </div>
          <datalist id='partidas-list'>${options}</datalist>

      </div>`
    // addSeleccionPartidasrow()
  }

  const seleccionPartidas = () => {
    return `<div id='card-body-part2' class="slide-up-animation">
    <h4 class="text-center text-info">Seleccione las partidas:</h4>
    <h5 class="text-center text-info">Partidas seleccionadas: <b id="partidas-seleccionadas">0</b></h5>
        
          <div class=''>
            <table
              id='asignacion-part3-table'
              class='table table-striped table-sm'
              style='width:100%'
            >
              <thead class='w-100'>
                <th>ELEGIR</th>
                <th>PARTIDA</th>
                <th>NOMBRE</th>
                <th>DESCRIPCION</th>
                <th>MONTO SOLICITADO</th>
              </thead>
              <tbody>${distribucionPartidasEnteList(true)}</tbody>
            </table>
          </div>
        
          ${formularioNuevaPartida()}
      </div>`
  }

  // PARTE 3: ASIGNAR MONTOS A PARTIDAS

  const partidasSeleccionadasList = () => {
    let partidasRelacionadas = relacionarPartidas()

    let liItems = partidasRelacionadas.map((partida) => {
      fieldListPartidas[`partida-monto-${partida.id}`] = ''
      fieldListErrorsPartidas[`partida-monto-${partida.id}`] = {
        value: true,
        message: 'Monto inválido',
        type: 'number',
      }

      return `  <tr>
          <td>${partida.partida}</td>
          <td>${partida.nombre}</td>
          <td>${partida.monto_solicitado}</td>
          <td>
          <input
          class='form-control partida-input partida-monto-disponible'
          type='text'
          data-valorinicial='${partida.monto_disponible}'
          name='partida-monto-disponible-${partida.id}'
          id='partida-monto-disponible-${partida.id}'
          placeholder='Monto a asignar...'
          value="${partida.monto_disponible}"

          disabled
        />
</td>
          
          <td>
            <input
              class='form-control partida-input partida-monto'
              type='number'
              data-id='${partida.id}'
              name='partida-monto-${partida.id}'
              id='partida-monto-${partida.id}'
              placeholder='Monto a asignar...'
            />
          </td>
        </tr>`
    })

    return liItems.join('')
  }

  function relacionarPartidas() {
    let partidasRelacionadas = partidasSeleccionadas.map((id) => {
      let partidaEncontrada = partidas.fullInfo.find((par) => par.id == id)

      let partidaEncontrada2 = plan.partidas.find((partida) => partida.id == id)
      let partidaEncontrada3 = ejercicioFiscal.partidas.find(
        (partida) => partida.id == id
      )

      return {
        id: id,
        partida: partidaEncontrada.partida,
        nombre: partidaEncontrada.nombre || 'No asignado',
        descripcion: partidaEncontrada.descripcion,
        monto_disponible: partidaEncontrada3
          ? partidaEncontrada3.monto_inicial
          : 0,
        monto_solicitado: partidaEncontrada2
          ? partidaEncontrada2.monto
          : 'No solicitado ',
      }
    })

    console.log(partidasRelacionadas)

    return partidasRelacionadas
  }

  function partidaDisponibilidadPresupuestaria(id) {
    let partidaEncontrada3 = ejercicioFiscal.partidas.find(
      (partida) => partida.id == id
    )

    let partidasRelacionadas = partidasSeleccionadas.map((id) => {
      let partidaEncontrada = partidas.fullInfo.find((par) => par.id == id)

      let partidaEncontrada2 = plan.partidas.find((partida) => partida.id == id)
      let partidaEncontrada3 = ejercicioFiscal.partidas.find(
        (partida) => partida.id == id
      )

      return {
        id: id,
        partida: partidaEncontrada.partida,
        descripcion: partidaEncontrada.descripcion,
        monto_disponible: partidaEncontrada3
          ? partidaEncontrada3.monto_inicial
          : 'No asignado',
        monto_solicitado: partidaEncontrada2
          ? partidaEncontrada2.monto
          : 'No solicitado',
      }
    })

    console.log(partidasRelacionadas)

    return partidasRelacionadas
  }

  const asignarMontoPartidas = () => {
    return ` <div id="card-body-part3" class="slide-up-animation">
          <h4 class="text-center text-info">Asigne el monto a partidas:</h4>
          <h5>Nombre: ${plan.ente_nombre}</h5>
          <div class="d-flex gap-2 justify-content-between">
          <h5>Monto total solicitado: ${plan.monto}</h5>
          <h5>Monto total asignado: <b id="monto-total-asignado">0</b></h5>
          </div>

        <table
          id='asignacion-part4-table'
          class='table table-striped table-sm'
          style='width:100%'
        >
          <thead class='w-100'>
            <th>PARTIDA</th>
            <th>NOMBRE</th>
            <th>Monto monto solicitado</th>
            <th>Monto disponible</th>
            <th>ASIGNACION</th>
          </thead>
          
          <tbody>${partidasSeleccionadasList()}</tbody>
        </table>
      </div>
      `
  }

  let card = ` <div class='card slide-up-animation' id='asignacion-entes-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Validar información de plan operativo</h5>
          <small class='mt-0 text-muted'>
            Introduzca los datos para la verificar el plan operativo
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
      <div class='card-body' id='card-body-container'>
        
      </div>
      <div class='card-footer d-flex justify-content-center gap-2'>
       <button class='btn btn-secondary' id='btn-previus'>
          Atrás
        </button>
        <button class='btn btn-primary' id='btn-next'>
          Siguiente
        </button>
        <button class='btn btn-success d-none' id='btn-add'>
          Añadir
        </button>
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardBody = d.getElementById('card-body-container')

  // INICIALIZAR CARD
  cardBody.innerHTML = planEnte()
  validarPartidasEntesTable()

  let cardElement = d.getElementById('asignacion-entes-form-card')
  // let formElement = d.getElementById('asignacion-entes-form')

  const closeCard = () => {
    // validateEditButtons()
    cardElement.remove()
    cardElement.removeEventListener('click', validateClick)
    cardElement.removeEventListener('input', validateInputFunction)

    return false
  }

  function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.id === 'btn-add') {
      d.getElementById('form-nueva-partida').classList.remove('d-none')
    }
    if (e.target.id === 'btn-add-partida') {
      d.getElementById('form-nueva-partida').classList.add('d-none')

      let input = d.getElementById('partida-nueva-input')

      let partidaEncontrada = partidas.fullInfo.find(
        (partida) => partida.partida === input.value
      )
      let datos = [
        `<input type='checkbox' value="${partidaEncontrada.id}" class="form-check-input input-check" name='partida-ejercicio-${partidaEncontrada.id}' id='partida-ejetcicio-${partidaEncontrada.id}' />`,
        partidaEncontrada.partida,
        partidaEncontrada.nombre,
        partidaEncontrada.descripcion,
        'Monto no especificado',
      ]
      addSeleccionPartidasrow(datos)
      input.value = ''
    }

    // TENGO QUE ENVIAR LOS DATOS CON ESTA ESTRUCTURA: [[id_partida, monto, id_ente, id_poa, tipo]]
    validateFormFocus(e)
  }

  async function validateInputFunction(e) {
    if (e.target.classList.contains('input-check')) {
      // VALIDAR SI HAY PARTIDAS REPETIDAS
      validarCheckboxRepetido(e)
      // ALMACENAR PARTIDAS PARA LUEGO ASIGNAR MONTO
      partidasSeleccionadas = obtenerValorCheckbox({
        id_card: 'card-body-part2',
        id_text: 'partidas-seleccionadas',
      })
      console.log(partidasSeleccionadas)
    }
    if (e.target.classList.contains('partida-monto')) {
      actualizarMontoRestante()
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })

      let montoDisponibleInput = d.getElementById(
        `partida-monto-disponible-${e.target.dataset.id}`
      )
      console.log(montoDisponibleInput)

      montoDisponibleInput.value =
        Number(montoDisponibleInput.dataset.valorinicial) - e.target.value
    }
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    console.log(data)
  }

  function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')
    let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part1')
    let cardBodyPart2 = d.getElementById('card-body-part2')
    let cardBodyPart3 = d.getElementById('card-body-part3')

    if (e.target === btnNext) {
      scroll(0, 0)
      if (formFocus === 1) {
        cardBodyPart1.classList.add('d-none')

        cardBody.innerHTML += seleccionPartidas()
        validarSeleccionPartidasTable()

        formFocus++
        btnPrevius.classList.remove('d-none')
        btnPrevius.removeAttribute('disabled')
        btnAdd.classList.remove('d-none')
        return
      }
      if (formFocus === 2) {
        console.log(partidasSeleccionadas)

        if (partidasSeleccionadas.length === 0) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Seleccione al menos una partida',
          })
          return
        }
        let cardBodyPart2 = d.getElementById('card-body-part2')
        cardBodyPart2.remove()

        cardBody.innerHTML += asignarMontoPartidas()

        validarAsignacionPartidasTable()
        btnNext.textContent = 'Enviar'
        btnAdd.classList.add('d-none')
        formFocus++
        return
      }

      if (formFocus === 3) {
        let inputsPartidas = d.querySelectorAll('.partida-monto')

        inputsPartidas.forEach((input) => {
          fieldListPartidas = validateInput({
            target: input,
            fieldList: fieldListPartidas,
            fieldListErrors: fieldListErrorsPartidas,
            type: fieldListErrorsPartidas[input.name].type,
          })
        })

        if (Object.values(fieldListErrorsPartidas).some((el) => el.value)) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Debe asignar un monto a cada input',
          })
          return
        }

        let mappedInfo = Array.from(inputsPartidas).map((input) => {
          let id_partida = input.dataset.id
          console.log(id_partida)

          return [
            Number(input.dataset.id),
            Number(input.value),
            plan.id_ente,
            plan.id_poa,
          ]
        })

        enviarInformacion(mappedInfo)
        return
      }
    }

    if (e.target === btnPrevius) {
      scroll(0, 100)

      if (formFocus === 3) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message: 'Si continua se borrarán los cambios hechos aquí',
          successFunction: function () {
            cardBodyPart3.remove()

            cardBodyPart1.classList.remove('d-block')
            cardBodyPart1.classList.add('d-none')
            btnNext.textContent = 'Siguiente'
            btnAdd.classList.remove('d-none')

            partidasSeleccionadas = []
            cardBody.innerHTML += seleccionPartidas()
            validarSeleccionPartidasTable()

            formFocus--
          },
        })
        return
      }
      if (formFocus === 2) {
        btnPrevius.setAttribute('disabled', true)
        btnAdd.classList.add('d-none')

        cardBodyPart2.remove()

        cardBodyPart1.classList.remove('d-none')

        formFocus--
        return
      }
    }
  }

  function actualizarMontoRestante() {
    let montoElement = d.getElementById('monto-total-asignado')

    let inputsPartidasMontos = d.querySelectorAll('.partida-monto')

    // REINICIAR MONTO ACUMULADO
    montos.acumulado = 0

    inputsPartidasMontos.forEach((input) => {
      montos.acumulado += Number(input.value)
    })

    let diferenciaSolicitado = montos.total_solicitado - montos.acumulado

    if (diferenciaSolicitado < 0) {
      montoElement.innerHTML = `<span class="text-warning">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado > 0) {
      montoElement.innerHTML = `<span class="text-success">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado === 0) {
      montoElement.innerHTML = `<span class="text-secondary">${montos.acumulado}</span>`
      return
    }
  }

  function obtenerValorCheckbox({ id_card, id_text }) {
    const cardCheckbox = d.getElementById(id_card)
    let checkboxes = cardCheckbox.querySelectorAll('input[type="checkbox"]')
    let cantidadSeleccionado = 0
    let valores = []

    checkboxes.forEach(function (checkbox) {
      if (checkbox.checked) {
        valores.push(Number(checkbox.value))
        cantidadSeleccionado++
      }
    })

    if (id_text) {
      d.getElementById(id_text).textContent = cantidadSeleccionado
    }
    return valores
  }

  function validarCheckboxRepetido(e) {
    const cardCheckbox = d.getElementById('card-body-part2')

    let validado = false

    if (e.target.checked) {
      let checkboxes = cardCheckbox.querySelectorAll(
        'input[type=checkbox]:checked'
      )
      checkboxes.forEach((checkbox) => {
        if (
          checkbox.checked &&
          checkbox.value === e.target.value &&
          checkbox !== e.target
        ) {
          e.target.checked = false
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: 'Esta partida ya fue seleccionada',
          })
        }
      })
    }
  }

  // formElement.addEventListener('submit', (e) => e.preventDefault())

  cardElement.addEventListener('input', validateInputFunction)
  cardElement.addEventListener('click', validateClick)
}

function validarPartidasEntesTable() {
  let planesTable = new DataTable('#asignacion-part1-table', {
    responsive: true,
    scrollY: 120,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center mb-0">Lista de partidas solicitadas por el ente:</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}
let seleccionPartidasTable
function validarSeleccionPartidasTable() {
  // let planesTable2 = new DataTable('#asignacion-part2-table', {
  //   scrollY: 120,
  //   language: tableLanguage,
  //   layout: {
  //     topStart: function () {
  //       let toolbar = document.createElement('div')
  //       toolbar.innerHTML = `
  //           <h5 class="text-center mb-0">Distribución presupuestaria:</h5>
  //                     `
  //       return toolbar
  //     },
  //     topEnd: { search: { placeholder: 'Buscar...' } },
  //     bottomStart: 'info',
  //     bottomEnd: 'paging',
  //   },
  // })

  seleccionPartidasTable = new DataTable('#asignacion-part3-table', {
    scrollY: 200,
    colums: [
      { data: 'elegir' },
      { data: 'partida' },
      { data: 'nombre' },
      { data: 'descripcion' },
      { data: 'monto_solicitado' },
    ],
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center mb-0">Lista de partidas solicitadas por el ente:</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

function addSeleccionPartidasrow(datos) {
  seleccionPartidasTable.row.add(datos).draw()
}

function validarAsignacionPartidasTable() {
  let planesTable = new DataTable('#asignacion-part4-table', {
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center mb-0">Partidas seleccionadas:</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  let planesTable3 = new DataTable('#asignacion-part3-table', {
    scrollY: 200,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
            <h5 class="text-center mb-0">Lista de partidas solicitadas por el ente:</h5>
                      `
        return toolbar
      },
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })
}

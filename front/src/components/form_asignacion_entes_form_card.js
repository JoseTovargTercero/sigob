// CONTINUAR MAQUETADO
// AJUSTAR INFORMACIÓN DE LA DISTRIBUCIÓN PRESUPUESTARIA
// AJUSTAR INFORMACIÓN DEL PLAN OPERATIVO DE ENTES
// COLOCAR EL MONTO RESTANTE EN UN HEADER EN LA CARD
// AÑADIR UNA TABLA PARA SELECCIONAR PARTIDAS QUE SE QUIERAN ASIGNAR PARA POSTERIOR ASIGNARLES SU MONTO

import {
  aceptarDistribucionEnte,
  getDistribucionEnte,
  rechazarDistribucionEnte,
} from '../api/form_entes.js'
import { getFormPartidas, getPartidas } from '../api/partidas.js'
import {
  enviarDistribucionPresupuestariaEntes,
  getEjecicio,
  getEjecicios,
} from '../api/pre_distribucion.js'
import { loadAsignacionEntesTable } from '../controllers/form_asignacionEntesTable.js'
import {
  confirmNotification,
  formatearFloat,
  hideLoader,
  insertOptions,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { form_distribucion_entes_card } from './form_distribucion_entes_card.js'
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
  asignacion,
  ejercicioFiscal,
  actualizar,
}) => {
  let fieldList = { ejemplo: '' }
  let fieldListErrors = {
    ejemplo: {
      value: true,
      message: 'mensaje de error',
      type: 'text',
    },
  }

  // PARA GUARDAR DEPENDENCIAS DEL ENTE SELECCIONADAS
  let dependenciaEnteSeleccionada = null

  // DATOS A ENVIAR PARA REGISTRAR DISTRIBUCION

  let datosDistribucionActividades = []

  // DATOS PARA GUARDAR ESTADO DE LAS PARTIDAS (MONTO ASIGNADO Y/O DISPONIBLE)
  let disponibilidadPartida = {}

  ejercicioFiscal.distribucion_partidas.forEach((distribucion) => {
    disponibilidadPartida[`distribucion-${distribucion.id}`] = Number(
      distribucion.monto_actual
    )
  })

  // CONTROLAR FOCUS DEL FORMUALRIO
  let formFocus = 1

  // OBTENER DATOS PARA TRABAJAR EN EL FORMULARIO

  console.log(ejercicioFiscal)
  console.log(asignacion)
  console.log(asignacion.monto_total)

  let montos = { total: 0, restante: 0, acumulado: 0, distribuido_total: 0 }

  montos.total = ejercicioFiscal.situado
  montos.restante = ejercicioFiscal.restante
  montos.distribuido = ejercicioFiscal.distribuido
  montos.total_asignado = asignacion.monto_total

  const oldCardElement = d.getElementById('asignacion-entes-form-card')
  if (oldCardElement) oldCardElement.remove()

  // PARTE 1

  function recortarTexto(str, length) {
    let texto = str.length < length ? str : `${str.slice(0, length)} ...`

    return texto
  }

  const distribucionPartidasEnteList = ({ info }) => {
    let data = info

    let montoTotalDistribuido = 0

    let filas = data.map((actividad) => {
      let actividad_codigo = actividad.actividad,
        actividad_nombre = recortarTexto(actividad.ente_nombre, 35),
        status,
        acciones

      if (asignacion.actividades_entes.length > 0) {
        let distribucionesEstanAprobadas = asignacion.actividades_entes.every(
          (distribucion) => Number(distribucion.status) === 1
        )

        acciones = ` <button
          class='btn btn-sm bg-brand-color-2 text-white'
          disabled
        >
          <i class='bx bx-detail'></i>
        </button>`
      } else {
        acciones = `<button class="btn btn-danger btn-sm btn-destroy" data-eliminaractividadid="${
          actividad.actividad_id
        }" ${Number(asignacion.status) === 1 ? 'disabled' : ''}></button>
         <button
            data-distribuciondetalleid='${actividad.actividad_id}'
            type='button'
            class='btn btn-sm btn-success'
            data-toggle='tooltip'
            title='Ver distribucion'
            ${asignacion.status === 1 ? 'disabled' : ''}
          >
            <i class='bx bx-detail'></i>
          </button>`
      }

      if (actividad.status) {
        if (actividad.status === 0) {
          status = `<span class='btn btn-sm btn-secondary'>Pendiente</span>`
        }
        if (actividad.status === 1) {
          status = `<span class='btn btn-sm btn-success'>Aceptado</span>`
        }
        if (actividad.status === 2) {
          status = `<span class='btn btn-sm btn-danger'>Rechazada</span>`
        }
      } else {
        status = `<span class='btn btn-sm btn-secondary'>Pendiente</span>`
      }

      let montoTotalActividad = 0

      actividad.distribucion_partidas.forEach((partida) => {
        // let sector_codigo = `${partida.sector_informacion.sector}.${partida.sector_informacion.programa}.${partida.sector_informacion.proyecto}`,
        //   sector_nombre = partida.sector_informacion.nombre,
        //   partida_codigo = partida.partida,
        //   nombre = partida.nombre || 'No asignado',
        //   descripcion = partida.descripcion,
        let monto = partida.monto

        montoTotalActividad += Number(monto)

        // return `<tr class=''>
        //             <td>${sector_nombre}</td>
        //             <td>${sector_codigo}</td>
        //             <td>${partida_codigo}</td>
        //             <td>${actividad_codigo}</td>
        //             <td>${monto}</td>
        //         </tr>`
      })

      montoTotalDistribuido += montoTotalActividad

      return `<tr class=''>
      <td>${actividad_nombre}</td>
      <td>${actividad_codigo}</td>

      <td>${montoTotalActividad}</td>
      <td>${status}</td>
      <td>
     ${acciones}
      
      </td>
  </tr>`
    })

    let tabla = `<div class='row'>
        <div class='col'>
          <div class='d-flex justify-content-between mb-4'>
            <h4 class='text-green-800 text-center'>
              Asignaciones a las actividades del ente:
            </h4>
          </div>
          <h6>Monto total asignado: ${montoTotalDistribuido}</h6>
        </div>

        <table
          id='asignacion-part1-table'
          class='table table-striped table-sm'
          style='width:100%'
        >
          <thead class='w-100'>
            <th>NOMBRE</th>
            <th>ACTIVIDAD</th>
            
            <th>MONTO</th>
            <th>ESTATUS</th>
            <th>ACCIONES</th>
          </thead>

          <tbody>${filas.join('')}</tbody>
        </table>
      </div>`

    return tabla
  }

  // GENERAR LISTA DE CHECKBOX DE LAS ACTIVIDADES LIGADAS AL ENTE

  const dependenciasEnteList = () => {
    let dependenciasList = asignacion.dependencias.filter(
      (dependencia) =>
        !datosDistribucionActividades.some(
          (el) => Number(el.actividad_id) === Number(dependencia.id)
        )
    )

    let liItems
    // SI YA HAY DISTRIBUCIONES REGISTRADAS, ENTONCES CARGAR LAS ACTIVIDADES LAS CUALES TIENEN UNA DISTRIBUCION

    if (asignacion.actividades_entes.length > 0) {
      liItems = asignacion.actividades_entes.map((dependencia) => {
        return `  <div class='form-check'>
        <input
          class='form-check-input'
          type='checkbox'
          value='${dependencia.actividad_id}'
          data-dependencia="${dependencia.actividad_id}"
          name='ente-dependencia'
          id='ente-dependencia-check-${dependencia.actividad_id}'
          checked
          disabled
        />
        <label
          class='form-check-label'
          for='ente-dependencia-check-${dependencia.actividad_id}'
        >
          ${dependencia.actividad} - ${dependencia.ente_nombre}
        </label>
      </div>`
      })

      return `<h4 class='text-blue-800'>Actividades de ente:</h4> ${liItems.join(
        ''
      )}`
    }

    // PARA ACTUALIZAR A MEDIDA DE QUE SE VAYA REALIZANDO LA DISTRIBUCION

    if (dependenciasList && dependenciasList.length > 0) {
      if (Number(asignacion.status) === 1) {
        liItems = dependenciasList.map((dependencia) => {
          return `  <div class='form-check'>
              <input
                class='form-check-input'
                type='checkbox'
                value='${dependencia.id}'
                data-dependencia="${dependencia.id}"
                name='ente-dependencia'
                id='ente-dependencia-check-${dependencia.id}'
                checked
                disabled
              />
              <label
                class='form-check-label'
                for='ente-dependencia-check-${dependencia.id}'
              >
                ${dependencia.actividad} - ${dependencia.ente_nombre}
              </label>
            </div>`
        })
      } else {
        liItems = dependenciasList.map((dependencia) => {
          return `  <div class='form-check'>
              <input
                class='form-check-input'
                type='radio'
                value='${dependencia.id}'
                data-dependencia="${dependencia.id}"
                name='ente-dependencia'
                id='ente-dependencia-check-${dependencia.id}'
              />
              <label
                class='form-check-label'
                for='ente-dependencia-check-${dependencia.id}'
              >
                ${dependencia.actividad} - ${dependencia.ente_nombre}
              </label>
            </div>`
        })
      }

      return `<h4 class='text-blue-800'>Actividades de ente:</h4> ${liItems.join(
        ''
      )}`
    } else {
      return `<h4 class='text-red-800'>Sin dependencias a usar</h4>`
    }
  }

  // GENERAR CARD PRINCIPAL DONDE SE CARGARÍA LA INFORMACIÓN DE ASIGNACIÓN O DISTRIBUCIÓN DE PARTIDAS DEL ENTE

  const planEnte = async () => {
    let tipo = asignacion.tipo_ente
    return `<div id='card-body-part1' class='slide-up-animation'>
        <div class='row'>
          <div class='col'>
            <h4 class='text-blue-800'>Información sobre asignación:</h4>
            <h6>Nombre: ${asignacion.ente_nombre || 'Ente sin nombre'}</h6>
            <h6>
              Tipo: ${tipo === 'J' ? 'juridico' : 'Descentralizado'}
            </h6>
            <h6>
              Monto total asignado: ${separarMiles(asignacion.monto_total)}
            </h6>
            ${
              montos.distribuido_total > 0
                ? `<h6>
              Monto total distribuido: ${separarMiles(montos.distribuido_total)}
            </h6>`
                : ''
            }
           
          </div>
          ${
            asignacion.dependencias.length > 0
              ? `<div class='col'>${dependenciasEnteList()}</div>`
              : ''
          }
          
        </div>
        <hr/>
        ${
          asignacion.actividades_entes.length > 0
            ? distribucionPartidasEnteList({
                info: asignacion.actividades_entes,
              })
            : datosDistribucionActividades.length > 0
            ? distribucionPartidasEnteList({ info: relacionarActividades() })
            : `<div>
              <h4 class='text-center text-blue-800'>
                Esta asignación no posee distribución de partidas.
              </h4>
              <h4 class='text-center text-blue-800'>
                Proceda a realizar la distribución.
              </h4>
            </div>`
        }
      </div>`
  }

  // PARTE 2: ASIGNAR MONTOS A PARTIDAS

  const partidasSeleccionadasList = (partidasRelacionadas) => {
    let liItems = partidasRelacionadas
      .filter((distribucion) => Number(distribucion.monto_disponible) > 0)
      .map((distribucion) => {
        return `  <tr>
          <td>${distribucion.sector}</td>
          <td>${distribucion.programa}</td>
          <td>${distribucion.proyecto}</td>
          <td>${distribucion.actividad}</td>
          <td>
          <input
          class='form-control partida-input partida-monto-disponible'
          type='text'
          data-valorinicial='${distribucion.monto_disponible}'
          data-id='${distribucion.id}'
          name='partida-monto-disponible-${distribucion.id}'
          id='partida-monto-disponible-${distribucion.id}'
          placeholder='Monto a asignar...'
          value="${separarMiles(distribucion.monto_disponible)}"

          disabled
        />
</td>
        </tr>`
      })

    return liItems.join('')
  }

  function relacionarPartidas() {
    let dependenciaActividad, dependenciaNombre, actividad_id

    if (dependenciaEnteSeleccionada) {
      dependenciaActividad = dependenciaEnteSeleccionada.actividad
      dependenciaNombre = dependenciaEnteSeleccionada.ente_nombre
      actividad_id = null
    } else {
      dependenciaActividad = '-'
      dependenciaNombre = '-'
    }

    let ente_sector = Number(asignacion.sector)
    let ente_programa = Number(asignacion.programa)

    let partidasRelacionadas = ejercicioFiscal.distribucion_partidas.filter(
      (partida) => {
        return (
          Number(partida.sector_informacion.sector) === ente_sector &&
          (Number(partida.programa_informacion.programa) === ente_programa) &
            (Number(partida.id_actividad) === Number(dependenciaActividad))
        )
        //
      }
    )

    if (partidasRelacionadas.length === 0) {
      return false
    }

    return partidasRelacionadas.map((el) => {
      let sector = `${
        el.sector_informacion
          ? el.sector_informacion.sector
          : 'Sector no disponible'
      }`
      let programa = `${
        el.programa_informacion
          ? el.programa_informacion.programa
          : 'Programa no disponible'
      }`
      let proyecto = `${
        el.proyecto_informacion == 0
          ? '00'
          : el.proyecto_informacion.proyecto_id
      }`

      return {
        id: el.id,
        actividad_id,
        sector,
        programa,
        proyecto,
        actividad: dependenciaActividad,
        actividad_nombre: dependenciaNombre,
        partida: el.partida,
        nombre: el.nombre || 'No asignado',
        descripcion: el.descripcion,
        monto_disponible: disponibilidadPartida[`distribucion-${el.id}`],
      }
    })
    // let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
    //   (partida) => partida.id == id
    // )

    // console.log(datosDistribucionActividades)
  }

  function relacionarActividades() {
    let dataRelacionada = datosDistribucionActividades.map((data) => {
      let {
        id_ente,
        actividad_id,
        distribuciones,
        id_ejercicio,
        id_asignacion,
      } = data
      let actividadEncontrada = asignacion.dependencias.find(
        (dependencia) => Number(dependencia.id) === Number(actividad_id)
      )

      // let monto_asignado = 0

      // distribuciones.forEach((distribucionPartida) => {
      //   monto_asignado += Number(distribucionPartida.monto)
      // })

      // console.log(monto_asignado)

      return {
        id_ejercicio,
        actividad_id: actividadEncontrada
          ? Number(actividadEncontrada.id)
          : null,
        id_ente,
        actividad: actividadEncontrada ? actividadEncontrada.actividad : '-',
        ente_nombre: actividadEncontrada
          ? actividadEncontrada.ente_nombre
          : '-',
        distribucion_partidas: distribuciones,
      }
    })

    return dataRelacionada
  }

  const asignarMontoPartidas = (partidasRelacionadas) => {
    return ` <div id='card-body-part2' class='slide-up-animation'>
        <h4 class='text-center text-info'>Distribución presupuestaria:</h4>

        <div class='row align-items-center text-center'>
          <div class='col'>
            <h6>Ejercicio: ${separarMiles(montos.total)}</h6>
            <h6>Restante: ${separarMiles(montos.restante)}</h6>
            <h6>Distribuido: ${separarMiles(montos.distribuido)}</h6>
          </div>
          <div class='col'>
            
            <h6>
              Asignación total: <b id=''>${asignacion.monto_total}</b>
            </h6>
            <h6>
              Distribución presupuestaria actual: <b id='monto-total-asignado'><span class="p-2 text-secondary">0</span></b>
            </h6>
            <h6>
            Total distribuido: <b id='monto-total-distribuido'><span class="p-2 text-secondary">${
              montos.distribuido_total
            }</span></b>
          </h6>
          </div>
          <div class='col'>
            <h6>Nombre: ${asignacion.ente_nombre}</h6>
            <h6>Tipo: ${
              asignacion.tipo_ente === 'J' ? 'Jurídico' : 'Descentralizado'
            }</h6>
            
          </div>
          
        </div>

      
        <table
          id='asignacion-part4-table'
          class='table table-striped table-sm'
          style='width:100%'
        >
          <thead class='w-100'>
            <th>SECTOR</th>
            <th>PROGRAMA</th>
            <th>PROYECTO</th>
            <th>ACTIVIDAD</th>
            
            <th>ASIGNACION</th>
          </thead>

          <tbody>${partidasSeleccionadasList(partidasRelacionadas)}</tbody>
        </table>
      </div>`
  }

  const validarFooter = () => {
    if (asignacion.actividades_entes.length > 0) {
      let distribucionesEstanPendientes = asignacion.actividades_entes.every(
        (distribucion) => Number(distribucion.status) === 0
      )
      let distribucionesEstanAprobadas = asignacion.actividades_entes.every(
        (distribucion) => Number(distribucion.status) === 1
      )
      let distribucionesEstanRechazadas = asignacion.actividades_entes.every(
        (distribucion) => Number(distribucion.status) === 2
      )

      if (distribucionesEstanAprobadas) {
        return `<span class='btn btn-success'>Esta distribucion fue aprobada</span>`
      }
      if (distribucionesEstanRechazadas) {
        return `<span class='btn btn-danger'>Esta distribucion fue rechazada</span>`
      }

      if (distribucionesEstanPendientes) {
        return `<button class='btn btn-primary' id='distribucion-ente-aceptar'>
        Aceptar distribuciones
      </button>
      <button class='btn btn-danger' id='distribucion-ente-rechazar'>
        Rechazar
      </button>`
      }
    } else {
      return ` <button class='btn btn-secondary' id='btn-previus' disabled>
      Atrás
    </button>
    <button class='btn btn-primary' id='btn-next'>
      Siguiente
    </button>
    <button class='btn btn-success d-none' id='btn-add'>
      Añadir
    </button>
    
    <button class='btn btn-success d-none' id='btn-send'>
      Añadir
    </button>
    `
    }
  }

  let card = ` <div class='card slide-up-animation' id='asignacion-entes-form-card'>
      <div class='card-header d-flex justify-content-between'>
        <div class=''>
          <h5 class='mb-0'>Validar información de asignación presupuestaria</h5>
          <small class='mt-0 text-muted'>
            Información del ente y su distribución presupuestaria
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
        ${validarFooter()}
      </div>
    </div>`

  d.getElementById(elementToInset).insertAdjacentHTML('afterbegin', card)

  let cardBody = d.getElementById('card-body-container')

  // INICIALIZAR CARD

  cardBody.innerHTML = await planEnte()
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

  async function validateClick(e) {
    if (e.target.dataset.close) {
      closeCard()
    }

    if (e.target.closest('[data-distribuciondetalleid]')) {
      let id = Number(e.target.closest('button').dataset.distribuciondetalleid)

      let distribucionEncontrada = datosDistribucionActividades.find(
        (el) => Number(el.actividad_id) === id
      )

      let dependenciaEnteEncontrada = asignacion.dependencias.find(
        (el) => Number(el.id) === id
      )

      let detallesDatos = distribucionEncontrada.distribuciones.map((el) => {
        let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
          (partida) => Number(partida.id) === Number(el.id_distribucion)
        )

        console.log(partidaEncontrada)

        let sector = `${
          partidaEncontrada.sector_informacion
            ? partidaEncontrada.sector_informacion.sector
            : 'Sector no disponible'
        }`
        let programa = `${
          partidaEncontrada.programa_informacion
            ? partidaEncontrada.programa_informacion.programa
            : 'Programa no disponible'
        }`
        let proyecto = `${
          partidaEncontrada.proyecto_informacion == 0
            ? '00'
            : partidaEncontrada.proyecto_informacion.proyecto_id
        }`

        let partida = partidaEncontrada.partida

        return { sector, programa, proyecto, partida, monto: el.monto }
      })

      let informacion = {
        ente_nombre: dependenciaEnteEncontrada.ente_nombre,
        sectorActividad: dependenciaEnteEncontrada.sector,
        programaActividad: dependenciaEnteEncontrada.programa,
        proyectoActividad: dependenciaEnteEncontrada.proyecto,
        actividad: dependenciaEnteEncontrada.actividad,
        distribuciones: detallesDatos,
        monto_total: distribucionEncontrada.monto_total_asignado,
      }

      console.log(informacion)

      form_distribucion_entes_card({
        elementToInsert: 'asignacion-entes-view',
        informacion: informacion,
      })
    }

    if (e.target.dataset.eliminaractividadid) {
      // ACTUALIZAR TOTAL DISTRIBUIDO

      let distribucionRestar = datosDistribucionActividades.find(
        (el) =>
          Number(el.actividad_id) ===
          Number(e.target.dataset.eliminaractividadid)
      )

      montos.distribuido_total -= distribucionRestar.monto_total_asignado

      // ACTUALIZAR MONTO DISPONIBLE DE DISTRIBUCIONES
      distribucionRestar.distribuciones.forEach((el) => {
        disponibilidadPartida[`distribucion-${el.id_distribucion}`] += Number(
          el.monto
        )
      })

      if (asignacion.tipo_ente === 'D') {
        datosDistribucionActividades = []
      } else {
        datosDistribucionActividades = datosDistribucionActividades.filter(
          (el) => {
            return (
              Number(el.actividad_id) !==
              Number(e.target.dataset.eliminaractividadid)
            )
          }
        )
      }

      console.log(montos)

      // ELIMINAR TABLA Y ACTUALIZAR CARD

      cardBody.innerHTML = await planEnte()
      validarPartidasEntesTable()
    }

    if (e.target.id === 'btn-send') {
      if (datosDistribucionActividades.length > 0) {
        enviarInformacion(datosDistribucionActividades)
      } else {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Tienes que realizar al menos una distribución en este ente',
        })
      }
    }
    if (e.target.id === 'distribucion-ente-aceptar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: `¿Desea aceptar esta distribución de presupuesto?`,
        successFunction: async function () {
          let res = await aceptarDistribucionEnte({
            id: asignacion.id,
          })

          if (res.success) {
            closeCard()
            loadAsignacionEntesTable(ejercicioFiscal.id)
          }
        },
      })
    }

    if (e.target.id === 'distribucion-ente-rechazar') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: `¿Desea rechazar esta distribución de presupuesto?`,
        successFunction: async function () {
          let res = await rechazarDistribucionEnte({
            id: asignacion.id,
          })

          if (res.success) {
            closeCard()
            loadAsignacionEntesTable(ejercicioFiscal.id)
          }
        },
      })
    }

    validateFormFocus(e)
  }
  async function validateInputFunction(e) {
    if (e.target.dataset.dependencia) {
      let dependencia = asignacion.dependencias.find(
        (el) => Number(el.id) === Number(e.target.value)
      )
      dependenciaEnteSeleccionada = dependencia
    }
    // if (e.target.classList.contains('partida-monto')) {
    //   let montoDisponibleInput = d.getElementById(
    //     `partida-monto-disponible-${e.target.dataset.id}`
    //   )

    //   if (
    //     Number(montoDisponibleInput.dataset.valorinicial) <
    //     Number(formatearFloat(e.target.value))
    //   ) {
    //     toastNotification({
    //       type: NOTIFICATIONS_TYPES.fail,
    //       message: 'Esta partida ya no posee disponibilidad presupuestaria',
    //     })

    //     e.target.value = separarMiles(montoDisponibleInput.dataset.valorinicial)
    //   }

    //   montoDisponibleInput.value = separarMiles(
    //     Number(montoDisponibleInput.dataset.valorinicial) -
    //       Number(formatearFloat(e.target.value))
    //   )

    //   actualizarMontoRestante()
    // }
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    console.log(montos)

    if (montos.distribuido_total < Number(montos.total_asignado)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Monto distribuido es menor al monto asignado al ente',
      })
      return
    }

    confirmNotification({
      type: NOTIFICATIONS_TYPES.send,
      message: '¿Desea registrar esta distribución presupuestaria?',
      successFunction: async function () {
        let res = await enviarDistribucionPresupuestariaEntes({
          data: data,
        })
        if (res.success) {
          closeCard()
          actualizar()
        }
      },
    })
  }

  function formFocusPart1() {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')
    let btnSend = d.getElementById('btn-send')
    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part1')
    let cardBodyPart2 = d.getElementById('card-body-part2')

    if (cardBodyPart2) cardBodyPart2.remove()

    if (ejercicioFiscal.distribucion_partidas.length < 1) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message:
          'El ejercicio fiscal actual no posee una distribución de partidas',
      })
      return
    }

    if (!dependenciaEnteSeleccionada && asignacion.dependencias.length > 0) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Seleccione la actividad a asignar monto',
      })
      return
    }

    let partidasRelacionadas = relacionarPartidas()

    console.log(partidasRelacionadas)
    if (!partidasRelacionadas) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Esta actividad no posee una distribución',
      })
      return
    }

    cardBodyPart1.classList.add('d-none')
    cardBody.innerHTML += asignarMontoPartidas(partidasRelacionadas)
    validarAsignacionPartidasTable()

    formFocus++
    btnPrevius.classList.remove('d-none')
    btnSend.classList.add('d-none')
    btnPrevius.removeAttribute('disabled')
    // btnAdd.classList.remove('d-none')
  }

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')
    let btnSend = d.getElementById('btn-send')
    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part1')
    let cardBodyPart2 = d.getElementById('card-body-part2')

    if (e.target === btnNext) {
      scroll(0, 0)
      if (formFocus === 1) {
        if (
          asignacion.dependencias.length ===
            datosDistribucionActividades.length &&
          datosDistribucionActividades.length > 0
        ) {
          enviarInformacion(datosDistribucionActividades)
          return
        }

        if (
          asignacion.tipo_ente === 'D' &&
          datosDistribucionActividades.length > 0
        ) {
          enviarInformacion(datosDistribucionActividades)
          return
        }

        formFocusPart1()

        return
      }
      if (formFocus === 2) {
        let inputsPartidas = d.querySelectorAll('.partida-monto-disponible')

        let monto_total_asignado = 0

        let mappedInfo = Array.from(inputsPartidas).map((input) => {
          let id_distribucion = input.dataset.id
          let monto = Number(input.dataset.valorinicial)

          monto_total_asignado += monto

          return { id_distribucion, monto }
        })

        montos.distribuido_total += monto_total_asignado

        console.log(disponibilidadPartida)

        let data = {
          id_ente: asignacion.id_ente,
          actividad_id: dependenciaEnteSeleccionada
            ? dependenciaEnteSeleccionada.id
            : dependenciaEnteSeleccionada,
          distribuciones: mappedInfo,
          id_ejercicio: ejercicioFiscal.id,
          id_asignacion: asignacion.id,
          monto_total_asignado,
        }

        datosDistribucionActividades.push(data)

        cardBody.innerHTML = await planEnte()

        dependenciaEnteSeleccionada = null
        formFocus = 1
        btnSend.classList.remove('d-none')

        toastNotification({
          type: NOTIFICATIONS_TYPES.done,
          message: 'Distribución a actividad añadida',
        })

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
            cardBodyPart2.remove()

            cardBodyPart1.classList.remove('d-block')
            cardBodyPart1.classList.add('d-none')
            btnNext.textContent = 'Siguiente'
            // btnAdd.classList.remove('d-none')

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
        // btnAdd.classList.add('d-none')

        cardBodyPart2.remove()

        cardBodyPart1.classList.remove('d-none')
        dependenciaEnteSeleccionada = null
        formFocus--
        return
      }
    }
  }

  function actualizarMontoRestante() {
    let montoElement = d.getElementById('monto-total-asignado')
    let montoDistribuidoTotalElement = d.getElementById(
      'monto-total-distribuido'
    )

    let inputsPartidasMontos = d.querySelectorAll('.partida-monto')

    // REINICIAR MONTO ACUMULADO
    montos.acumulado = montos.distribuido_total

    inputsPartidasMontos.forEach((input) => {
      montos.acumulado += Number(input.value)
    })

    let diferenciaSolicitado =
      Number(montos.total_asignado) - Number(montos.acumulado)

    montoDistribuidoTotalElement.innerHTML = montos.acumulado

    if (montos.acumulado > montos.distribuido) {
      montoElement.innerHTML = `<span class="text-danger">${montos.acumulado}</span>`
      return
    }

    // console.log(diferenciaSolicitado)

    if (diferenciaSolicitado < 0) {
      montoElement.innerHTML = `<span class="px-2 rounded text-red-600 bg-red-100">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado > 0) {
      montoElement.innerHTML = `<span class="px-2 rounded text-green-600 bg-green-100">${montos.acumulado}</span>`
      return
    }
    if (diferenciaSolicitado === 0) {
      montoElement.innerHTML = `<span class="class="px-2 rounded text-secondary">${montos.acumulado}</span>`
      return
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
            <h5 class="text-center mb-0">Distribución de partidas del ente:</h5>
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
    responsive: false,
    colums: [
      { data: 'elegir' },
      { data: 'sector_nombre' },
      { data: 'sector_codigo' },
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
        <h5 class="text-center text-blue-800">Partidas seleccionadas: <b id="partidas-seleccionadas">0</b></h5>
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
}

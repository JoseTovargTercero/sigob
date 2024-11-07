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

  // PARA VALIDAR INPUTS DE PARTIDAS
  let fieldListPartidas = {}
  let fieldListErrorsPartidas = {}

  // PARA GUARDAR PARTIDAS SELECCIONADAS
  let partidasSeleccionadas = []
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

  let partidas = await getFormPartidas()

  console.log(ejercicioFiscal)
  console.log(asignacion)

  let montos = { total: 0, restante: 0, acumulado: 0, distribuido_total: 0 }

  montos.total = ejercicioFiscal.situado
  montos.restante = ejercicioFiscal.restante
  montos.distribuido = ejercicioFiscal.distribuido
  montos.total_asignado = asignacion.monto_total

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

  //

  function recortarTexto(str, length) {
    let texto = str.length < length ? str : `${str.slice(0, length)} ...`

    return texto
  }

  const distribucionPartidasEnteCheckbox = ({ partidasList, checkbox }) => {
    let dependenciaActividad, dependenciaNombre

    if (dependenciaEnteSeleccionada) {
      dependenciaActividad = dependenciaEnteSeleccionada.actividad
      dependenciaNombre = dependenciaEnteSeleccionada.ente_nombre
    } else {
      dependenciaActividad = '-'
      dependenciaNombre = '-'
    }

    let liItems = partidasList.map((distribucion) => {
      let sector = `${
        distribucion.sector_informacion
          ? distribucion.sector_informacion.sector
          : 'Sector no disponible'
      }`
      let programa = `${
        distribucion.programa_informacion
          ? distribucion.programa_informacion.programa
          : 'Programa no disponible'
      }`
      let proyecto = `${
        distribucion.proyecto_informacion == 0
          ? '00'
          : distribucion.proyecto_informacion.proyecto_id
      }`

      let descripcion = recortarTexto(distribucion.descripcion, 40)

      return ` <tr class=''>
    <td><input type="checkbox" class="form-check-input input-check" value="${distribucion.id}" name="ente-partida-${distribucion.id}"/></td>
    <td>${sector}</td>
    <td>${programa}</td>
    <td>${proyecto}</td>
    <td>${distribucion.partida}</td>
    
  </tr>`
    })

    return liItems.join('')
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

  // GENERAR TABLAS DINÁMICAS SEGÚN SE REQUIERA PARA QUE EL USUARIO PUEDA VISUALIZAR LO QUE EST´HACIENDO CON LAS ACTIVIDADES

  const distribucionEntesActividadList = (arr) => {
    let dataRelacionada = arr.map((data) => {
      let actividadEncontrada = asignacion.dependencias.find(
        (dependencia) => Number(dependencia.id) === Number(data.actividad_id)
      )

      let distribucion = data.distribuciones.map((distribucionPartida) => {
        let distribucionPartidaEncontrada =
          ejercicioFiscal.distribucion_partidas.find(
            (partida) => partida.id == distribucionPartida.id_distribucion
          )

        distribucionPartidaEncontrada.monto_asignado = distribucionPartida.monto

        return distribucionPartidaEncontrada
      })

      return {
        actividad_id: actividadEncontrada.id,
        actividad: actividadEncontrada.actividad,
        actividad_nombre: actividadEncontrada.ente_nombre,
        distribucion,
      }
    })

    let tablas = dataRelacionada.map((data) => {
      let actividad = data.actividad,
        actividad_nombre = data.actividad_nombre

      let montoTotalActividad = 0

      let rows = data.distribucion.map((partida) => {
        let sector_codigo = `${partida.sector_informacion.sector}.${partida.sector_informacion.programa}.${partida.sector_informacion.proyecto}`,
          sector_nombre = partida.sector_informacion.nombre,
          partida_codigo = partida.partida,
          nombre = partida.nombre || 'No asignado',
          descripcion = partida.descripcion,
          monto = partida.monto_asignado

        montoTotalActividad += monto

        return `<tr class=''>
                  <td>${sector_nombre}</td>
                  <td>${sector_codigo}</td>
                  <td>${partida_codigo}</td>
                  <td>${data.actividad}</td>
                  <td>${monto}</td>
              </tr>`
      })

      let tabla = ` <div class='row p-4 border bg-gray-100 rounded'>
          <div class='col'>
            <h4 class='text-green-800 text-center'>Distribución de partidas para actividad:</h4>
            <h6>
              Actividad: ${actividad} - ${actividad_nombre}
            </h6>
            <h6>
              Monto total asignado: ${montoTotalActividad}
            </h6>
          </div>

          <table
            id='distribucion-partidas-tabla-${data.actividad_id}'
            class='table table-striped table-sm'
            style='width:100%'
          >
            <thead class='w-100'>
              <th>SECTOR NOMBRE</th>
              <th>SECTOR CODIGO</th>
              <th>PARTIDA</th>
              <th>ACTIVIDAD</th>
              <th>MONTO</th>
            </thead>

            <tbody>${rows.join('')}</tbody>
          </table>
        </div>`

      return tabla
    })

    return tablas
  }

  // function validarTablas(tablas) {
  //   tablas.forEach((el, index) => {
  //     let planesTable = new DataTable(
  //       `#distribucion-partidas-tabla-${el.actividad_id}`,
  //       {
  //         scrollY: 100,
  //         language: tableLanguage,
  //         layout: {
  //           topStart: function () {
  //             let toolbar = document.createElement('div')
  //             toolbar.innerHTML = `
  //               <h5 class="text-center mb-0">Partidas seleccionadas:</h5>
  //                         `
  //             return toolbar
  //           },
  //           topEnd: { search: { placeholder: 'Buscar...' } },
  //           bottomStart: 'info',
  //           bottomEnd: 'paging',
  //         },
  //       }
  //     )
  //   })
  // }

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

      console.log(liItems)

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

  // PARTE 2

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
    <h4 class="text-center">Seleccione las partidas a distribuir:</h4>
    <h5 class="text-center">Se cargaran las partidas ya distribuidas del ejercicio fiscal:</h5>
    <h6 class="text-center">Si desea añadir nuevas partidas primero registre nuevas partidas en la distribución anual.</h6>
        
          <div class=''>
            <table
              id='asignacion-part3-table'
              class='table table-striped table-sm'
              style='width:100%'
            >
              <thead class='w-100'>
                <th>ELEGIR</th>
                <th>SECTOR</th>
                <th>PROGRAMA</th>
                <th>PROYECTO</th>
                <th>PARTIDA</th>
                
              </thead>
              ${
                ejercicioFiscal.distribucion_partidas
                  ? `<tbody>${distribucionPartidasEnteCheckbox({
                      checkbox: true,
                      partidasList: ejercicioFiscal.distribucion_partidas,
                    })}</tbody>`
                  : `<tbody></tbody>`
              }
              
            </table>
          </div>
        
          ${formularioNuevaPartida()}
      </div>`
  }

  // PARTE 3: ASIGNAR MONTOS A PARTIDAS

  const partidasSeleccionadasList = () => {
    let partidasRelacionadas = relacionarPartidas()

    let liItems = partidasRelacionadas.map((distribucion) => {
      fieldListPartidas[`partida-monto-${distribucion.id}`] = ''
      fieldListErrorsPartidas[`partida-monto-${distribucion.id}`] = {
        value: true,
        message: 'Monto inválido',
        type: 'textarea',
      }

      console.log(distribucion)

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
          name='partida-monto-disponible-${distribucion.id}'
          id='partida-monto-disponible-${distribucion.id}'
          placeholder='Monto a asignar...'
          value="${distribucion.monto_disponible}"

          disabled
        />
</td>
          
          <td>
            <input
              class='form-control partida-input partida-monto'
              type='number'
              data-id='${distribucion.id}'
              name='partida-monto-${distribucion.id}'
              id='partida-monto-${distribucion.id}'
              placeholder='Monto a asignar...'
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

    let partidasRelacionadas = partidasSeleccionadas.map((id) => {
      let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
        (partida) => partida.id == id
      )

      console.log(datosDistribucionActividades)

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

      return {
        id: id,
        actividad_id,
        sector,
        programa,
        proyecto,
        actividad: dependenciaActividad,
        actividad_nombre: dependenciaNombre,
        partida: partidaEncontrada.partida,
        nombre: partidaEncontrada.nombre || 'No asignado',
        descripcion: partidaEncontrada.descripcion,
        monto_disponible: disponibilidadPartida[`distribucion-${id}`],
      }
    })

    console.log(partidasRelacionadas)

    return partidasRelacionadas
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

  const asignarMontoPartidas = () => {
    return ` <div id='card-body-part3' class='slide-up-animation'>
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
            Distribución presupuestaria actual: <b id='monto-total-distribuido'><span class="p-2 text-secondary">${
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
            <th>DISPONIBILIDAD</th>
            <th>ASIGNACION</th>
          </thead>

          <tbody>${partidasSeleccionadasList()}</tbody>
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
      console.log()

      let id = Number(e.target.closest('button').dataset.distribuciondetalleid)

      let distribucionEncontrada = datosDistribucionActividades.find(
        (el) => Number(el.actividad_id) === id
      )

      let dependenciaEnteEncontrada = asignacion.dependencias.find(
        (el) => Number(el.id) === id
      )
      console.log(dependenciaEnteEncontrada)

      let detallesDatos = distribucionEncontrada.distribuciones.map((el) => {
        let partidaEncontrada = ejercicioFiscal.distribucion_partidas.find(
          (partida) => Number(partida.id) === Number(el.id_distribucion)
        )

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
      console.log(datosDistribucionActividades)

      // ACTUALIZAR TOTAL DISTRIBUIDO

      let distribucionRestar = datosDistribucionActividades.find(
        (el) =>
          Number(el.actividad_id) ===
          Number(e.target.dataset.eliminaractividadid)
      )
      console.log(distribucionRestar)

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

    // if (e.target.id === 'btn-add') {
    //   d.getElementById('form-nueva-partida').classList.remove('d-none')
    // }
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
      // validarCheckboxRepetido(e)
      // ALMACENAR PARTIDAS PARA LUEGO ASIGNAR MONTO
      partidasSeleccionadas = obtenerValorCheckbox({
        id_card: 'card-body-part2',
        id_text: 'partidas-seleccionadas',
      })
      console.log(partidasSeleccionadas)
    }

    if (e.target.dataset.dependencia) {
      let dependencia = asignacion.dependencias.find(
        (el) => Number(el.id) === Number(e.target.value)
      )
      dependenciaEnteSeleccionada = dependencia
      console.log(dependenciaEnteSeleccionada)
    }
    if (e.target.classList.contains('partida-monto')) {
      fieldListPartidas = validateInput({
        target: e.target,
        fieldList: fieldListPartidas,
        fieldListErrors: fieldListErrorsPartidas,
        type: fieldListErrorsPartidas[e.target.name].type,
      })

      let montoDisponibleInput = d.getElementById(
        `partida-monto-disponible-${e.target.dataset.id}`
      )

      console.log(e.target.value)

      if (
        Number(montoDisponibleInput.dataset.valorinicial) <
        Number(e.target.value)
      ) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Esta partida ya no posee disponibilidad presupuestaria',
        })

        e.target.value = montoDisponibleInput.dataset.valorinicial
      }

      montoDisponibleInput.value =
        Number(montoDisponibleInput.dataset.valorinicial) -
        Number(e.target.value)

      actualizarMontoRestante()
    }
  }

  // CARGAR LISTA DE PARTIDAS

  function enviarInformacion(data) {
    if (montos.distribuido_total < Number(montos.total_asignado)) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: 'Monto distribuido es menor al monto asignado al ente',
      })
      return
    }
    console.log(data)
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

  async function validateFormFocus(e) {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')
    let btnSend = d.getElementById('btn-send')
    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part1')
    let cardBodyPart2 = d.getElementById('card-body-part2')
    let cardBodyPart3 = d.getElementById('card-body-part3')

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
        // btnNext.textContent = 'Enviar'
        // btnAdd.classList.add('d-none')
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
            message: 'Debe asignar un monto a cada partida',
          })
          return
        }

        if (montos.acumulado > montos.distribuido) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message:
              'Se ha superado el límite de la distribución presupuestaria',
          })
          return
        }

        if (montos.acumulado > montos.total_asignado) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message:
              'Se ha superado el límite del total asignado. Reasigne el monto anual al ente para continuar',
          })
          return
        }

        let monto_total_asignado = 0

        let mappedInfo = Array.from(inputsPartidas).map((input) => {
          let id_distribucion = input.dataset.id
          let monto = Number(input.value)
          monto_total_asignado += monto

          if (
            disponibilidadPartida.hasOwnProperty(
              `distribucion-${id_distribucion}`
            )
          ) {
            disponibilidadPartida[`distribucion-${id_distribucion}`] -= monto
          }

          return { id_distribucion, monto }
        })

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

        // dependenciasList = dependenciasList.filter(
        //   (dependencia) =>
        //     !datosDistribucionActividades.some(
        //       (el) => Number(el.actividad_id) === Number(dependencia.id)
        //     )
        // )

        montos.distribuido_total = montos.acumulado

        // distribucionEntesActividadList(datosDistribucionActividades)
        cardBody.innerHTML = await planEnte()
        // validarTablas(datosDistribucionActividades)

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
            cardBodyPart3.remove()

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

  function formFocusPart1() {
    let btnNext = d.getElementById('btn-next')
    let btnPrevius = d.getElementById('btn-previus')
    let btnSend = d.getElementById('btn-send')
    // let btnAdd = d.getElementById('btn-add')
    let cardBodyPart1 = d.getElementById('card-body-part1')
    let cardBodyPart2 = d.getElementById('card-body-part2')
    let cardBodyPart3 = d.getElementById('card-body-part3')

    if (cardBodyPart3) cardBodyPart3.remove()

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

    cardBodyPart1.classList.add('d-none')

    cardBody.innerHTML += seleccionPartidas()
    validarSeleccionPartidasTable()

    formFocus++
    btnPrevius.classList.remove('d-none')
    btnSend.classList.add('d-none')
    btnPrevius.removeAttribute('disabled')
    // btnAdd.classList.remove('d-none')
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
        '[data-dependencia] input[type=checkbox]:checked'
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

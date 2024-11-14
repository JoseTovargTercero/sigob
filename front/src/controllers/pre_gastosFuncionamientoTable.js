import { getPartidas } from '../api/partidas.js'
import { getGastos, getTiposGastos } from '../api/pre_gastos.js'
import { getSolicitudesDozavos } from '../api/pre_solicitudesDozavos.js'

import {
  confirmNotification,
  separarMiles,
  toastNotification,
  validateInput,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

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
let gastosTable, tipoGastosTable
export async function validateGastosTable(id_ejercicio) {
  gastosTable = new DataTable('#gastos-table', {
    columns: [
      { data: 'compromiso' },
      { data: 'descripcion' },
      { data: 'tipo' },
      { data: 'monto' },
      { data: 'fecha' },
      { data: 'estado' },
      { data: 'acciones' },
    ],
    responsive: true,
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
                  `
        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadGastosTable(id_ejercicio)
}

export async function validateTiposGastosTable() {
  tipoGastosTable = new DataTable('#tipos-gastos-table', {
    columns: [
      { data: 'nombre' },

      { data: 'acciones' },
      // { data: 'partida_descripcion' },
    ],
    responsive: true,
    scrollY: 300,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
                  `
        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadTipoGastosTable()
}

export async function loadGastosTable({ id_ejercicio }) {
  let gastos = await getGastos()

  console.log('CARGÓO')

  if (!Array.isArray(gastos)) return

  if (!gastos || gastos.error) return

  let datosOrdenados = [...gastos].sort((a, b) => a.id - b.id)

  let data = datosOrdenados
    .filter((gastos) => Number(gastos.id_ejercicio) === Number(id_ejercicio))
    .map((gastos) => {
      // let sector_programa_proyecto = `${
      //   el.sector_informacion ? el.sector_informacion.sector : '0'
      // }.${el.programa_informacion ? el.programa_informacion.programa : '0'}.${
      //   el.proyecto_informacion == 0 ? '00' : el.proyecto_informacion.proyecto
      // }.${el.id_actividad == 0 ? '00' : el.id_actividad}`

      return {
        compromiso: gastos.compromiso || 'Pendiente',
        descripcion: gastos.descripcion_gasto,
        tipo: gastos.nombre_tipo_gasto,
        monto: `${separarMiles(gastos.monto_gasto)} Bs`,
        fecha: 'Pendiente',
        estado:
          Number(gastos.status_gasto) === 0
            ? ` <span class='btn btn-sm btn-secondary'>Pendiente</span>`
            : Number(gastos.status_gasto) === 1
            ? `<span class='btn btn-sm btn-success'>Procesado</span>`
            : `<span class='btn btn-sm btn-danger'>Rechazado</span>`,
        acciones:
          Number(gastos.status_gasto) === 0
            ? `<button class="btn btn-danger btn-sm" data-rechazarid="${gastos.id}">Rechazar</button>
      <button class="btn btn-info btn-sm" data-aceptarid="${gastos.id}">Aceptar</button>`
            : `Gasto procesado`,
      }
    })

  console.log(data)

  gastosTable.clear().draw()

  // console.log(datosOrdenados)
  gastosTable.rows.add(data).draw()
}

export async function loadTipoGastosTable() {
  let tipoGastos = await getTiposGastos()

  if (!Array.isArray(tipoGastos)) return

  if (!tipoGastos || tipoGastos.error) return

  console.log(tipoGastos)

  let datosOrdenados = [...tipoGastos].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((gastos) => {
    return {
      nombre: gastos.nombre,

      acciones: `<button class="btn btn-danger btn-sm" data-eliminarid="${gastos.id}">Eliminar</button>`,
      // partida_descripcion: partidaEncontrada.descripcion,
    }
  })

  tipoGastosTable.clear().draw()

  // console.log(datosOrdenados)
  tipoGastosTable.rows.add(data).draw()
}

export async function deleteGasto({ id, row }) {
  gastosTable.row(row).remove().draw()
}

export async function deleteTipoGasto({ id, row }) {
  tipoGastosTable.row(row).remove().draw()
}

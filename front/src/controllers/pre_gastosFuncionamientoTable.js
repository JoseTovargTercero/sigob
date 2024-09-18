import { getGastos } from '../api/pre_gastos.js'
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
let gastosTable
export async function validateGastosTable() {
  gastosTable = new DataTable('#gastos-table', {
    columns: [
      { data: 'numero_compromiso' },
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

  loadGastosTable()
}

export async function loadGastosTable() {
  let gastos = await getGastos()
  console.log(gastos)

  if (!Array.isArray(gastos)) return

  if (!gastos || gastos.error) return

  let datosOrdenados = [...gastos].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((gastos) => {
    return {
      numero_compromiso: gastos.numero_compromiso,
      descripcion: gastos.descripcion,
      tipo: gastos.tipo,
      monto: `${separarMiles(gastos.monto)} Bs`,
      fecha: gastos.fecha,
      estado:
        gastos.estado === 0
          ? ` <span class='btn btn-sm btn-warning'>Pendiente</span>`
          : `<span class='btn btn-sm btn-success'>Procesado</span>`,
      acciones:
        gastos.estado === 0
          ? `<button class="btn btn-danger btn-sm" data-detalleid="${gastos.id}">Rechazar</button>
      <button class="btn btn-info btn-sm" data-detalleid="${gastos.id}">Aceptar</button>`
          : ``,
    }
  })

  gastosTable.clear().draw()

  // console.log(datosOrdenados)
  gastosTable.rows.add(data).draw()
}

export async function deleteSolicitudDozeavo({ id, row }) {
  gastosTable.row(row).remove().draw()
}

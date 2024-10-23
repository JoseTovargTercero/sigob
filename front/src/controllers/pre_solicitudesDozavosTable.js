import { deleteCategoria, getCategorias } from '../api/categorias.js'
import { getSolicitudesDozavos } from '../api/pre_solicitudesDozavos.js'
import {
  actualizarTasa,
  actualizarTasaManual,
  obtenerHistorialTasa,
  obtenerTasa,
} from '../api/tasa..js'
import { nomTasaCard } from '../components/nom_tasa_card.js'
import {
  confirmNotification,
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
let solicitudesDozavosTable
export async function validateSolicitudesDozavosTable() {
  solicitudesDozavosTable = new DataTable('#solicitudes-dozavos-table', {
    columns: [
      { data: 'numero_orden' },
      { data: 'entes' },
      { data: 'numero_compromiso' },
      { data: 'descripcion' },
      { data: 'tipo' },
      { data: 'monto' },
      { data: 'fecha' },
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

  loadSolicitudesDozavosTable()
}

export async function loadSolicitudesDozavosTable() {
  let solicitudes = await getSolicitudesDozavos()
  console.log(solicitudes)

  if (!Array.isArray(solicitudes)) return

  if (!solicitudes || solicitudes.error) return

  let datosOrdenados = [...solicitudes].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((solicitud) => {
    return {
      numero_orden: solicitud.numero_orden,
      entes: solicitud.ente || 'Ente',
      numero_compromiso: solicitud.numero_compromiso,
      descripcion: solicitud.descripcion,
      tipo: solicitud.tipo || 'hola',
      monto: solicitud.monto,
      fecha: solicitud.fecha,
      acciones: ` <button class="btn btn-info btn-sm btn-view" data-detalleid="${solicitud.id}"><i class="bx bx-detail me-1"></i>Detalles</button>`,
    }
  })

  solicitudesDozavosTable.clear().draw()

  // console.log(datosOrdenados)
  solicitudesDozavosTable.rows.add(data).draw()
}

export async function deleteSolicitudDozeavoRow({ id, row }) {
  solicitudesDozavosTable.row(row).remove().draw()
}

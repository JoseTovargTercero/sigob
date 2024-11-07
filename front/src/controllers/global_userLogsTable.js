import { selectTables } from '../api/globalApi.js'
import { tableLanguage } from '../helpers/helpers.js'

const d = document

export const validateUserLogs = async () => {
  console.log('hola')
  // let btnNewElement = d.getElementById('titulo-1-registrar')

  validateUserLogsTable()
}

let userLogsTable
export const validateUserLogsTable = async () => {
  userLogsTable = new DataTable('#user-logs-table', {
    columns: [
      { data: 'usuario' },
      { data: 'tabla' },
      { data: 'accion' },
      { data: 'situacion' },
      { data: 'fecha' },
    ],
    responsive: true,
    scrollY: 350,
    language: tableLanguage,
    layout: {
      topEnd: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  loadUserLogsTable()
}

export const loadUserLogsTable = async () => {
  let logsData = await selectTables('audit_logs')

  console.log(logsData)

  if (!Array.isArray(logsData)) return

  if (!logsData || logsData.error) return

  let datosOrdenados = [...logsData].sort((a, b) => a.id - b.id)
  let data = datosOrdenados.map((el) => {
    return {
      usuario: el.user_id,
      tabla: el.table_name,
      accion: el.action_type,
      situacion: el.situation,
      fecha: el.timestamp,
    }
  })

  userLogsTable.clear().draw()

  // console.log(datosOrdenados)
  userLogsTable.rows.add(data).draw()
}

import { selectTables } from "../api/globalApi.js";
import { tableLanguage } from "../helpers/helpers.js";

const d = document;

export const validateUserLogs = async () => {
  // let btnNewElement = d.getElementById('titulo-1-registrar')

  validateUserLogsTable();
};

let userLogsTable;
export const validateUserLogsTable = async () => {
  userLogsTable = new DataTable("#user-logs-table", {
    columns: [
      { data: "usuario" },
      { data: "tabla" },
      { data: "accion" },
      { data: "situacion" },
      { data: "fecha" },
    ],
    responsive: true,
    scrollY: 350,
    language: tableLanguage,
    layout: {
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadUserLogsTable();
};

export const loadUserLogsTable = async () => {
  let logsData = await selectTables("audit_logs", "_to_users");

  //console.log(logsData);

  if (!Array.isArray(logsData)) return;

  if (!logsData || logsData.error) return;


  const actions_bg = {
    'DELETE': '<span class="badge bg-danger">Eliminar</span>',
    'UPDATE': '<span class="badge bg-warning">Actualizar</span>',
    'INSERT': '<span class="badge bg-success">Insertar</span>'
  }
    const desconocido_bg = '<span class="badge bg-secondary">Desconocido</span>';
    



  let datosOrdenados = [...logsData].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    return {
      usuario: el.u_nombre,
      tabla: el.table_name,
      accion: (actions_bg[el.action_type] ? actions_bg[el.action_type] : desconocido_bg),
      situacion: el.situation,
      fecha: el.timestamp,
    };
  });

  userLogsTable.clear().draw();

  userLogsTable.rows.add(data).draw();
};

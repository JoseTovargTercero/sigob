import { getFormPartidas } from "../api/partidas.js";
import { separarMiles } from "../helpers/helpers.js";

const tableLanguage = {
  decimal: "",
  emptyTable: "No hay datos disponibles en la tabla",
  info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
  infoFiltered: "(filtrado de _MAX_ entradas totales)",
  infoPostFix: "",
  thousands: ",",
  lengthMenu: "Mostrar _MENU_",
  loadingRecords: "Cargando...",
  processing: "",
  search: "Buscar:",
  zeroRecords: "No se encontraron registros coincidentes",
  paginate: {
    first: "Primera",
    last: "Última",
    next: "Siguiente",
    previous: "Anterior",
  },
  aria: {
    orderable: "Ordenar por esta columna",
    orderableReverse: "Orden inverso de esta columna",
  },
};

let distribucionTable;
export const validateDistribucionTable = async ({ partidas }) => {
  distribucionTable = new DataTable("#distribucion-table", {
    columns: [
      // { data: 'sector_nombre' },
      { data: "sector_programa_proyecto" },
      { data: "partida" },
      {
        data: "descripcion",
        render: function (data) {
          return `<div class="text-left">${data}</div>`;
        },
      },
      { data: "monto_inicial" },
      // { data: 'monto_actual' },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de partidas</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadDistribucionTable(partidas);
};

export const loadDistribucionTable = async (partidas) => {
  // let partidas = await getFormPartidas()

  if (!Array.isArray(partidas)) return;

  if (!partidas || partidas.error) return;

  let datosOrdenados = [...partidas].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    let descripcion =
      el.descripcion.length < 40
        ? el.descripcion
        : `${el.descripcion.slice(0, 40)} ...`;

    return {
      // sector_nombre: el.sector_inf[1].sector_informacion.sectorormacion.nombre,
      sector_programa_proyecto: `${
        el.sector_informacion ? el.sector_informacion.sector : "0"
      }.${el.programa_informacion ? el.programa_informacion.programa : "0"}.${
        el.proyecto_informacion == 0 ? "00" : el.proyecto_informacion.proyecto
      }.${el.id_actividad == 0 ? "00" : el.id_actividad}`,
      partida: el.partida,
      descripcion: descripcion,
      monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      // monto_actual: `${separarMiles(el.monto_actual)} Bs`,
      acciones: `
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });
  // <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>

  distribucionTable.clear().draw();

  // console.log(datosOrdenados)
  distribucionTable.rows.add(data).draw();
};

export async function deleteDistribucionRow({ id, row }) {
  distribucionTable.row(row).remove().draw();
}

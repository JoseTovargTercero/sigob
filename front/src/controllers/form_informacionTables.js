import {
  getConsejoData,
  getContraloriaData,
  getDescripcionProgramaData,
  getDirectivoData,
  getGobernacionData,
  getPersonaData,
  getTitulo1Data,
} from "../api/form_informacion.js";
import { getFormPartidas } from "../api/partidas.js";
import { recortarTexto, separarMiles } from "../helpers/helpers.js";

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

let gobernacionTable;
export const validateGobernacionTable = async () => {
  gobernacionTable = new DataTable("#gobernacion-table", {
    columns: [
      { data: "identificacion" },
      { data: "domicilio" },
      { data: "telefono" },
      { data: "pagina_web" },
      { data: "fax" },
      { data: "codigo_postal" },
      { data: "nombre_apellido_gobernador" },

      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros en la gobernación</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadGobernacionTable();
};

export const loadGobernacionTable = async () => {
  let gobernacionData = await getGobernacionData();

  if (!Array.isArray(gobernacionData.fullInfo)) return;

  if (!gobernacionData.fullInfo || gobernacionData.error) return;

  console.log(gobernacionData);

  let datosOrdenados = [...gobernacionData.fullInfo].sort(
    (a, b) => a.id - b.id
  );
  let data = datosOrdenados.map((el) => {
    return {
      identificacion: el.identificacion,
      domicilio: el.domicilio,
      telefono: el.telefono,
      pagina_web: el.pagina_web,
      fax: el.fax || "No asignado",
      codigo_postal: el.codigo_postal,
      nombre_apellido_gobernador: el.nombre_apellido_gobernador,

      // sector_nombre: el.sector_informacion.nombre,
      //   sector_cod: sector_codigo,
      //   partida: el.partida,
      //   descripcion: descripcion,
      //   monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      //   monto_actual: `${separarMiles(el.monto_actual)} Bs`,
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  gobernacionTable.clear().draw();

  // console.log(datosOrdenados)
  gobernacionTable.rows.add(data).draw();
};

let contraloriaTable;
export const validateContraloriaTable = async () => {
  contraloriaTable = new DataTable("#contraloria-table", {
    columns: [
      { data: "nombre_apellido_contralor" },
      { data: "domicilio" },
      { data: "telefono" },
      { data: "pagina_web" },
      { data: "email" },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros en la contraloria</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadContraloriaTable();
};

export const loadContraloriaTable = async () => {
  let contraloriaData = await getContraloriaData();

  if (!Array.isArray(contraloriaData.fullInfo)) return;

  if (!contraloriaData.fullInfo || contraloriaData.error) return;

  console.log(contraloriaData);

  let datosOrdenados = [...contraloriaData.fullInfo].sort(
    (a, b) => a.id - b.id
  );
  let data = datosOrdenados.map((el) => {
    return {
      nombre_apellido_contralor: el.nombre_apellido_contralor,
      domicilio: el.domicilio,
      telefono: el.telefono,
      pagina_web: el.pagina_web,
      email: el.email || "No asignado",
      // sector_nombre: el.sector_informacion.nombre,
      //   sector_cod: sector_codigo,
      //   partida: el.partida,
      //   descripcion: descripcion,
      //   monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      //   monto_actual: `${separarMiles(el.monto_actual)} Bs`,
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  contraloriaTable.clear().draw();

  // console.log(datosOrdenados)
  contraloriaTable.rows.add(data).draw();
};

let consejoTable;
export const validateConsejoTable = async () => {
  consejoTable = new DataTable("#consejo-table", {
    columns: [
      { data: "nombre_apellido_presidente" },
      { data: "nombre_apellido_secretario" },
      { data: "domicilio" },
      { data: "telefono" },
      { data: "pagina_web" },
      { data: "email" },
      { data: "consejo_local" },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros del consejo</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadConsejoTable();
};

export const loadConsejoTable = async () => {
  let consejoData = await getConsejoData();

  if (!Array.isArray(consejoData.fullInfo)) return;

  if (!consejoData.fullInfo || consejoData.error) return;

  console.log(consejoData);

  let datosOrdenados = [...consejoData.fullInfo].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    return {
      nombre_apellido_presidente: el.nombre_apellido_presidente,
      nombre_apellido_secretario: el.nombre_apellido_secretario,
      domicilio: el.domicilio,
      telefono: el.telefono || "No asignado",
      pagina_web: el.pagina_web || "No asignado",
      email: el.email || "No asignado",
      consejo_local: el.consejo_local || "No asignado",
      // sector_nombre: el.sector_informacion.nombre,
      //   sector_cod: sector_codigo,
      //   partida: el.partida,
      //   descripcion: descripcion,
      //   monto_inicial: `${separarMiles(el.monto_inicial)} Bs`,
      //   monto_actual: `${separarMiles(el.monto_actual)} Bs`,
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  consejoTable.clear().draw();

  // console.log(datosOrdenados)
  consejoTable.rows.add(data).draw();
};

let directivoTable;
export const validateDirectivoTable = async () => {
  directivoTable = new DataTable("#directivo-table", {
    columns: [
      { data: "nombre_apellido" },
      { data: "direccion" },
      { data: "email" },
      { data: "telefono" },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros en los directivos</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadDirectivoTable();
};

export const loadDirectivoTable = async () => {
  let directivoData = await getDirectivoData();

  if (!Array.isArray(directivoData.fullInfo)) return;

  if (!directivoData.fullInfo || directivoData.error) return;

  console.log(directivoData);

  let datosOrdenados = [...directivoData.fullInfo].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    return {
      nombre_apellido: el.nombre_apellido,
      direccion: el.direccion,
      email: el.email || "No asignado",
      telefono: el.telefono || "No asignado",
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  directivoTable.clear().draw();

  // console.log(datosOrdenados)
  directivoTable.rows.add(data).draw();
};
let personaTable;
export const validatePersonaTable = async () => {
  personaTable = new DataTable("#persona-table", {
    columns: [{ data: "nombres" }, { data: "cargo" }, { data: "acciones" }],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros de personas</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadPersonaTable();
};

export const loadPersonaTable = async () => {
  let personaData = await getPersonaData();

  if (!Array.isArray(personaData.fullInfo)) return;

  if (!personaData.fullInfo || personaData.error) return;

  console.log(personaData);

  let datosOrdenados = [...personaData.fullInfo].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    return {
      nombres: el.nombres,
      cargo: el.cargo,
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  personaTable.clear().draw();

  // console.log(datosOrdenados)
  personaTable.rows.add(data).draw();
};

let titulo1Table;
export const validateTitulo1Table = async () => {
  titulo1Table = new DataTable("#titulo-1-table", {
    columns: [
      { data: "articulo" },
      { data: "descripcion" },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Artículos registrados</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadTitulo1Table();
};

export const loadTitulo1Table = async () => {
  let titutlo1Data = await getTitulo1Data();

  if (!Array.isArray(titutlo1Data.fullInfo)) return;

  if (!titutlo1Data.fullInfo || titutlo1Data.error) return;

  let datosOrdenados = [...titutlo1Data.fullInfo].sort((a, b) => a.id - b.id);
  let data = datosOrdenados.map((el) => {
    let descripcion = recortarTexto(el.descripcion, 50);
    return {
      articulo: el.articulo,
      descripcion: descripcion || "No asignado",
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  titulo1Table.clear().draw();

  // console.log(datosOrdenados)
  titulo1Table.rows.add(data).draw();
};
let descripcionProgramaTable;
export const validateDescripcionProgramaTable = async () => {
  descripcionProgramaTable = new DataTable("#descripcion-programa-table", {
    columns: [
      { data: "sector" },
      { data: "programa" },
      { data: "descripcion" },
      { data: "acciones" },
    ],
    responsive: true,
    scrollY: 400,
    language: tableLanguage,
    layout: {
      topStart: function () {
        let toolbar = document.createElement("div");
        toolbar.innerHTML = `
            <h5 class="text-center">Lista de registros en descripcion de programas</h5>
                      `;
        return toolbar;
      },
      topEnd: { search: { placeholder: "Buscar..." } },
      bottomStart: "info",
      bottomEnd: "paging",
    },
  });

  loadDescripcionProgramaTable();
};

export const loadDescripcionProgramaTable = async () => {
  let descripcionPorgramaData = await getDescripcionProgramaData();

  if (!Array.isArray(descripcionPorgramaData.fullInfo)) return;

  if (!descripcionPorgramaData.fullInfo || descripcionPorgramaData.error)
    return;

  let datosOrdenados = [...descripcionPorgramaData.fullInfo].sort(
    (a, b) => a.id - b.id
  );
  let data = datosOrdenados.map((el) => {
    let descripcion = recortarTexto(el.descripcion, 50);
    return {
      sector: el.sector,
      programa: el.programa,
      descripcion: descripcion || "No asignado",
      acciones: `
      <button class="btn btn-sm bg-brand-color-2 text-white btn-update" data-editarid="${el.id}"></button>
      <button class="btn btn-danger btn-sm btn-destroy" data-eliminarid="${el.id}"></button>
      `,
    };
  });

  descripcionProgramaTable.clear().draw();

  // console.log(datosOrdenados)
  descripcionProgramaTable.rows.add(data).draw();
};

export async function deleteGobernacionRow({ id, row }) {
  gobernacionTable.row(row).remove().draw();
}

export async function deleteContraloriaRow({ id, row }) {
  contraloriaTable.row(row).remove().draw();
}
export async function deleteConsejoRow({ id, row }) {
  consejoTable.row(row).remove().draw();
}
export async function deleteDirectivoRow({ id, row }) {
  directivoTable.row(row).remove().draw();
}
export async function deletePersonaRow({ id, row }) {
  personaTable.row(row).remove().draw();
}

export async function deleteTitulo1Row({ id, row }) {
  titulo1Table.row(row).remove().draw();
}

export async function deleteDescripcionProgramaRow({ id, row }) {
  descripcionProgramaTable.row(row).remove().draw();
}
